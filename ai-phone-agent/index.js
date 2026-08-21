const config = require("./config");
const eslClient = require("./esl-client");
const { createHttpServer } = require("./http-server");
const { startWsServer } = require("./ws-server");
const transcriptHub = require("./transcript-hub");

async function main() {
    if (!config.gemini.apiKey) {
        console.warn("[Boot] GEMINI_API_KEY is not set — calls will fail to reach Gemini Live.");
    }
    if (!config.sharedSecret) {
        console.warn("[Boot] AI_PHONE_AGENT_SHARED_SECRET is not set — the control API will reject every call.");
    }

    // A pending Promise alone does not keep Node's event loop alive. Keep
    // retrying ESL while FreeSWITCH is still initializing or is restarted.
    while (true) {
        try {
            await eslClient.connect();
            break;
        } catch (error) {
            console.error("[Boot] ESL unavailable; retrying in 5 seconds.", error.message);
            await new Promise((resolve) => setTimeout(resolve, 5000));
        }
    }
    createHttpServer();
    transcriptHub.start();
    startWsServer();
}

main().catch((error) => {
    console.error("[Boot] Fatal error starting the AI phone agent bridge.", error);
    process.exit(1);
});
