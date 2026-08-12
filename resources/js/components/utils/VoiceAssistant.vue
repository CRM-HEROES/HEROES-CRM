<template>
    <div
        ref="wrapper"
        class="hc-voice-assistant"
        :class="{ 'panel-below': panelBelow }"
        :style="wrapperStyle"
    >
        <div v-if="status !== 'idle'" class="hc-voice-assistant-panel">
            <div class="hc-voice-assistant-status">
                <span v-if="status === 'connecting'">Connexion à l'assistant vocal…</span>
                <span v-else-if="status === 'error'">{{ errorMessage || "Erreur de l'assistant vocal." }}</span>
                <span v-else-if="status === 'active'">
                    <span v-if="isSpeaking">Gemini répond…</span>
                    <span v-else>Assistant vocal actif — parlez.</span>
                </span>
            </div>
        </div>

        <button
            ref="button"
            type="button"
            class="hc-voice-assistant-button"
            :class="[buttonStatusClass, { 'is-dragging': dragging }]"
            :title="status === 'idle' ? 'Démarrer l\'assistant vocal (glisser pour déplacer)' : 'Arrêter l\'assistant vocal'"
            @pointerdown="onPointerDown"
            @click="onButtonClick"
        >
            <span class="hc-voice-assistant-ring ring-1"></span>
            <span class="hc-voice-assistant-ring ring-2"></span>
            <i class="fas" :class="iconClass"></i>
        </button>
    </div>
</template>

<style scoped>
.hc-voice-assistant {
    position: fixed;
    right: 24px;
    bottom: 24px;
    z-index: 1050;
    width: 52px;
    height: 52px;
}

.hc-voice-assistant-panel {
    position: absolute;
    right: 0;
    bottom: 64px;
    max-width: 260px;
    padding: 8px 12px;
    border-radius: 8px;
    background: #212529;
    color: #fff;
    font-size: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.hc-voice-assistant.panel-below .hc-voice-assistant-panel {
    bottom: auto;
    top: 64px;
}

.hc-voice-assistant-button {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: none;
    background: #0d6efd;
    color: #fff;
    font-size: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.25);
    cursor: grab;
    touch-action: none;
    user-select: none;
    transition: background-color 0.15s ease, transform 0.1s ease;
    --hc-voice-level: 0;
}

.hc-voice-assistant-button.is-dragging {
    cursor: grabbing;
}

.hc-voice-assistant-ring {
    position: absolute;
    inset: -6px;
    border-radius: 50%;
    border: 2px solid currentColor;
    opacity: 0;
    pointer-events: none;
}

.hc-voice-assistant-button.status-listening .hc-voice-assistant-ring,
.hc-voice-assistant-button.status-speaking .hc-voice-assistant-ring {
    animation: hc-voice-assistant-ring-pulse 1.8s ease-out infinite;
}

.hc-voice-assistant-button.status-speaking .hc-voice-assistant-ring {
    animation-duration: 1s;
}

.hc-voice-assistant-ring.ring-2 {
    animation-delay: 0.5s;
}

.hc-voice-assistant-button.status-speaking .hc-voice-assistant-ring.ring-2 {
    animation-delay: 0.3s;
}

@keyframes hc-voice-assistant-ring-pulse {
    0% {
        transform: scale(calc(1 + var(--hc-voice-level, 0) * 0.3));
        opacity: 0.6;
    }
    100% {
        transform: scale(1.9);
        opacity: 0;
    }
}

.hc-voice-assistant-button.status-listening,
.hc-voice-assistant-button.status-speaking {
    transform: scale(calc(1 + var(--hc-voice-level, 0) * 0.12));
}

.hc-voice-assistant-button.status-connecting {
    background: #6c757d;
}

.hc-voice-assistant-button.status-connecting::after {
    content: "";
    position: absolute;
    inset: -5px;
    border-radius: 50%;
    border: 2px solid transparent;
    border-top-color: #fff;
    animation: hc-voice-assistant-spin 0.8s linear infinite;
}

.hc-voice-assistant-button.status-listening {
    background: #0d6efd;
}

.hc-voice-assistant-button.status-speaking {
    background: #fd7e14;
}

.hc-voice-assistant-button.status-error {
    background: #dc3545;
    animation: hc-voice-assistant-shake 0.4s ease-in-out;
}

@keyframes hc-voice-assistant-spin {
    to {
        transform: rotate(360deg);
    }
}

@keyframes hc-voice-assistant-shake {
    0%,
    100% {
        transform: translateX(0);
    }
    25% {
        transform: translateX(-4px);
    }
    75% {
        transform: translateX(4px);
    }
}
</style>

<script>
import ApiService from "@/apis/api.service";

const INPUT_SAMPLE_RATE = 16000;
const OUTPUT_SAMPLE_RATE = 24000;
const FLUSH_INTERVAL_MS = 200;
const LEVEL_FFT_SIZE = 256;
const DRAG_THRESHOLD_PX = 4;
const POSITION_STORAGE_KEY = "hc-voice-assistant-position";

const RECORDER_WORKLET_SOURCE = `
class PCMRecorderProcessor extends AudioWorkletProcessor {
    process(inputs) {
        const input = inputs[0];
        if (input && input[0]) {
            this.port.postMessage(input[0].slice());
        }
        return true;
    }
}
registerProcessor("pcm-recorder-processor", PCMRecorderProcessor);
`;

function floatTo16BitPCM(chunks, totalLength) {
    const merged = new Float32Array(totalLength);
    let offset = 0;
    chunks.forEach((chunk) => {
        merged.set(chunk, offset);
        offset += chunk.length;
    });

    const pcm = new Int16Array(totalLength);
    for (let i = 0; i < totalLength; i++) {
        const s = Math.max(-1, Math.min(1, merged[i]));
        pcm[i] = s < 0 ? s * 0x8000 : s * 0x7fff;
    }
    return pcm;
}

function int16ArrayToBase64(int16Array) {
    const bytes = new Uint8Array(int16Array.buffer);
    let binary = "";
    const chunkSize = 0x8000;
    for (let i = 0; i < bytes.length; i += chunkSize) {
        binary += String.fromCharCode.apply(null, bytes.subarray(i, i + chunkSize));
    }
    return btoa(binary);
}

function base64ToFloat32Array(base64) {
    const binary = atob(base64);
    const bytes = new Uint8Array(binary.length);
    for (let i = 0; i < binary.length; i++) {
        bytes[i] = binary.charCodeAt(i);
    }
    const int16 = new Int16Array(bytes.buffer);
    const float32 = new Float32Array(int16.length);
    for (let i = 0; i < int16.length; i++) {
        float32[i] = int16[i] / 0x8000;
    }
    return float32;
}

export default {
    data() {
        return {
            status: "idle", // idle | connecting | active | error
            isSpeaking: false,
            errorMessage: "",

            ws: null,
            mediaStream: null,
            inputAudioContext: null,
            inputAnalyser: null,
            workletNode: null,
            pendingChunks: [],
            pendingLength: 0,
            flushTimer: null,

            playbackAudioContext: null,
            playbackAnalyser: null,
            playbackSources: [],
            nextPlaybackTime: 0,

            levelMeterFrame: null,
            levelBuffer: null,

            position: null,
            dragging: false,
            dragMoved: false,
            dragStart: { x: 0, y: 0 },
            dragOffset: { x: 0, y: 0 },
        };
    },

    computed: {
        buttonStatusClass() {
            if (this.status === "connecting") return "status-connecting";
            if (this.status === "error") return "status-error";
            if (this.status === "active") return this.isSpeaking ? "status-speaking" : "status-listening";
            return "status-idle";
        },

        iconClass() {
            if (this.status === "connecting") return "fa-spinner fa-spin";
            if (this.status === "active") return "fa-microphone-slash";
            return "fa-microphone";
        },

        wrapperStyle() {
            if (!this.position) {
                return {};
            }
            return {
                left: `${this.position.x}px`,
                top: `${this.position.y}px`,
                right: "auto",
                bottom: "auto",
            };
        },

        panelBelow() {
            return !!this.position && this.position.y < 140;
        },
    },

    mounted() {
        this.loadPosition();
        window.addEventListener("resize", this.clampPosition);
    },

    beforeUnmount() {
        this.stop();
        window.removeEventListener("resize", this.clampPosition);
        window.removeEventListener("pointermove", this.onPointerMove);
        window.removeEventListener("pointerup", this.onPointerUp);
    },

    methods: {
        // Drag to reposition

        loadPosition() {
            try {
                const saved = JSON.parse(localStorage.getItem(POSITION_STORAGE_KEY) || "null");
                if (saved && typeof saved.x === "number" && typeof saved.y === "number") {
                    this.position = saved;
                    this.$nextTick(() => this.clampPosition());
                }
            } catch (_) {
                this.position = null;
            }
        },

        clampPosition() {
            if (!this.position || !this.$refs.wrapper) return;
            const rect = this.$refs.wrapper.getBoundingClientRect();
            const maxX = Math.max(0, window.innerWidth - rect.width);
            const maxY = Math.max(0, window.innerHeight - rect.height);
            this.position = {
                x: Math.min(Math.max(this.position.x, 0), maxX),
                y: Math.min(Math.max(this.position.y, 0), maxY),
            };
        },

        onPointerDown(event) {
            if (event.button !== undefined && event.button !== 0) return;

            this.dragging = true;
            this.dragMoved = false;
            this.dragStart = { x: event.clientX, y: event.clientY };

            const rect = this.$refs.wrapper.getBoundingClientRect();
            this.dragOffset = { x: event.clientX - rect.left, y: event.clientY - rect.top };

            window.addEventListener("pointermove", this.onPointerMove);
            window.addEventListener("pointerup", this.onPointerUp);
        },

        onPointerMove(event) {
            if (!this.dragging) return;

            if (!this.dragMoved) {
                const dist = Math.hypot(event.clientX - this.dragStart.x, event.clientY - this.dragStart.y);
                if (dist < DRAG_THRESHOLD_PX) return;
                this.dragMoved = true;
            }

            const rect = this.$refs.wrapper.getBoundingClientRect();
            const x = Math.min(
                Math.max(event.clientX - this.dragOffset.x, 0),
                window.innerWidth - rect.width
            );
            const y = Math.min(
                Math.max(event.clientY - this.dragOffset.y, 0),
                window.innerHeight - rect.height
            );
            this.position = { x, y };
        },

        onPointerUp() {
            this.dragging = false;
            window.removeEventListener("pointermove", this.onPointerMove);
            window.removeEventListener("pointerup", this.onPointerUp);

            if (this.dragMoved && this.position) {
                localStorage.setItem(POSITION_STORAGE_KEY, JSON.stringify(this.position));
            }
        },

        onButtonClick() {
            if (this.dragMoved) {
                this.dragMoved = false;
                return;
            }
            this.toggle();
        },

        // Session lifecycle

        toggle() {
            if (this.status === "idle" || this.status === "error") {
                this.start();
            } else {
                this.stop();
            }
        },

        async start() {
            this.status = "connecting";
            this.errorMessage = "";

            let tokenData;
            try {
                const { data } = await ApiService.post("voice-assistant/token");
                tokenData = data;
            } catch (error) {
                this.fail(
                    error.response?.data?.message ||
                        error.message ||
                        "Erreur inattendue lors de la connexion à l'assistant vocal."
                );
                return;
            }

            if (!tokenData || !tokenData.success) {
                this.fail(tokenData?.message || "Assistant vocal indisponible.");
                return;
            }

            try {
                await this.startMicCapture();
            } catch (error) {
                this.logError("Impossible d'accéder au microphone", { error });
                this.fail(
                    "Impossible d'accéder au microphone. Autorisez l'accès au micro pour ce site."
                );
                return;
            }

            this.connectSocket(tokenData.token, tokenData.model);
        },

        async startMicCapture() {
            this.mediaStream = await navigator.mediaDevices.getUserMedia({
                audio: {
                    channelCount: 1,
                    echoCancellation: true,
                    noiseSuppression: true,
                },
            });

            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            this.inputAudioContext = new AudioContextClass({ sampleRate: INPUT_SAMPLE_RATE });

            const blob = new Blob([RECORDER_WORKLET_SOURCE], { type: "application/javascript" });
            const workletUrl = URL.createObjectURL(blob);
            try {
                await this.inputAudioContext.audioWorklet.addModule(workletUrl);
            } finally {
                URL.revokeObjectURL(workletUrl);
            }

            const source = this.inputAudioContext.createMediaStreamSource(this.mediaStream);

            this.inputAnalyser = this.inputAudioContext.createAnalyser();
            this.inputAnalyser.fftSize = LEVEL_FFT_SIZE;
            source.connect(this.inputAnalyser);

            this.workletNode = new AudioWorkletNode(this.inputAudioContext, "pcm-recorder-processor");
            this.workletNode.port.onmessage = (event) => {
                this.pendingChunks.push(event.data);
                this.pendingLength += event.data.length;
            };
            source.connect(this.workletNode);

            this.flushTimer = window.setInterval(() => this.flushPendingAudio(), FLUSH_INTERVAL_MS);
            this.startLevelMeter();
        },

        flushPendingAudio() {
            if (!this.pendingLength || !this.ws || this.ws.readyState !== WebSocket.OPEN) {
                return;
            }

            const pcm = floatTo16BitPCM(this.pendingChunks, this.pendingLength);
            this.pendingChunks = [];
            this.pendingLength = 0;

            this.ws.send(
                JSON.stringify({
                    realtimeInput: {
                        audio: {
                            mimeType: `audio/pcm;rate=${INPUT_SAMPLE_RATE}`,
                            data: int16ArrayToBase64(pcm),
                        },
                    },
                })
            );
        },

        connectSocket(token, model) {
            const url =
                "wss://generativelanguage.googleapis.com/ws/google.ai.generativelanguage.v1alpha.GenerativeService.BidiGenerateContentConstrained" +
                `?access_token=${encodeURIComponent(token)}`;

            this.ws = new WebSocket(url);

            this.ws.onopen = () => {
                this.logInfo("WebSocket Gemini Live ouvert");
                this.ws.send(
                    JSON.stringify({
                        setup: {
                            model,
                            generationConfig: {
                                responseModalities: ["AUDIO"],
                            },
                            systemInstruction: {
                                parts: [
                                    {
                                        text:
                                            "Tu es l'assistant vocal du CRM Heroes. Réponds de façon brève, " +
                                            "naturelle et utile, en français sauf si on te parle dans une autre langue.",
                                    },
                                ],
                            },
                        },
                    })
                );
            };

            this.ws.onmessage = async (event) => {
                const raw = event.data instanceof Blob ? await event.data.text() : event.data;
                let message;
                try {
                    message = JSON.parse(raw);
                } catch (error) {
                    this.logWarn("Message Gemini illisible", { raw });
                    return;
                }
                this.handleServerMessage(message);
            };

            this.ws.onerror = (event) => {
                this.logError("Erreur WebSocket Gemini Live", { event });
            };

            this.ws.onclose = (event) => {
                this.logInfo("WebSocket Gemini Live fermé", { code: event.code, reason: event.reason });
                if (this.status === "active" || this.status === "connecting") {
                    this.fail("La connexion à l'assistant vocal a été interrompue.");
                }
            };
        },

        handleServerMessage(message) {
            if (message.setupComplete) {
                this.status = "active";
                return;
            }

            if (message.serverContent) {
                const content = message.serverContent;

                if (content.interrupted) {
                    this.stopPlayback();
                }

                const parts = content.modelTurn?.parts || [];
                parts.forEach((part) => {
                    if (part.inlineData?.data) {
                        this.enqueuePlayback(part.inlineData.data);
                    }
                });

                if (content.turnComplete) {
                    this.isSpeaking = false;
                }
                return;
            }

            if (message.error) {
                this.logError("Erreur renvoyée par Gemini Live", { error: message.error });
                this.fail("L'assistant vocal a signalé une erreur.");
            }
        },

        ensurePlaybackContext() {
            if (!this.playbackAudioContext) {
                const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                this.playbackAudioContext = new AudioContextClass({ sampleRate: OUTPUT_SAMPLE_RATE });
                this.nextPlaybackTime = 0;

                this.playbackAnalyser = this.playbackAudioContext.createAnalyser();
                this.playbackAnalyser.fftSize = LEVEL_FFT_SIZE;
                this.playbackAnalyser.connect(this.playbackAudioContext.destination);
            }
            return this.playbackAudioContext;
        },

        enqueuePlayback(base64Data) {
            const ctx = this.ensurePlaybackContext();
            const float32 = base64ToFloat32Array(base64Data);
            if (!float32.length) {
                return;
            }

            const buffer = ctx.createBuffer(1, float32.length, OUTPUT_SAMPLE_RATE);
            buffer.copyToChannel(float32, 0);

            const source = ctx.createBufferSource();
            source.buffer = buffer;
            source.connect(this.playbackAnalyser);

            const startTime = Math.max(ctx.currentTime, this.nextPlaybackTime);
            source.start(startTime);
            this.nextPlaybackTime = startTime + buffer.duration;

            this.isSpeaking = true;
            this.playbackSources.push(source);
            source.onended = () => {
                this.playbackSources = this.playbackSources.filter((s) => s !== source);
            };
        },

        stopPlayback() {
            this.playbackSources.forEach((source) => {
                try {
                    source.stop();
                } catch (_) {
                    // Already stopped.
                }
            });
            this.playbackSources = [];
            this.isSpeaking = false;
            if (this.playbackAudioContext) {
                this.nextPlaybackTime = this.playbackAudioContext.currentTime;
            }
        },

        // Audio-reactive visual feedback

        startLevelMeter() {
            this.levelBuffer = new Uint8Array(LEVEL_FFT_SIZE);

            const step = () => {
                const analyser = this.isSpeaking ? this.playbackAnalyser : this.inputAnalyser;

                if (analyser && this.$refs.button) {
                    analyser.getByteTimeDomainData(this.levelBuffer);
                    let sumSquares = 0;
                    for (let i = 0; i < this.levelBuffer.length; i++) {
                        const normalized = (this.levelBuffer[i] - 128) / 128;
                        sumSquares += normalized * normalized;
                    }
                    const rms = Math.sqrt(sumSquares / this.levelBuffer.length);
                    const level = Math.min(1, rms * 4);
                    this.$refs.button.style.setProperty("--hc-voice-level", level.toFixed(3));
                }

                this.levelMeterFrame = window.requestAnimationFrame(step);
            };

            this.levelMeterFrame = window.requestAnimationFrame(step);
        },

        stopLevelMeter() {
            if (this.levelMeterFrame) {
                window.cancelAnimationFrame(this.levelMeterFrame);
                this.levelMeterFrame = null;
            }
            this.$refs.button?.style.setProperty("--hc-voice-level", 0);
        },

        // Teardown

        fail(message) {
            this.status = "error";
            this.errorMessage = message;
            this.teardown();
        },

        stop() {
            this.status = "idle";
            this.errorMessage = "";
            this.teardown();
        },

        teardown() {
            this.stopLevelMeter();

            if (this.flushTimer) {
                window.clearInterval(this.flushTimer);
                this.flushTimer = null;
            }
            this.pendingChunks = [];
            this.pendingLength = 0;

            if (this.ws) {
                const ws = this.ws;
                this.ws = null;
                try {
                    ws.close();
                } catch (_) {
                    // Already closed.
                }
            }

            if (this.workletNode) {
                this.workletNode.port.onmessage = null;
                this.workletNode.disconnect();
                this.workletNode = null;
            }
            this.inputAnalyser = null;

            if (this.mediaStream) {
                this.mediaStream.getTracks().forEach((track) => track.stop());
                this.mediaStream = null;
            }

            if (this.inputAudioContext) {
                this.inputAudioContext.close().catch(() => {});
                this.inputAudioContext = null;
            }

            this.stopPlayback();
            this.playbackAnalyser = null;
            if (this.playbackAudioContext) {
                this.playbackAudioContext.close().catch(() => {});
                this.playbackAudioContext = null;
            }
        },

        logInfo(message, data = {}) {
            console.log(`[VoiceAssistant ${new Date().toISOString()}] ${message}`, data);
        },

        logWarn(message, data = {}) {
            console.warn(`[VoiceAssistant WARNING ${new Date().toISOString()}] ${message}`, data);
        },

        logError(message, data = {}) {
            console.error(`[VoiceAssistant ERROR ${new Date().toISOString()}] ${message}`, data);
        },
    },
};
</script>
