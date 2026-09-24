import fs from 'fs';
import path from 'path';
import WebSocket from 'ws';

const RECORDINGS_DIR = path.resolve(process.cwd(), 'recordings');
fs.mkdirSync(RECORDINGS_DIR, { recursive: true });

function pcmToWavFile(rawFilePath) {
    if (!fs.existsSync(rawFilePath)) return null;

    const pcmBuffer = fs.readFileSync(rawFilePath);
    if (!pcmBuffer.length) return null;

    const wavFilePath = rawFilePath.replace(/\.pcm$/i, '.wav');
    const wavBuffer = Buffer.alloc(44 + pcmBuffer.length);

    wavBuffer.write('RIFF', 0, 4, 'ascii');
    wavBuffer.writeUInt32LE(36 + pcmBuffer.length, 4);
    wavBuffer.write('WAVE', 8, 4, 'ascii');
    wavBuffer.write('fmt ', 12, 4, 'ascii');
    wavBuffer.writeUInt32LE(16, 16);
    wavBuffer.writeUInt16LE(1, 20);
    wavBuffer.writeUInt16LE(1, 22);
    wavBuffer.writeUInt32LE(16000, 24);
    wavBuffer.writeUInt32LE(16000 * 2, 28);
    wavBuffer.writeUInt16LE(2, 32);
    wavBuffer.writeUInt16LE(16, 34);
    wavBuffer.write('data', 36, 4, 'ascii');
    wavBuffer.writeUInt32LE(pcmBuffer.length, 40);
    pcmBuffer.copy(wavBuffer, 44);

    fs.writeFileSync(wavFilePath, wavBuffer);
    return wavFilePath;
}

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
    const openingPrompt = process.env.GEMINI_OPENING_PROMPT
        || "L'appel vient de démarrer. Tu dois parler en premier : salue l'appelant et demande-lui comment tu peux l'aider.";

    const url = `wss://generativelanguage.googleapis.com/ws/google.ai.generativelanguage.v1alpha.GenerativeService.BidiGenerateContent?key=${apiKey}`;
    const ws = new WebSocket(url);
    const geminiRecordingPath = path.join(RECORDINGS_DIR, `gemini-${Date.now()}.pcm`);
    const geminiRecordingStream = fs.createWriteStream(geminiRecordingPath, { flags: 'w' });
    let recordingFinalized = false;

    const finalizeGeminiRecording = () => {
        if (recordingFinalized) return;
        recordingFinalized = true;

        const wavPath = pcmToWavFile(geminiRecordingPath);
        if (totalAudioReceived > 0) {
            console.log(`💾 Audio Gemini enregistré: ${wavPath || geminiRecordingPath}`);
        } else {
            console.log(`⚠️ Aucun audio Gemini reçu, fichier non généré: ${geminiRecordingPath}`);
        }
    };

    geminiRecordingStream.on('finish', finalizeGeminiRecording);
    geminiRecordingStream.on('close', finalizeGeminiRecording);

    let setupComplete = false;
    const pendingChunks = [];
    let resampleRemainder = Buffer.alloc(0);
    let totalAudioSent = 0;
    let totalAudioReceived = 0;
    let sessionStartTime = null;

    console.log(`🧠 [Gemini] Initialisation de la session avec le modèle: ${model}`);

    ws.on('open', () => {
        sessionStartTime = Date.now();
        console.log(`🤖 [Gemini] ✅ Connecté au serveur Gemini Live API`);
        console.log(`📍 URL: wss://generativelanguage.googleapis.com/ws/...`);

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

        const initialGreeting = {
            clientContent: {
                turns: [
                    {
                        role: "user",
                        parts: [{ text: "L'appel vient d'être décroché. Salue le client immédiatement et présente-toi en une phrase." }]
                    }
                ],
                turnComplete: true
            }
        };

        ws.send(JSON.stringify(initialGreeting));
    });

    ws.on('message', (rawMessage) => {
        try {
            const response = JSON.parse(rawMessage);

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

            const parts = response.serverContent?.modelTurn?.parts;
            if (parts) {
                for (const part of parts) {
                    if (part.inlineData && part.inlineData.mimeType.startsWith('audio/pcm')) {
                        const pcmBuffer = Buffer.from(part.inlineData.data, 'base64');
                        totalAudioReceived += pcmBuffer.length;
                        geminiRecordingStream.write(pcmBuffer);
                        console.log(`🎵 [Gemini] Reçu ${pcmBuffer.length} bytes audio (Total: ${totalAudioReceived} bytes)  ((((${onAudioData}))))`);
                        if (onAudioData) {
                            console.log(`📤 [Gemini -> Asterisk] Transmission de ${pcmBuffer.length} bytes audio vers Asterisk`);
                            onAudioData(pcmBuffer);
                        }
                    }
                }
            }

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
        try {
            geminiRecordingStream.end();
        } catch (err) {
            console.warn('[Gemini] Impossible de fermer le flux d’enregistrement:', err.message);
        }

        console.log(`[Gemini] Connexion fermée (${code}: ${reason})`);
        if (onClose) onClose();
    });

    function resample8kTo16k(input) {
        const inSamples = input.length / 2;
        const out = Buffer.alloc(inSamples * 4);
        for (let i = 0; i < inSamples; i++) {
            const s0 = input.readInt16LE(i * 2);
            const s1 = i + 1 < inSamples ? input.readInt16LE((i + 1) * 2) : s0;
            out.writeInt16LE(s0, i * 4);
            out.writeInt16LE(Math.round((s0 + s1) / 2), i * 4 + 2);
        }
        return out;
    }

    function transmitAudio(pcmBuffer) {
        if (ws.readyState !== WebSocket.OPEN) {
            console.warn(`[Gemini] ⚠️  WebSocket non OPEN (état: ${ws.readyState}), audio non envoyé`);
            return;
        }

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
