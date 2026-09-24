import WebSocket from 'ws';

// Asterisk AudioSocket envoie du PCM 16-bit à 8 kHz (slin),
// alors que la Live API de Gemini attend du 16 kHz en entrée.
const ASTERISK_SAMPLE_RATE = 8000;
const GEMINI_INPUT_RATE = 16000;

/**
 * Crée et gère la connexion WebSocket vers l'API Gemini Multimodal Live.
 *
 * @param {Object} options
 * @param {Function} options.onAudioData - Callback appelée lors de la réception d'un chunk audio de Gemini (PCM 16-bit 24kHz).
 * @param {Function} options.onClose - Callback exécuté à la fermeture de la connexion.
 * @returns {Object} Interface de contrôle du service Gemini.
 */
export function createGeminiSession({ onAudioData, onClose }) {
    const apiKey = process.env.GEMINI_API_KEY;
    const model = process.env.GEMINI_LIVE_MODEL || "models/gemini-2.5-flash-native-audio-preview-09-2025";
    // Message déclencheur : l'IA doit parler en premier au début de l'appel
    const openingPrompt = process.env.GEMINI_OPENING_PROMPT
        || "L'appel vient de démarrer. Tu dois parler en premier : salue l'appelant et demande-lui comment tu peux l'aider.";

    const url = `wss://generativelanguage.googleapis.com/ws/google.ai.generativelanguage.v1alpha.GenerativeService.BidiGenerateContent?key=${apiKey}`;
    const ws = new WebSocket(url);

    let setupComplete = false;
    const pendingChunks = [];          // Audio reçu avant le setupComplete -> mis en file d'attente
    let resampleRemainder = Buffer.alloc(0); // Octets impairs en attente entre deux chunks
    let totalAudioSent = 0;
    let totalAudioReceived = 0;
    let sessionStartTime = null;

    console.log(`🧠 [Gemini] Initialisation de la session avec le modèle: ${model}`);

    ws.on('open', () => {
        sessionStartTime = Date.now();
        console.log(`🤖 [Gemini] ✅ Connecté au serveur Gemini Live API`);
        console.log(`📍 URL: wss://generativelanguage.googleapis.com/ws/...`);

    // 1. Configuration initiale
    const setupMessage = {
        setup: {
            model: model,
            generationConfig: {
                responseModalities: ["AUDIO"],
                speechConfig: {
                    voiceConfig: {
                        prebuiltVoiceConfig: { voiceName: "Puck" }
                    }
                }
            },
            systemInstruction: {
                parts: [{
                    text: "Tu es un assistant téléphonique. Sois fluide et concis."
                }]
            }
        }
    };
    ws.send(JSON.stringify(setupMessage));

    // 2. AMORCE : On ordonne à l'IA de saluer en premier
    const initialGreeting = {
        clientContent: {
            turns: [
                {
                    role: "user",
                    parts: [{ text: "L'appel vient d'être décroché. Salue le client immédiatement et présente-toi en une phrase." }]
                }
            ],
            turnComplete: true // Indique à Gemini qu'il doit répondre tout de suite
        }
    };
    
    ws.send(JSON.stringify(initialGreeting));
});

    ws.on('message', (rawMessage) => {
        try {
            const response = JSON.parse(rawMessage);

            // Fin de l'initialisation : l'IA parle en premier, puis on purge l'audio en attente
            if (response.setupComplete) {
                setupComplete = true;
                console.log('[Gemini] Setup terminé. Déclenchement de la salutation (l\'IA parle en premier)...');

                if (openingPrompt) {
                    ws.send(JSON.stringify({
                        clientContent: {
                            turns: [{ role: 'user', parts: [{ text: openingPrompt }] }],
                            turnComplete: true
                        }
                    }));
                }

                for (const chunk of pendingChunks) {
                    transmitAudio(chunk);
                }
                pendingChunks.length = 0;
                return;
            }

            // Extraction des morceaux d'audio renvoyés par Gemini
            const parts = response.serverContent?.modelTurn?.parts;
            if (parts) {
                for (const part of parts) {
                    if (part.inlineData && part.inlineData.mimeType.startsWith('audio/pcm')) {
                        const pcmBuffer = Buffer.from(part.inlineData.data, 'base64');
                        totalAudioReceived += pcmBuffer.length;
                        console.log(`🎵 [Gemini] Reçu ${pcmBuffer.length} bytes audio (Total: ${totalAudioReceived} bytes)`);
                        if (onAudioData) {
                            onAudioData(pcmBuffer);
                        }
                    }
                }
            }

            // Logs utiles pour les messages non attendus (erreurs, goAway, etc.)
            const knownKeys = ['serverContent', 'setupComplete', 'usageMetadata', 'goAway', 'sessionResumptionUpdate'];
            const unknownKeys = Object.keys(response).filter(k => !knownKeys.includes(k));
            if (unknownKeys.length > 0) {
                console.log('[Gemini] Message non géré:', JSON.stringify(response).slice(0, 300));
            }
        } catch (err) {
            console.error('[Gemini] Erreur lors du parsing du message:', err);
        }
    });

    ws.on('error', (err) => {
        console.error('[Gemini] Erreur WebSocket:', err.message);
    });

    ws.on('close', (code, reason) => {
        console.log(`[Gemini] Connexion fermée (${code}: ${reason})`);
        if (onClose) onClose();
    });

    /**
     * Rééchantillonne du PCM 16-bit 8 kHz vers 16 kHz (interpolation linéaire).
     * @param {Buffer} input - Buffer contenant un nombre pair d'octets.
     * @returns {Buffer} Buffer 2x plus grand à 16 kHz.
     */
    function resample8kTo16k(input) {
        const inSamples = input.length / 2;
        const out = Buffer.alloc(inSamples * 4);
        for (let i = 0; i < inSamples; i++) {
            const s0 = input.readInt16LE(i * 2);
            const s1 = i + 1 < inSamples ? input.readInt16LE((i + 1) * 2) : s0;
            out.writeInt16LE(s0, i * 4);                       // sample pair
            out.writeInt16LE(Math.round((s0 + s1) / 2), i * 4 + 2); // interpolation
        }
        return out;
    }

    /**
     * Envoie réellement un chunk à Gemini après resampling 8k -> 16k.
     * @param {Buffer} pcmBuffer - Audio brut depuis Asterisk (8 kHz).
     */
    function transmitAudio(pcmBuffer) {
        if (ws.readyState !== WebSocket.OPEN) {
            console.warn(`[Gemini] ⚠️  WebSocket non OPEN (état: ${ws.readyState}), audio non envoyé`);
            return;
        }

        // Réaligne sur des échantillons complets (2 octets) entre deux chunks TCP
        let buf = Buffer.concat([resampleRemainder, pcmBuffer]);
        const usable = buf.length - (buf.length % 2);
        resampleRemainder = buf.subarray(usable);
        buf = buf.subarray(0, usable);
        if (buf.length === 0) return;

        const upsampled = resample8kTo16k(buf);
        const message = {
            realtimeInput: {
                mediaChunks: [{
                    mimeType: `audio/pcm;rate=${GEMINI_INPUT_RATE}`,
                    data: upsampled.toString('base64')
                }]
            }
        };

        totalAudioSent += upsampled.length;
        console.log(`🎤 [Gemini] Envoi de ${upsampled.length} bytes audio (Total: ${totalAudioSent} bytes)`);
        ws.send(JSON.stringify(message));
    }

    /**
     * Envoie un paquet audio PCM à Gemini.
     * Met en file d'attente tant que le setup Gemini n'est pas terminé.
     * @param {Buffer} pcmBuffer - Buffer audio au format PCM 16-bit 8 kHz (format Asterisk).
     */
    function sendAudioChunk(pcmBuffer) {
        if (!setupComplete) {
            pendingChunks.push(pcmBuffer);
            if (pendingChunks.length % 10 === 0) {
                console.log(`[Gemini] ⏳ Mise en file d'attente: ${pendingChunks.length} chunks (en attente du setup)`);
            }
            return;
        }
        transmitAudio(pcmBuffer);
    }

    /**
     * Ferme proprement la connexion WebSocket Gemini.
     */
    function close() {
        const elapsed = sessionStartTime ? ((Date.now() - sessionStartTime) / 1000).toFixed(2) : '?';
        console.log(`\n🧠 [Gemini] Fermeture de session`);
        console.log(`   Durée: ${elapsed}s`);
        console.log(`   Audio envoyé: ${totalAudioSent} bytes`);
        console.log(`   Audio reçu: ${totalAudioReceived} bytes\n`);
        
        if (ws.readyState === WebSocket.OPEN || ws.readyState === WebSocket.CONNECTING) {
            ws.close();
        }
    }

    return {
        sendAudioChunk,
        close
    };
}
