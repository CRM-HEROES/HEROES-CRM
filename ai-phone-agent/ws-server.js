const WebSocket = require("ws");
const config = require("./config");
const { GeminiCallBridge } = require("./gemini-call-bridge");

/**
 * Accepts the WebSocket connections opened by FreeSWITCH's
 * mod_audio_stream (one per call, started via
 * `uuid_audio_stream <uuid> start <wsUrl> mono 16k <metadata>` in
 * esl-client.js). The first frame is the `<metadata>` argument as UTF-8
 * text — here a JSON blob with call_uuid/prospect_id, which is enough to
 * instantiate the right GeminiCallBridge with no extra lookup needed.
 * Every following binary frame is raw L16 PCM audio at 16kHz.
 */
function startWsServer() {
    const wss = new WebSocket.Server({ port: config.wsPort });

    wss.on("connection", (ws) => {
        let bridge = null;
        let gotMetadata = false;

        ws.on("message", (data, isBinary) => {
            if (!gotMetadata) {
                gotMetadata = true;

                let metadata = {};
                try {
                    metadata = JSON.parse(data.toString());
                } catch (error) {
                    console.warn("[WS] Invalid metadata frame from FreeSWITCH.", data.toString().slice(0, 200));
                }

                bridge = new GeminiCallBridge({
                    callUuid: metadata.call_uuid || `unknown-${Date.now()}`,
                    prospectId: metadata.prospect_id,
                });
                bridge.onAudio = (base64Pcm24k) => {
                    if (ws.readyState !== WebSocket.OPEN) {
                        return;
                    }
                    ws.send(
                        JSON.stringify({
                            type: "streamAudio",
                            data: { audioDataType: "raw", sampleRate: 24000, audioData: base64Pcm24k },
                        })
                    );
                };
                bridge.connect();
                return;
            }

            if (isBinary && bridge) {
                bridge.pushAudio(data);
            }
        });

        ws.on("close", async () => {
            if (bridge) {
                await bridge.finalize();
            }
        });

        ws.on("error", (error) => {
            console.error("[WS] FreeSWITCH audio stream error.", error);
        });
    });

    console.log(`[WS] Listening for FreeSWITCH audio streams on port ${config.wsPort}.`);
    return wss;
}

module.exports = { startWsServer };
