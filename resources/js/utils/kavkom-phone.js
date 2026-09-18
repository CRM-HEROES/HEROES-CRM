import { reactive } from "vue";
import { SimpleUser } from "sip.js/lib/platform/web";
import ApiService from "@/apis/api.service";
import EventBus from "@/utils/event-bus";

/**
 * Single Kavkom softphone for the whole application.
 *
 * The SIP registration used to live inside the "Kavkom" tab of the prospect
 * interaction slide, which meant an incoming call could only be received
 * while that tab was open. The extension is now registered once, for the
 * whole session (see KavkomIncomingCall.vue, mounted in the main layout),
 * and every UI component reads this shared state instead of connecting.
 *
 * A softphone extension must never be registered twice from the same
 * browser: two AOR contacts would make the PBX fork an incoming call to two
 * dialogs, ringing twice and attaching the media to only one of them.
 */

/** Events published on the app-wide EventBus. */
export const KAVKOM_EVENTS = {
    READY: "kavkom:ready",
    INCOMING_CALL: "kavkom:incoming-call",
    CALL_ANSWERED: "kavkom:call-answered",
    CALL_HANGUP: "kavkom:call-hangup",
    CALL_FAILED: "kavkom:call-failed",
    CONNECTION_ERROR: "kavkom:connection-error",
};

/**
 * Outbound calls made from the CRM work in two steps: the REST API asks the
 * PBX to call our extension (the "agent leg"), then the PBX bridges it to
 * the prospect. The agent leg must be answered immediately or Kavkom drops
 * it, so it is auto-answered — but only when the CRM is the one that just
 * triggered a call, never for a call coming from outside. The window is
 * wide enough to cover the REST request itself (Kavkom can take tens of
 * seconds to acknowledge it) yet short enough not to swallow a genuine
 * incoming call arriving a minute later.
 */
const AGENT_LEG_TIMEOUT_MS = 60000;

export const state = reactive({
    /** idle | connecting | not-configured | registered | ringing | in-call | error */
    status: "idle",
    errorMessage: "",
    sipErrorDetails: "",
    extension: "",
    isRegistered: false,
    /** null | 'inbound' | 'outbound' */
    callDirection: null,
    callNumber: "",
    callDisplayName: "",
    callAnswered: false,
    callStartedAt: null,
    /** True when the CRM answered the call itself (its own agent leg). */
    callAutoAnswered: false,
});

let simpleUser = null;
let remoteAudio = null;
let bootstrapping = false;
let connectionAttempts = 0;
let expectedAgentLegUntil = 0;
let declinedCall = false;

const MAX_CONNECTION_ATTEMPTS = 2;

/**
 * The element the remote media is attached to. The global softphone widget
 * owns it: it outlives the slides, so an ongoing call keeps its audio when
 * the agent navigates.
 */
export function setRemoteAudio(element) {
    if (element) {
        remoteAudio = element;
    }
}

/**
 * Register the extension for the current user, if a Kavkom line is
 * configured. Safe to call repeatedly (layout mount, reconnection...).
 */
export async function bootstrap({ projectId = null } = {}) {
    if (bootstrapping || simpleUser || state.status === "registered") {
        return;
    }

    bootstrapping = true;

    try {
        await registerWebphone(projectId);
    } finally {
        bootstrapping = false;
    }
}

/** Reconnect with the credentials saved since the last attempt. */
export async function refresh() {
    connectionAttempts = 0;
    await teardown();
    await bootstrap();
}

/**
 * Tell the softphone that the CRM has just asked Kavkom to call this
 * extension: the next incoming INVITE is the agent leg of a click-to-call
 * and must be answered automatically.
 */
export function expectAgentLeg(timeoutMs = AGENT_LEG_TIMEOUT_MS) {
    expectedAgentLegUntil = Date.now() + timeoutMs;
}

/**
 * Give up on an expected agent leg: the call request was refused before
 * Kavkom could ring this extension (missing configuration, refused caller
 * ID...), so the next INVITE is a genuine incoming call again.
 */
export function forgetAgentLeg() {
    expectedAgentLegUntil = 0;
}

async function registerWebphone(projectId = null) {
    state.status = "connecting";
    state.isRegistered = false;
    state.errorMessage = "";
    state.sipErrorDetails = "";

    try {
        const { data } = await ApiService.get("settings/kavkom/credentials", {
            params: { project_id: projectId },
        });

        if (!data.success) {
            state.status = "not-configured";
            state.errorMessage = data.message;
            logError("Credentials fetch failed", { response: data });
            return;
        }

        state.extension = data.extension;
        logInfo("Credentials received", {
            extension: state.extension,
            userContext: data.user_context,
        });

        connectSip(data);
    } catch (error) {
        state.status = "error";
        state.errorMessage =
            error.response?.data?.message ||
            error.message ||
            "Erreur inattendue lors de la connexion à Kavkom.";
        logError("Credentials fetch error", { error });
        EventBus.emit(KAVKOM_EVENTS.CONNECTION_ERROR, state.errorMessage);
    }
}

function connectSip({ extension, password, user_context }) {
    const server = `wss://${user_context}/`;
    const aor = `sip:${extension}@${user_context}`;

    logInfo("Connecting to SIP", { server, aor, extension });

    simpleUser = new SimpleUser(server, {
        aor,
        userAgentOptions: {
            authorizationUsername: extension,
            authorizationPassword: password,
            logBuiltinEnabled: true,
            logLevel: "debug",
            logConnector: createSipLogger(),
            transportOptions: {
                // SimpleUser ne renseigne `server` dans transportOptions que
                // si on ne fournit pas nous-mêmes cet objet ; on doit donc le
                // redéfinir ici.
                server,
                // Sans ping périodique, le proxy WSS de Kavkom coupe la
                // connexion pour inactivité (fermeture code 1006 observée
                // toutes les ~2 minutes), désenregistrant l'extension entre
                // deux appels.
                keepAliveInterval: 30,
            },
        },
        media: {
            constraints: { audio: true, video: false },
            remote: { audio: remoteAudio },
        },
    });

    simpleUser.delegate = {
        onRegistered: () => {
            state.isRegistered = true;
            state.status = "registered";
            connectionAttempts = 0;
            logInfo("SIP registration succeeded", { extension: state.extension });
            EventBus.emit(KAVKOM_EVENTS.READY, { extension: state.extension });
        },
        onUnregistered: () => {
            state.isRegistered = false;
        },
        onServerDisconnect: (error) => {
            state.isRegistered = false;

            if (error) {
                state.status = "error";
                state.errorMessage =
                    "La connexion au softphone Kavkom a été interrompue.";
                EventBus.emit(KAVKOM_EVENTS.CONNECTION_ERROR, state.errorMessage);
            }
        },

        /**
         * An incoming INVITE is either the agent leg of a click-to-call the
         * CRM has just triggered (answered automatically) or a call coming
         * from outside on the agent's Kavkom number (the agent decides).
         */
        onCallReceived: () => {
            const caller = extractCaller();
            const automatic = isAgentLegExpected();

            declinedCall = false;
            state.status = "ringing";
            state.callDirection = automatic ? "outbound" : "inbound";
            state.callNumber = caller.number;
            state.callDisplayName = caller.displayName;
            state.callAnswered = false;
            state.callAutoAnswered = automatic;
            state.callStartedAt = null;

            logInfo("Incoming call received", {
                direction: state.callDirection,
                caller: state.callNumber,
                automatic,
            });

            EventBus.emit(KAVKOM_EVENTS.INCOMING_CALL, {
                direction: state.callDirection,
                number: state.callNumber,
                displayName: state.callDisplayName,
                automatic,
            });

            if (automatic) {
                void answer(true);
            }
        },

        onCallAnswered: () => {
            state.status = "in-call";
            state.callAnswered = true;
            state.callStartedAt = Date.now();
            playRemoteAudio();
            logInfo("Call established", {
                direction: state.callDirection,
                number: state.callNumber,
            });
            EventBus.emit(KAVKOM_EVENTS.CALL_ANSWERED, callSnapshot());
        },

        onCallHangup: () => {
            const snapshot = callSnapshot();

            resetCall();
            // A lost registration during the call (server disconnect) must
            // not leave the UI stuck on "in-call".
            state.status = state.isRegistered ? "registered" : "idle";

            logInfo("Call ended", snapshot);
            EventBus.emit(KAVKOM_EVENTS.CALL_HANGUP, snapshot);
        },
    };

    simpleUser
        .connect()
        .then(() => {
            logInfo("SIP connection established");
            return simpleUser.register();
        })
        .catch((error) => {
            state.isRegistered = false;
            state.status = "error";
            const errorMsg = extractSipErrorMessage(error);
            state.errorMessage = errorMsg;
            state.sipErrorDetails = extractSipErrorDetails(error);
            logError("SIP connection or registration failed", {
                error: errorMsg,
                details: state.sipErrorDetails,
                originalError: error,
            });
            EventBus.emit(KAVKOM_EVENTS.CONNECTION_ERROR, errorMsg);

            if (connectionAttempts < MAX_CONNECTION_ATTEMPTS) {
                connectionAttempts++;
                logWarn("Retrying SIP connection", {
                    attempt: connectionAttempts,
                    maxAttempts: MAX_CONNECTION_ATTEMPTS,
                });
                setTimeout(() => {
                    teardown().then(() => registerWebphone());
                }, 2000);
            }
        });
}

/** Accept the call. `automatic` is true for the agent leg of a click-to-call. */
export async function answer(automatic = false) {
    if (!simpleUser?.session) {
        return;
    }

    const direction = state.callDirection;

    try {
        await simpleUser.answer();
        logInfo("Call accepted", { automatic });
    } catch (error) {
        resetCall();
        state.status = state.isRegistered ? "registered" : "idle";
        logError("Failed to accept the Kavkom call", { error });
        EventBus.emit(KAVKOM_EVENTS.CALL_FAILED, {
            message: "Impossible d'accepter l'appel Kavkom.",
            direction,
            automatic,
        });
    }
}

/**
 * Reject the ringing call. A deliberately rejected call is not a missed
 * one: it is reported as such to the history without alerting the agent.
 */
export async function decline() {
    declinedCall = true;

    try {
        await simpleUser?.decline();
        logInfo("Call declined");
    } catch (error) {
        logError("Failed to decline the Kavkom call", { error });
    } finally {
        // Rejecting the INVITE terminates the session and the delegate's
        // onCallHangup resets the call state, keeping its details
        // (direction, missed...) for the call history. Only clear here when
        // there is no session left to end.
        if (!simpleUser?.session) {
            resetCall();
            state.status = state.isRegistered ? "registered" : "idle";
        }
    }
}

/** Hang up the ongoing call. */
export function hangup() {
    simpleUser?.hangup().catch((error) => {
        logError("Failed to hang up call", { error });
    });
}

/** Close the SIP transport (logout, or before a fresh registration). */
export async function teardown() {
    const user = simpleUser;
    simpleUser = null;
    state.isRegistered = false;
    resetCall();

    if (user) {
        try {
            await user.disconnect();
        } catch (_) {
            // The old WSS transport may already be closed.
        }
    }
}

function resetCall() {
    state.callDirection = null;
    state.callNumber = "";
    state.callDisplayName = "";
    state.callAnswered = false;
    state.callAutoAnswered = false;
    state.callStartedAt = null;
}

function callSnapshot() {
    return {
        direction: state.callDirection,
        number: state.callNumber,
        displayName: state.callDisplayName,
        answered: state.callAnswered,
        durationMs: state.callStartedAt ? Date.now() - state.callStartedAt : null,
        // A ringing inbound call that ends without being answered is a
        // missed call, not a completed one.
        missed: state.callDirection === "inbound" && !state.callAnswered,
        declined: declinedCall,
    };
}

function isAgentLegExpected() {
    if (expectedAgentLegUntil === 0) {
        return false;
    }

    const expected = Date.now() <= expectedAgentLegUntil;
    expectedAgentLegUntil = 0;

    return expected;
}

/**
 * Caller number and name, as advertised by the PBX in the INVITE. Kavkom
 * may only expose the caller through P-Asserted-Identity or the From header
 * depending on the trunk, so both are inspected.
 */
function extractCaller() {
    const session = simpleUser?.session;
    let number = "";
    let displayName = "";

    try {
        number = (session?.remoteIdentity?.uri?.user || "").trim();
        displayName = (session?.remoteIdentity?.displayName || "").trim();
    } catch (_) {
        // Not an incoming invitation: no remote identity to read.
    }

    let assertIdentity = "";
    try {
        assertIdentity = (
            session?.request?.getHeader?.("P-Asserted-Identity") || ""
        ).trim();
    } catch (_) {
        assertIdentity = "";
    }

    if (assertIdentity) {
        // Both the number and the display name can be present:
        // "Nicolas" <sip:+33688753390@client.kavkom.com>
        const match = assertIdentity.match(/sip:([^@;>]+)@/i);

        if (match) {
            number = match[1].replace(/[^\d+]/g, "") || number;
        }

        if (!displayName) {
            displayName = assertIdentity.replace(/<[^>]*>/g, "").replace(/"/g, "").trim();
        }
    }

    return {
        number: displayableNumber(number),
        displayName,
    };
}

/** Keep the digits and a leading + so the number reads like a phone number. */
function displayableNumber(number) {
    const trimmed = (number || "").replace(/[^\d+]/g, "");

    return trimmed ? (trimmed.startsWith("+") ? trimmed : `+${trimmed}`) : "";
}

async function playRemoteAudio() {
    if (!remoteAudio) {
        logWarn("Remote audio element not available.");
        return;
    }

    // The stream is attached when the call is answered rather than at
    // construction time: the remote audio element belongs to the global
    // widget, which may not be mounted yet when the registration starts.
    const stream = simpleUser?.remoteMediaStream;
    if (stream && remoteAudio.srcObject !== stream) {
        remoteAudio.srcObject = stream;
    }

    try {
        await remoteAudio.play();
        logInfo("Remote audio playback started", {
            hasStream: !!remoteAudio.srcObject,
            audioTracks: remoteAudio.srcObject?.getAudioTracks?.().length || 0,
        });
    } catch (error) {
        logError("Remote audio playback blocked by the browser", { error });
        EventBus.emit(
            KAVKOM_EVENTS.CONNECTION_ERROR,
            "Le navigateur a bloqué le son de l'appel. Autorisez l'audio et le microphone pour ce site."
        );
    }
}

/**
 * Extract a user-friendly error message from SIP errors
 */
function extractSipErrorMessage(error) {
    if (error instanceof TypeError) {
        if (error.message.includes("WebSocket")) {
            return "Impossible de se connecter au serveur SIP WebSocket (problème de certificat SSL, DNS, ou firewall).";
        }
        return "Erreur de connexion SIP : " + error.message;
    }

    if (error && typeof error === "object") {
        if (error.statusCode === 401 || error.statusCode === 407) {
            return "Erreur d'authentification SIP (identifiants incorrects ou extension désactivée dans Kavkom).";
        }
        if (error.statusCode === 404) {
            return "Extension SIP non trouvée sur le serveur Kavkom.";
        }
        if (error.reasonPhrase && error.reasonPhrase.includes("NOT_REGISTERED")) {
            return "L'extension n'a pas pu s'enregistrer auprès du serveur SIP. Le serveur WebSocket n'est peut-être pas joignable.";
        }
        if (error.message) {
            return "Erreur SIP : " + error.message;
        }
        if (error.statusCode) {
            return "Erreur SIP (HTTP " + error.statusCode + ").";
        }
    }

    return "Impossible de connecter le softphone à l'extension Kavkom.";
}

/**
 * Extract detailed error information for debugging
 */
function extractSipErrorDetails(error) {
    const details = [];

    if (error && typeof error === "object") {
        if (error.statusCode) {
            details.push(`Status: ${error.statusCode}`);
        }
        if (error.reasonPhrase) {
            details.push(`Reason: ${error.reasonPhrase}`);
        }
        if (error.cause) {
            details.push(`Cause: ${error.cause}`);
        }
        if (error.message) {
            details.push(`Message: ${error.message}`);
        }
    }

    return details.join(" | ");
}

function createSipLogger() {
    return (message) => {
        logDebug("SIP.js", { message });
    };
}

function logInfo(message, data = {}) {
    console.log(`[Kavkom ${new Date().toISOString()}] ${message}`, data);
}

function logDebug(message, data = {}) {
    console.debug(`[Kavkom DEBUG ${new Date().toISOString()}] ${message}`, data);
}

function logWarn(message, data = {}) {
    console.warn(`[Kavkom WARNING ${new Date().toISOString()}] ${message}`, data);
}

function logError(message, data = {}) {
    console.error(`[Kavkom ERROR ${new Date().toISOString()}] ${message}`, data);
}

/**
 * The softphone belongs to the session: it stops with the app (logout,
 * impersonation change...).
 */
export default {
    state,
    KAVKOM_EVENTS,
    setRemoteAudio,
    bootstrap,
    refresh,
    expectAgentLeg,
    forgetAgentLeg,
    answer,
    decline,
    hangup,
    teardown,
};