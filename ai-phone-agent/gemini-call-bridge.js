const WebSocket = require("ws");
const config = require("./config");
const { CallArchive } = require("./call-archive");
const transcriptHub = require("./transcript-hub");
const { extractTranscript } = require("./post-call-extractor");

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
            website_url: { type: "STRING" },
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
en français sauf si le prospect s'exprime dans une autre langue.

Règle stricte sur l'outil record_prospect_info : appelle-le après CHAQUE
message du prospect qui contient au moins une information utile (nom,
ville, téléphone, email, entreprise, site web, besoin, budget...), même
une seule donnée isolée — n'attends jamais un message suivant ni la fin
de l'appel pour enregistrer une information déjà donnée. Si le prospect
donne trois informations dans une même phrase, inclus les trois dans le
même appel d'outil. N'invente jamais une information que le prospect n'a
pas confirmée, et ne devine jamais une valeur approximative (par exemple
ne convertis pas un âge en date de naissance).`;

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
    constructor({ callUuid, prospectId, systemContext = "" }) {
        this.callUuid = callUuid;
        this.prospectId = prospectId;
        this.ws = null;
        this.ready = false;
        this.collected = {};
        this.transcript = [];
        this.archive = new CallArchive(callUuid);
        this.systemContext = systemContext;
        this.paused = false;
        this.closedByUs = false;
        this.reconnects = 0;
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
                        systemInstruction: { parts: [{ text: `${SYSTEM_INSTRUCTION}\n\nContexte CRM de cet appel : ${this.systemContext}` }] },
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
            // Live sessions have a finite lifetime. Reconnect once the socket
            // closes unexpectedly and carry the factual transcript as context.
            if (!this.closedByUs && this.reconnects < 3) {
                this.reconnects += 1;
                setTimeout(() => { this.systemContext += `\nConversation déjà tenue:\n${this.transcript.slice(-40).join("\n")}`; this.connect(); }, 500 * this.reconnects);
            }
        });
    }

    /** @param {Buffer} pcm16kBuffer Raw L16 PCM at 16kHz, as received from FreeSWITCH. */
    pushAudio(pcm16kBuffer) {
        if (this.paused || !this.ws || this.ws.readyState !== WebSocket.OPEN) {
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
                    this._appendTranscript("assistant", part.text);
                    if (this.onText) {
                        this.onText(part.text);
                    }
                }
            });

            if (content.inputTranscription?.text) {
                this._appendTranscript("caller", content.inputTranscription.text);
            }
            if (content.outputTranscription?.text) {
                this._appendTranscript("assistant", content.outputTranscription.text);
                if (this.onText) {
                    this.onText(content.outputTranscription.text);
                }
            }

            if (content.turnComplete && this.onTurnComplete) {
                this.onTurnComplete();
            }
            if (content.interrupted && this.onInterrupted) this.onInterrupted();
        }
    }

    _appendTranscript(speaker, text) {
        this.transcript.push(`${speaker === "assistant" ? "IA" : "Interlocuteur"}: ${text}`);
        this.archive.event(speaker, text);
        transcriptHub.publish({ type: "transcript", call_uuid: this.callUuid, speaker, text, at: new Date().toISOString() });
    }

    setPaused(paused) { this.paused = paused; }
    recordInbound(buffer) { this.archive.writeInbound(buffer); }
    recordOutbound(buffer) { this.archive.writeOutbound(buffer); }

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
        this.closedByUs = true;
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            try {
                this.ws.close();
            } catch (_) {
                // Already closed.
            }
        }

        const transcript = this.transcript.join("\n");
        let analysis = this.buildAnalysis();
        try {
            const extraction = await extractTranscript(transcript);
            if (extraction) analysis = {
                ...analysis,
                post_call_extraction: extraction,
                summary: extraction.resume_appel || analysis.summary,
                qualification: ({ chaud: "hot", tiede: "warm", froid: "cold" })[extraction.niveau_interet] || analysis.qualification,
                needs: extraction.besoin_exprime ? [extraction.besoin_exprime] : analysis.needs,
                objections: extraction.objections || analysis.objections,
                next_steps: extraction.action_suivante ? [extraction.action_suivante] : analysis.next_steps,
                extracted: { ...analysis.extracted, budget: extraction.budget || analysis.extracted.budget, project: extraction.besoin_exprime || analysis.extracted.project },
            };
        } catch (error) {
            console.error(`[Gemini ${this.callUuid}] Post-call extraction failed.`, error.message);
        }
        const body = {
            call_uuid: this.callUuid,
            prospect_id: this.prospectId,
            transcript,
            analysis,
            test_mode: config.testMode,
        };
        this.archive.close();

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
