const WebSocket = require("ws");
const config = require("./config");

const INPUT_SAMPLE_RATE = 16000;

const RECORD_PROSPECT_INFO_TOOL = {
    name: "record_prospect_info",
    description:
        "Enregistre ou met à jour les informations recueillies sur le prospect pendant l'appel. " +
        "Peut être appelé plusieurs fois au fil de la conversation : n'inclue que les champs " +
        "nouvellement obtenus ou confirmés, pas besoin de répéter ce qui a déjà été envoyé.",
    parameters: {
        type: "OBJECT",
        properties: {
            first_name: { type: "STRING" },
            last_name: { type: "STRING" },
            email: { type: "STRING" },
            phone_number: { type: "STRING" },
            mobile_phone_number: { type: "STRING" },
            company_name: { type: "STRING" },
            job_title: { type: "STRING" },
            street: { type: "STRING" },
            postal_code: { type: "STRING" },
            city: { type: "STRING" },
            country: { type: "STRING" },
            budget: { type: "STRING" },
            project: { type: "STRING", description: "Le besoin ou projet exprimé par le prospect." },
            summary: { type: "STRING", description: "Résumé bref de l'appel." },
            qualification: {
                type: "STRING",
                enum: ["hot", "warm", "cold", "unqualified", "unknown"],
            },
            needs: { type: "ARRAY", items: { type: "STRING" } },
            objections: { type: "ARRAY", items: { type: "STRING" } },
            next_steps: { type: "ARRAY", items: { type: "STRING" } },
        },
    },
};

const SYSTEM_INSTRUCTION = `Tu es l'assistant vocal du CRM Heroes. Tu appelles un prospect pour le compte
de l'utilisateur du CRM, qui est aussi en ligne sur cet appel et peut
intervenir à tout moment. Présente-toi brièvement comme un assistant IA
qui appelle pour le compte de l'utilisateur, explique la raison de
l'appel, puis pose les questions nécessaires pour qualifier le prospect :
coordonnées, entreprise, besoin, budget. Reste naturelle, brève et polie,
en français sauf si le prospect s'exprime dans une autre langue. Appelle
l'outil record_prospect_info dès que tu obtiens ou confirmes une
information, sans attendre la fin de l'appel. N'invente jamais une
information que le prospect n'a pas confirmée.`;

/**
 * One instance per phone call: owns the Gemini Live WebSocket, relays
 * audio in both directions, accumulates the transcript and the fields
 * gathered via the record_prospect_info tool, and reports the outcome to
 * Laravel once the call ends.
 *
 * The wire protocol (setup/realtimeInput/serverContent) mirrors
 * resources/js/components/utils/VoiceAssistant.vue, already validated
 * against the Gemini Live API. Function-calling message shapes
 * (toolCall/clientContent/toolResponse) match the official Live API
 * reference (https://ai.google.dev/api/live), but have only been
 * exercised via simulate-call.js (text mode) — the AUDIO path used by
 * real calls shares all of this code and only swaps the transport, so a
 * clean simulate-call.js run is a good signal, but a real call should
 * still be checked once FreeSWITCH is in place.
 */
class GeminiCallBridge {
    constructor({ callUuid, prospectId }) {
        this.callUuid = callUuid;
        this.prospectId = prospectId;
        this.ws = null;
        this.ready = false;
        this.collected = {};
        this.transcript = [];
        this.onAudio = null; // (base64Pcm24k) => void, set by the caller (ws-server.js)
        this.onText = null; // (text) => void, set by the caller (simulate-call.js); fed
        // from outputAudioTranscription — the native-audio models used here only
        // support the AUDIO response modality (confirmed against a live session:
        // requesting TEXT gets the connection closed with code 1007), so text mode
        // just means "connect normally but don't play the audio back", not a
        // different modality.
        this.onReady = null; // () => void, fires once Gemini has acked the setup message
        this.onTurnComplete = null; // () => void, fires once Gemini has finished responding to a turn
    }

    connect() {
        const url =
            `wss://generativelanguage.googleapis.com/ws/google.ai.generativelanguage.${config.gemini.apiVersion}.GenerativeService.BidiGenerateContent` +
            `?key=${encodeURIComponent(config.gemini.apiKey)}`;

        this.ws = new WebSocket(url);

        this.ws.on("open", () => {
            console.log(`[Gemini ${this.callUuid}] WebSocket open.`);
            this.ws.send(
                JSON.stringify({
                    setup: {
                        model: config.gemini.model,
                        generationConfig: { responseModalities: ["AUDIO"] },
                        systemInstruction: { parts: [{ text: SYSTEM_INSTRUCTION }] },
                        tools: [{ functionDeclarations: [RECORD_PROSPECT_INFO_TOOL] }],
                        inputAudioTranscription: {},
                        outputAudioTranscription: {},
                    },
                })
            );
        });

        this.ws.on("message", (data) => this._handleMessage(data));
        this.ws.on("error", (error) => console.error(`[Gemini ${this.callUuid}] WebSocket error.`, error));
        this.ws.on("close", (code, reason) => {
            console.log(`[Gemini ${this.callUuid}] WebSocket closed.`, code, reason?.toString());
        });
    }

    /** @param {Buffer} pcm16kBuffer Raw L16 PCM at 16kHz, as received from FreeSWITCH. */
    pushAudio(pcm16kBuffer) {
        if (!this.ws || this.ws.readyState !== WebSocket.OPEN) {
            return;
        }
        this.ws.send(
            JSON.stringify({
                realtimeInput: {
                    audio: {
                        mimeType: `audio/pcm;rate=${INPUT_SAMPLE_RATE}`,
                        data: pcm16kBuffer.toString("base64"),
                    },
                },
            })
        );
    }

    /** Text equivalent of pushAudio(), used by simulate-call.js. */
    pushText(text) {
        if (!this.ws || this.ws.readyState !== WebSocket.OPEN) {
            return;
        }
        this.transcript.push(`Interlocuteur (simulé): ${text}`);
        this.ws.send(
            JSON.stringify({
                clientContent: {
                    turns: [{ role: "user", parts: [{ text }] }],
                    turnComplete: true,
                },
            })
        );
    }

    _handleMessage(raw) {
        let message;
        try {
            message = JSON.parse(raw.toString());
        } catch (error) {
            console.warn(`[Gemini ${this.callUuid}] Unreadable message.`, raw.toString().slice(0, 200));
            return;
        }

        if (message.setupComplete) {
            console.log(`[Gemini ${this.callUuid}] Setup complete.`);
            this.ready = true;
            if (this.onReady) {
                this.onReady();
            }
            return;
        }

        if (message.toolCall) {
            this._handleToolCall(message.toolCall);
            return;
        }

        if (message.serverContent) {
            const content = message.serverContent;

            const parts = content.modelTurn?.parts || [];
            parts.forEach((part) => {
                if (part.inlineData?.data && this.onAudio) {
                    this.onAudio(part.inlineData.data);
                }
                if (part.text) {
                    this.transcript.push(`IA: ${part.text}`);
                    if (this.onText) {
                        this.onText(part.text);
                    }
                }
            });

            if (content.inputTranscription?.text) {
                this.transcript.push(`Interlocuteur: ${content.inputTranscription.text}`);
            }
            if (content.outputTranscription?.text) {
                this.transcript.push(`IA: ${content.outputTranscription.text}`);
                if (this.onText) {
                    this.onText(content.outputTranscription.text);
                }
            }

            if (content.turnComplete && this.onTurnComplete) {
                this.onTurnComplete();
            }
        }
    }

    _handleToolCall(toolCall) {
        const calls = toolCall.functionCalls || [];

        calls.forEach((call) => {
            if (call.name === "record_prospect_info" && call.args) {
                Object.assign(this.collected, call.args);
            }
        });

        if (!this.ws || this.ws.readyState !== WebSocket.OPEN || !calls.length) {
            return;
        }

        this.ws.send(
            JSON.stringify({
                toolResponse: {
                    functionResponses: calls.map((call) => ({
                        id: call.id,
                        response: { result: "ok" },
                    })),
                },
            })
        );
    }

    /** Shape expected by App\Http\Controllers\API\AiPhoneAgentController::ingest(). */
    buildAnalysis() {
        const { summary, qualification, needs, objections, next_steps: nextSteps, ...extracted } = this.collected;

        return {
            summary: summary || null,
            qualification: qualification || "unknown",
            needs: needs || [],
            objections: objections || [],
            next_steps: nextSteps || [],
            extracted,
        };
    }

    async finalize() {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            try {
                this.ws.close();
            } catch (_) {
                // Already closed.
            }
        }

        const body = {
            call_uuid: this.callUuid,
            prospect_id: this.prospectId,
            transcript: this.transcript.join("\n"),
            analysis: this.buildAnalysis(),
        };

        try {
            const response = await fetch(`${config.laravelBaseUrl}/api/webhooks/ai-phone-agent/calls`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-AI-Agent-Secret": config.sharedSecret,
                },
                body: JSON.stringify(body),
            });

            if (!response.ok) {
                console.error(`[Gemini ${this.callUuid}] Laravel ingest failed.`, response.status, await response.text());
            } else {
                console.log(`[Gemini ${this.callUuid}] Call outcome ingested by Laravel.`);
            }
        } catch (error) {
            console.error(`[Gemini ${this.callUuid}] Failed to reach Laravel.`, error);
        }
    }
}

module.exports = { GeminiCallBridge, RECORD_PROSPECT_INFO_TOOL };
