<template>
    <div :id="id" class="hc-kavkom-webphone">
        <audio ref="remoteAudio" autoplay style="display: none"></audio>

        <div class="hc-kavkom-webphone-status">
            <span v-if="status === 'not-configured'">
                Configurez le jeton Kavkom et le domain UUID dans les
                paramètres pour activer le softphone.
            </span>
            <span v-else-if="status === 'error'">
                <div style="color: #dc3545; margin-bottom: 4px;">{{ errorMessage || "Erreur de connexion au softphone Kavkom." }}</div>
                <div v-if="sipErrorDetails" style="font-size: 11px; color: #dc3545; opacity: 0.85;">
                    {{ sipErrorDetails }}
                </div>
            </span>
            <span v-else-if="status === 'connecting'">
                Connexion du softphone à l'extension Kavkom…
            </span>
            <span v-else-if="status === 'registered'">
                Softphone Kavkom prêt (extension {{ extension }}).
            </span>
            <span v-else-if="status === 'calling'">Appel en cours de connexion…</span>
            <span v-else-if="status === 'ringing'">
                Votre poste sonne (mise en relation Kavkom)…
            </span>
            <span v-else-if="status === 'in-call'">Appel en cours.</span>
        </div>

        <div class="hc-kavkom-webphone-controls" v-if="status === 'ringing'">
            <button type="button" class="hc-button-secondary" @click="answer">
                Accepter l'appel
            </button>
            <button type="button" class="hc-button-danger" @click="decline">
                Refuser
            </button>
        </div>

        <div class="hc-kavkom-webphone-controls" v-else-if="status === 'in-call'">
            <button type="button" class="hc-button-danger" @click="hangup">
                Raccrocher
            </button>
        </div>
    </div>
</template>

<style scoped>
.hc-kavkom-webphone {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.hc-kavkom-webphone-status {
    font-size: 13px;
    color: #6c757d;
}
.hc-kavkom-webphone-controls {
    display: flex;
    gap: 8px;
}
</style>

<script>
import { SimpleUser } from "sip.js/lib/platform/web";
import ApiService from "@/apis/api.service";

export default {
    props: {
        id: {
            type: String,
        },
        // This component is only mounted from the CRM click-to-call screen.
        // Answer the agent leg immediately so the PBX does not time it out
        // while the user is looking for an "Accept" button.
        autoAnswer: {
            type: Boolean,
            default: true,
        },
    },

    data() {
        return {
            simpleUser: null,
            status: "connecting",
            errorMessage: "",
            extension: "",
            userContext: "",
            isRegistered: false,
            sipErrorDetails: "",
            connectionAttempts: 0,
            maxConnectionAttempts: 2,
            pcDiagnosticInterval: null,
            ringtoneContext: null,
            ringtoneTimer: null,
            ringtoneOscillators: [],
            callEstablishedAt: null,
        };
    },

    async mounted() {
        await this.registerWebphone();
    },

    beforeUnmount() {
        this.stopRingtone();
        this.teardown();
    },

    methods: {
        /** Reconnect with the credentials saved after the component mounted. */
        async refreshWebphone() {
            this.connectionAttempts = 0;
            await this.teardown();
            await this.registerWebphone();
        },

        async registerWebphone() {
            this.status = "connecting";
            this.isRegistered = false;
            this.errorMessage = "";
            this.sipErrorDetails = "";

            try {
                const { data } = await ApiService.get(
                    "settings/kavkom/credentials"
                );

                if (!data.success) {
                    this.status = "not-configured";
                    this.errorMessage = data.message;
                    this.logError("Credentials fetch failed", { response: data });
                    return;
                }

                this.extension = data.extension;
                this.userContext = data.user_context;
                this.logInfo("Credentials received", {
                    extension: this.extension,
                    userContext: data.user_context,
                });

                this.connectSip(data);
            } catch (error) {
                this.status = "error";
                this.errorMessage =
                    error.response?.data?.message ||
                    error.message ||
                    "Erreur inattendue lors de la connexion à Kavkom.";
                this.logError("Credentials fetch error", { error });
            }
        },

        connectSip({ extension, password, user_context }) {
            const server = `wss://${user_context}/`;
            const aor = `sip:${extension}@${user_context}`;

            this.logInfo("Connecting to SIP", {
                server,
                aor,
                extension,
            });

            this.simpleUser = new SimpleUser(server, {
                aor,
                userAgentOptions: {
                    authorizationUsername: extension,
                    authorizationPassword: password,
                    logBuiltinEnabled: true,
                    logLevel: "debug",
                    logConnector: this.createSipLogger(),
                    transportOptions: {
                        // SimpleUser ne renseigne `server` dans
                        // transportOptions que si on ne fournit pas nous-
                        // mêmes cet objet ; on doit donc le redéfinir ici.
                        server,
                        // Sans ping périodique, le proxy WSS de Kavkom coupe
                        // la connexion pour inactivité (fermeture code 1006
                        // observée toutes les ~2 minutes), désenregistrant
                        // l'extension entre deux appels.
                        keepAliveInterval: 30,
                    },
                },
                media: {
                    constraints: { audio: true, video: false },
                    remote: { audio: this.$refs.remoteAudio },
                },
            });

            this.simpleUser.delegate = {
                onRegistered: () => {
                    this.isRegistered = true;
                    this.status = "registered";
                    this.logInfo("SIP registration succeeded", {
                        extension: this.extension,
                    });
                    this.$emit("ready");
                },
                onUnregistered: () => {
                    this.isRegistered = false;
                },
                onServerDisconnect: (error) => {
                    this.isRegistered = false;

                    if (error) {
                        this.status = "error";
                        this.errorMessage = "La connexion au softphone Kavkom a été interrompue.";
                        this.$emit("connection-error", this.errorMessage);
                    }
                },

                /** The PBX calls the WebRTC extension after the REST request.
                 * The user explicitly accepts this agent leg before Kavkom
                 * connects the prospect. */
                onCallReceived: () => {
                    this.status = "ringing";
                    this.logInfo("Appel entrant du PBX Kavkom (leg agent)", {
                        autoAnswer: this.autoAnswer,
                    });
                    this.$emit("ringing-call");

                    if (this.autoAnswer) {
                        // The former local ringtone was the repeated "beep"
                        // heard while waiting for a manual response. It also
                        // made it easy to exceed the PBX agent-leg timeout.
                        this.stopRingtone();
                        void this.answer(true);
                    } else {
                        this.playRingtone();
                    }
                },

                onCallAnswered: () => {
                    this.stopRingtone();
                    this.status = "in-call";
                    this.callEstablishedAt = Date.now();
                    this.playRemoteAudio();
                    this.logInfo("Appel établi (leg agent connecté)", {
                        remoteAudioPaused: this.$refs.remoteAudio?.paused,
                        remoteAudioMuted: this.$refs.remoteAudio?.muted,
                    });
                    this.$emit("answered-call");
                },

                onCallHangup: () => {
                    const durationMs = this.callEstablishedAt
                        ? Date.now() - this.callEstablishedAt
                        : null;
                    this.callEstablishedAt = null;
                    this.stopRingtone();
                    this.status = "registered";
                    this.logInfo("Appel terminé");
                    this.$emit("hangup-call", { durationMs });
                },
            };

            this.simpleUser
                .connect()
                .then(() => {
                    this.logInfo("SIP connection established");
                    return this.simpleUser.register();
                })
                .catch((error) => {
                    this.isRegistered = false;
                    this.status = "error";
                    const errorMsg = this.extractSipErrorMessage(error);
                    this.errorMessage = errorMsg;
                    this.sipErrorDetails = this.extractSipErrorDetails(error);
                    this.logError("SIP connection or registration failed", {
                        error: errorMsg,
                        details: this.sipErrorDetails,
                        originalError: error,
                    });
                    this.$emit("connection-error", this.errorMessage);

                    if (this.connectionAttempts < this.maxConnectionAttempts) {
                        this.connectionAttempts++;
                        this.logWarn("Retrying SIP connection", {
                            attempt: this.connectionAttempts,
                            maxAttempts: this.maxConnectionAttempts,
                        });
                        setTimeout(() => {
                            this.registerWebphone();
                        }, 2000);
                    }
                });
        },

        hangup() {
            this.simpleUser
                ?.hangup()
                .catch((error) => {
                    this.logError("Failed to hang up call", { error });
                });
        },

        async answer(automatic = false) {
            try {
                await this.simpleUser?.answer();
                this.logInfo("Leg agent accepté", { automatic });
            } catch (error) {
                this.stopRingtone();
                this.status = "registered";
                this.logError("Échec de l'acceptation de l'appel Kavkom", { error });
                this.$emit("call-failed", "Impossible d'accepter l'appel Kavkom.");
            }
        },

        async playRemoteAudio() {
            const audio = this.$refs.remoteAudio;
            if (!audio) {
                this.logWarn("Élément audio distant introuvable.");
                return;
            }

            try {
                await audio.play();
                const stream = audio.srcObject;
                this.logInfo("Lecture audio distante démarrée", {
                    hasStream: !!stream,
                    audioTracks: stream?.getAudioTracks?.().length || 0,
                });
            } catch (error) {
                this.logError("Lecture audio distante bloquée par le navigateur", { error });
                this.$emit(
                    "connection-error",
                    "Le navigateur a bloqué le son de l'appel. Autorisez l'audio et le microphone pour ce site."
                );
            }
        },

        async decline() {
            try {
                await this.simpleUser?.decline();
                this.logInfo("Leg agent refusé par l'utilisateur");
            } catch (error) {
                this.logError("Échec du refus de l'appel Kavkom", { error });
            } finally {
                this.stopRingtone();
                this.status = "registered";
                this.$emit("call-failed", "Appel Kavkom refusé.");
            }
        },

        /**
         * Sonnerie locale pour signaler le leg entrant envoyé par Kavkom.
         * Elle est synthétisée afin de ne pas dépendre d'un fichier audio et
         * est arrêtée dès que le softphone répond ou que l'appel se termine.
         */
        playRingtone() {
            this.stopRingtone();

            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) {
                return;
            }

            try {
                this.ringtoneContext = new AudioContext();
                this.ringtoneContext.resume?.();

                const ring = () => {
                    if (!this.ringtoneContext || this.status !== "ringing") {
                        return;
                    }

                    const now = this.ringtoneContext.currentTime;
                    const gain = this.ringtoneContext.createGain();
                    gain.gain.setValueAtTime(0.0001, now);
                    gain.gain.exponentialRampToValueAtTime(0.08, now + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.7);
                    gain.connect(this.ringtoneContext.destination);

                    [440, 480].forEach((frequency) => {
                        const oscillator = this.ringtoneContext.createOscillator();
                        oscillator.frequency.value = frequency;
                        oscillator.connect(gain);
                        oscillator.start(now);
                        oscillator.stop(now + 0.7);
                        this.ringtoneOscillators.push(oscillator);
                    });
                };

                ring();
                this.ringtoneTimer = window.setInterval(ring, 2000);
            } catch (error) {
                this.logWarn("Impossible de jouer la sonnerie locale", { error });
                this.stopRingtone();
            }
        },

        stopRingtone() {
            if (this.ringtoneTimer) {
                window.clearInterval(this.ringtoneTimer);
                this.ringtoneTimer = null;
            }

            this.ringtoneOscillators.forEach((oscillator) => {
                try {
                    oscillator.stop();
                } catch (_) {
                    // L'oscillateur a déjà été arrêté.
                }
            });
            this.ringtoneOscillators = [];

            if (this.ringtoneContext) {
                this.ringtoneContext.close().catch(() => {});
                this.ringtoneContext = null;
            }
        },

        async teardown() {
            const simpleUser = this.simpleUser;
            this.simpleUser = null;
            this.isRegistered = false;

            if (simpleUser) {
                try {
                    await simpleUser.disconnect();
                } catch (_) {
                    // The old WSS transport may already be closed.
                }
            }
        },

        /**
         * Extract a user-friendly error message from SIP errors
         */
        extractSipErrorMessage(error) {
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
        },

        /**
         * Extract detailed error information for debugging
         */
        extractSipErrorDetails(error) {
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
        },

        createSipLogger() {
            return (message) => {
                this.logDebug("SIP.js", { message });
            };
        },

        logInfo(message, data = {}) {
            console.log(
                `[Kavkom ${new Date().toISOString()}] ${message}`,
                Object.keys(data).length > 0 ? data : ""
            );
        },

        logDebug(message, data = {}) {
            console.debug(
                `[Kavkom DEBUG ${new Date().toISOString()}] ${message}`,
                Object.keys(data).length > 0 ? data : ""
            );
        },

        logWarn(message, data = {}) {
            console.warn(
                `[Kavkom WARNING ${new Date().toISOString()}] ${message}`,
                Object.keys(data).length > 0 ? data : ""
            );
        },

        logError(message, data = {}) {
            console.error(
                `[Kavkom ERROR ${new Date().toISOString()}] ${message}`,
                Object.keys(data).length > 0 ? data : ""
            );
        },
    },
};
</script>
