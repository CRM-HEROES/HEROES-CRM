const express = require("express");
const crypto = require("crypto");
const config = require("./config");
const eslClient = require("./esl-client");
const { getBridge, registerCallContext } = require("./ws-server");

function checkSecret(req, res, next) {
    const provided = req.header("X-AI-Agent-Secret") || "";
    if (!config.sharedSecret || provided !== config.sharedSecret) {
        return res.status(401).json({ success: false, message: "Unauthorized" });
    }
    next();
}

/**
 * Control API called by AiPhoneAgentController::trigger() when a CRM user
 * clicks "Appeler avec l'IA". Sets up a 3-way FreeSWITCH conference: the
 * AI tap (a loopback channel with mod_audio_stream, bridged to Gemini
 * Live), the CRM user's own Kavkom extension, and the prospect.
 */
function createHttpServer() {
    const app = express();
    app.use(express.json());

    app.post("/calls", checkSecret, async (req, res) => {
        const {
            prospect_id: prospectId,
            destination_number: destination,
            user_extension: userExtension,
            agent_id: agentId,
            agent,
            kavkom_config: kavkomConfig,
        } = req.body || {};

        if (!prospectId || !destination || !userExtension || !agentId || !agent || !kavkomConfig) {
            return res.status(422).json({
                success: false,
                message: "prospect_id, destination_number, user_extension, agent_id et agent sont requis.",
            });
        }
        const normalizedDestination = String(destination).replace(/[^\d+]/g, "");
        if (config.testMode && !config.testAllowedNumbers.includes(normalizedDestination)) {
            return res.status(403).json({ success: false, message: "TEST_MODE: ce numéro n'est pas dans TEST_ALLOWED_NUMBERS." });
        }

        const callUuid = crypto.randomUUID();
        const room = `ai-call-${callUuid}`;
        // Tracked outside the try so the catch can tear the AI tap down if
        // a later step (audio stream, prospect leg) fails after it exists.
        let aiChannel = null;

        console.log(`[HTTP] Starting AI call ${callUuid}.`, {
            prospectId,
            agentId,
            agentName: agent.name,
            userExtension,
            destination: normalizedDestination,
        });

        try {
            // 1. The AI tap first, so Gemini is already listening before
            // anyone starts talking.
            aiChannel = await eslClient.originateIntoConference(config.freeswitch.loopbackTarget, room);
            console.log(`[HTTP] ${callUuid}: AI tap originated (${aiChannel}).`);
            await eslClient.waitForAnswer(aiChannel);
            console.log(`[HTTP] ${callUuid}: AI tap answered.`);
            registerCallContext(callUuid, { agent, agentId });
            await eslClient.startAudioStream(aiChannel, config.wsPublicUrl, {
                call_uuid: callUuid,
                prospect_id: prospectId,
                context: String(req.body.context || "").slice(0, 8000),
            });

            // 2. Dial the prospect directly. (Skipping the CRM user leg 
            // because Kavkom SIP trunk rejects outbound calls to internal 
            // extensions like 506 with NORMAL_TEMPORARY_FAILURE).
            // For the demo, the user can simply set the prospect's number 
            // to their own mobile phone, and the AI will call them directly!
            const prospectDialTarget = buildKavkomTarget(destination, kavkomConfig, false);
            console.log(`[HTTP] ${callUuid}: Originating prospect leg with dial string: ${prospectDialTarget}`);
            const prospectChannel = await eslClient.originateIntoConference(prospectDialTarget, room, { 
                sipAuth: kavkomConfig,
                callerIdNumber: kavkomConfig.phone_number ? String(kavkomConfig.phone_number).replace(/\D/g, "") : undefined
            });
            console.log(`[HTTP] ${callUuid}: Prospect leg originated (${prospectChannel}).`);

            // Respond immediately so the UI updates and connects the transcript WS
            res.json({ success: true, call_uuid: callUuid });

            eslClient
                .waitForAnswer(prospectChannel)
                .then(() => {
                    console.log(`[HTTP] ${callUuid}: Prospect answered.`);
                    const bridge = getBridge(callUuid);
                    if (bridge) {
                        bridge.pushText("Le prospect vient de décrocher. Commence la conversation en te présentant selon tes instructions.");
                    }
                })
                .catch((error) => {
                    console.error(`[HTTP] Call ${callUuid}: prospect dial failed or prospect hung up.`, error);
                    eslClient.hangup(aiChannel);
                });

        } catch (error) {
            console.error(`[HTTP] Failed to set up AI call ${callUuid}.`, error);
            if (aiChannel) {
                // The AI tap is already up: hang it up so it does not stay in
                // the conference alone with a live Gemini stream attached.
                eslClient.hangup(aiChannel);
            }
            if (!res.headersSent) {
                res.json({
                    success: false,
                    message: "Impossible d'établir la conférence pour l'appel IA. Voir les logs du service.",
                });
            }
        }
    });

    app.post("/calls/:callUuid/pause", checkSecret, (req, res) => {
        const bridge = getBridge(req.params.callUuid);
        if (bridge) bridge.setPaused(Boolean(req.body.paused));
        res.json({ success: true, active: Boolean(bridge) });
    });

    // LAN-only test path: rings a registered Linphone account and joins it to
    // Gemini without Kavkom, PSTN, or an external destination number.
    app.post("/local-test", checkSecret, async (req, res) => {
        const userExtension = String(req.body?.user_extension || config.localTestSipUser);
        const callUuid = crypto.randomUUID();
        const room = `ai-local-${callUuid}`;
        let aiChannel = null;
        try {
            aiChannel = await eslClient.originateIntoConference(config.freeswitch.loopbackTarget, room);
            await eslClient.waitForAnswer(aiChannel);
            await eslClient.startAudioStream(aiChannel, config.wsPublicUrl, {
                call_uuid: callUuid,
                prospect_id: null,
                context: "Local Linphone voice test. Speak naturally in French.",
            });
            const userChannel = await eslClient.originateIntoConference(`user/${userExtension}`, room);
            res.json({ success: true, call_uuid: callUuid, user_extension: userExtension });
            eslClient.waitForHangup(userChannel)
                .finally(() => eslClient.hangup(aiChannel));
        } catch (error) {
            console.error(`[HTTP] Failed local Linphone test ${callUuid}.`, error);
            if (aiChannel) {
                eslClient.hangup(aiChannel);
            }
            if (!res.headersSent) res.status(500).json({ success: false, message: "Impossible de démarrer le test Linphone. Consultez les logs." });
        }
    });

    app.listen(config.httpPort, () => {
        console.log(`[HTTP] Control API listening on port ${config.httpPort}.`);
    });

    return app;
}

function buildKavkomTarget(destination, kavkomConfig, isInternal = false) {
    const number = String(destination).replace(/[^\d+]/g, "");
    if (!number) {
        throw new Error("Agent Kavkom config must contain a destination number.");
    }
    
    // FreeSWITCH requires routing outbound calls through a defined gateway
    // rather than raw SIP URIs, otherwise it yields CHAN_NOT_IMPLEMENTED
    // if the profile isn't configured for raw outbound routing.
    const prefix = isInternal
        ? config.freeswitch.dialPrefixInternal
        : config.freeswitch.dialPrefixExternal;

    return `${prefix}${number}`;
}


module.exports = { createHttpServer };
