const config = require("./config");
const eslClient = require("./esl-client");
const { createHttpServer } = require("./http-server");
const { startWsServer } = require("./ws-server");

async function main() {
    if (!config.gemini.apiKey) {
        console.warn("[Boot] GEMINI_API_KEY is not set — calls will fail to reach Gemini Live.");
    }
    if (!config.sharedSecret) {
        console.warn("[Boot] AI_PHONE_AGENT_SHARED_SECRET is not set — the control API will reject every call.");
    }

    await eslClient.connect();
    createHttpServer();
    startWsServer();
}

main().catch((error) => {
    console.error("[Boot] Fatal error starting the AI phone agent bridge.", error);
    process.exit(1);
});
