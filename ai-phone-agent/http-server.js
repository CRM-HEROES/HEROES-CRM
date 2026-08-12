const express = require("express");
const crypto = require("crypto");
const config = require("./config");
const eslClient = require("./esl-client");

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
        } = req.body || {};

        if (!prospectId || !destination || !userExtension) {
            return res.status(422).json({
                success: false,
                message: "prospect_id, destination_number et user_extension sont requis.",
            });
        }

        const callUuid = crypto.randomUUID();
        const room = `ai-call-${callUuid}`;

        try {
            // 1. The AI tap first, so Gemini is already listening before
            // anyone starts talking.
            const aiChannel = await eslClient.originateIntoConference(config.freeswitch.loopbackTarget, room);
            await eslClient.waitForAnswer(aiChannel);
            await eslClient.startAudioStream(aiChannel, config.wsPublicUrl, {
                call_uuid: callUuid,
                prospect_id: prospectId,
            });

            // 2. Ring the CRM user's own Kavkom extension — their browser
            // softphone (Kavkom.vue) answers automatically, exactly like
            // the existing click-to-call flow.
            const userDialTarget = `${config.freeswitch.dialPrefixInternal}${userExtension}`;
            const userChannel = await eslClient.originateIntoConference(userDialTarget, room);

            // Respond now: don't make the CRM's HTTP request wait on the
            // user's phone actually ringing (same reasoning as Kavkom's own
            // REST API, see KavkomService::TIMEOUT_SECONDS comment). The
            // prospect is only dialed once the user has answered, handled
            // in the background below.
            res.json({ success: true, call_uuid: callUuid });

            eslClient
                .waitForAnswer(userChannel)
                .then(() => {
                    const prospectDialTarget = `${config.freeswitch.dialPrefixExternal}${destination}`;
                    return eslClient.originateIntoConference(prospectDialTarget, room);
                })
                .catch((error) => {
                    console.error(`[HTTP] Call ${callUuid}: user did not answer or prospect dial failed.`, error);
                    eslClient.hangup(aiChannel);
                });
        } catch (error) {
            console.error(`[HTTP] Failed to set up AI call ${callUuid}.`, error);
            if (!res.headersSent) {
                res.json({
                    success: false,
                    message: "Impossible d'établir la conférence pour l'appel IA. Voir les logs du service.",
                });
            }
        }
    });

    app.listen(config.httpPort, () => {
        console.log(`[HTTP] Control API listening on port ${config.httpPort}.`);
    });

    return app;
}

module.exports = { createHttpServer };
