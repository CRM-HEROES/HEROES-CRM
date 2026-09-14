const WebSocket = require("ws");
const config = require("./config");
const { GeminiCallBridge } = require("./gemini-call-bridge");
const transcriptHub = require("./transcript-hub");
const activeBridges = new Map();
const pendingCallContexts = new Map();

function registerCallContext(callUuid, context) {
    pendingCallContexts.set(callUuid, context);
    setTimeout(() => pendingCallContexts.delete(callUuid), 10 * 60 * 1000).unref();
}

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

                const callContext = pendingCallContexts.get(metadata.call_uuid) || {};
                pendingCallContexts.delete(metadata.call_uuid);

                bridge = new GeminiCallBridge({
                    callUuid: metadata.call_uuid || `unknown-${Date.now()}`,
                    prospectId: metadata.prospect_id,
                    systemContext: metadata.context || "",
                    agent: callContext.agent || {},
                    agentId: callContext.agentId,
                });
                activeBridges.set(bridge.callUuid, bridge);
                bridge.onAudio = (base64Pcm24k) => {
                    if (ws.readyState !== WebSocket.OPEN) {
                        return;
                    }
                    const audio = Buffer.from(base64Pcm24k, "base64");
                    bridge.recordOutbound(audio);
                    ws.send(
                        JSON.stringify({
                            type: "streamAudio",
                            data: { audioDataType: "raw", sampleRate: 24000, audioData: base64Pcm24k },
                        })
                    );
                };
                bridge.onInterrupted = () => {
                    // mod_audio_stream handles immediate frames; this command
                    // clears any FreeSWITCH playback queued before the barge-in.
                    ws.send(JSON.stringify({ type: "clearAudio" }));
                    transcriptHub.publish({ type: "interrupted", call_uuid: bridge.callUuid, at: new Date().toISOString() });
                };
                bridge.connect();
                return;
            }

            if (isBinary && bridge) {
                bridge.recordInbound(data);
                bridge.pushAudio(data);
            }
        });

        ws.on("close", async () => {
            if (bridge) {
                activeBridges.delete(bridge.callUuid);
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

function getBridge(callUuid) { return activeBridges.get(callUuid); }
module.exports = { startWsServer, getBridge, registerCallContext };
