import path from 'path';
import WebSocket from 'ws';
import { RECORDINGS_DIR, createPcmRecorder } from '../utils/audio.util.js';

// Asterisk AudioSocket envoie du PCM 16-bit à 8 kHz (slin), alors que la Live
// API de Gemini attend du 16 kHz en entrée et produit du 24 kHz en sortie.
const ASTERISK_SAMPLE_RATE = 8000;
const GEMINI_INPUT_SAMPLE_RATE = ASTERISK_SAMPLE_RATE * 2;
const GEMINI_OUTPUT_SAMPLE_RATE = 24000;

const GEMINI_WS_URL =
    'wss://generativelanguage.googleapis.com/ws/google.ai.generativelanguage.v1alpha.GenerativeService.BidiGenerateContent';

const DEFAULT_MODEL = 'models/gemini-2.5-flash-native-audio-preview-09-2025';
const DEFAULT_VOICE = 'Puck';
const DEFAULT_OPENING_PROMPT =
    "L'appel vient de démarrer. Tu dois parler en premier : salue l'appelant et demande-lui comment tu peux l'aider.";
const SYSTEM_INSTRUCTION = 'Tu es un assistant téléphonique. Sois fluide et concis.';

// Champs de réponse Gemini traités explicitement (sert à détecter les messages
// non gérés sans confondre avec une réponse audio).
const HANDLED_RESPONSE_KEYS = [
    'serverContent',
    'setupComplete',
    'usageMetadata',
    'goAway',
    'sessionResumptionUpdate'
];

/**
 * Construit le message `setup` initial de la session Gemini Live.
 */
function buildSetupMessage(model) {
    return {
        setup: {
            model,
            generationConfig: {
                responseModalities: ['AUDIO'],
                speechConfig: {
                    voiceConfig: {
                        prebuiltVoiceConfig: { voiceName: DEFAULT_VOICE }
                    }
                }
            },
            systemInstruction: {
                parts: [{ text: SYSTEM_INSTRUCTION }]
            }
        }
    };
}

/**
 * Construit un tour utilisateur envoyé au modèle (ex: déclenchement de la salutation).
 */
function buildUserTurn(text) {
    return {
        clientContent: {
            turns: [{ role: 'user', parts: [{ text }] }],
            turnComplete: true
        }
    };
}

/**
 * Double la fréquence d'échantillonnage (8 kHz -> 16 kHz) par interpolation
 * linéaire simple : chaque échantillon est suivi de la moyenne avec le suivant.
 *
 * @param {Buffer} input - PCM 16 bits signé little-endian
 * @returns {Buffer} PCM 16 bits à la fréquence doublée
 */
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

/**
 * Rééchantillonne du PCM 16-bit 24 kHz vers 8 kHz.
 * Le rapport est 3:1, donc on prend un échantillon sur trois.
 *
 * @param {Buffer} input - PCM 16 bits signé little-endian, 24 kHz
 * @returns {Buffer} PCM 16 bits à 8 kHz
 */
function resample24kTo8k(input) {
    const inSamples = input.length / 2;
    const outSamples = Math.max(1, Math.floor(inSamples / 3));
    const out = Buffer.alloc(outSamples * 2);

    for (let i = 0; i < outSamples; i++) {
        const srcIndex = i * 3;
        const sample = srcIndex < inSamples ? input.readInt16LE(srcIndex * 2) : 0;
        out.writeInt16LE(sample, i * 2);
    }

    return out;
}

/**
 * Crée et gère la connexion WebSocket vers l'API Gemini Multimodal Live.
 *
 * @param {Object} options
 * @param {Function} [options.onAudioData] - Appelée pour chaque chunk audio reçu de Gemini (PCM 16-bit 24 kHz).
 * @param {Function} [options.onClose] - Callback exécuté à la fermeture de la connexion.
 * @returns {Object} Interface de contrôle du service Gemini.
 */
export function createGeminiSession({ onAudioData, onClose } = {}) {
    const apiKey = process.env.GEMINI_API_KEY;
    const model = process.env.GEMINI_LIVE_MODEL || DEFAULT_MODEL;
    const openingPrompt = process.env.GEMINI_OPENING_PROMPT || DEFAULT_OPENING_PROMPT;

    const ws = new WebSocket(`${GEMINI_WS_URL}?key=${apiKey}`);
    const recordingPath = path.join(RECORDINGS_DIR, `gemini-${Date.now()}.pcm`);
    const recorder = createPcmRecorder({
        filePath: recordingPath,
        sampleRate: GEMINI_OUTPUT_SAMPLE_RATE
    });

    let setupComplete = false;
    let closed = false;
    const pendingChunks = [];
    let resampleRemainder = Buffer.alloc(0);
    let totalAudioSent = 0;
    let totalAudioReceived = 0;
    let sessionStartTime = null;

    console.log(`🧠 [Gemini] Initialisation de la session avec le modèle: ${model}`);

    /** Envoie un buffer PCM déjà redimensionné à Gemini (ou le met en file d'attente). */
    function transmitAudio(pcmBuffer) {
        if (ws.readyState !== WebSocket.OPEN) {
            console.warn(`[Gemini] ⚠️  WebSocket non OPEN (état: ${ws.readyState}), audio non envoyé`);
            return;
        }

        // Un paquet AudioSocket peut être impair : on conserve l'octet orphelin
        // pour le recoller au chunk suivant.
        let buffer = Buffer.concat([resampleRemainder, pcmBuffer]);
        const usableLength = buffer.length - (buffer.length % 2);
        resampleRemainder = buffer.subarray(usableLength);
        buffer = buffer.subarray(0, usableLength);
        if (buffer.length === 0) return;

        const upsampled = resample8kTo16k(buffer);
        totalAudioSent += upsampled.length;
        console.log(`🎤 [Gemini] Envoi de ${upsampled.length} bytes audio (Total: ${totalAudioSent} bytes)`);

        ws.send(JSON.stringify({
            realtimeInput: {
                mediaChunks: [{
                    mimeType: `audio/pcm;rate=${GEMINI_INPUT_SAMPLE_RATE}`,
                    data: upsampled.toString('base64')
                }]
            }
        }));
    }

    /** Gère l'audio du modèle : enregistrement + réinjection vers Asterisk. */
    function handleModelAudio(pcmBuffer) {
        totalAudioReceived += pcmBuffer.length;
        recorder.write(pcmBuffer);
        console.log(`🎵 [Gemini] Reçu ${pcmBuffer.length} bytes audio (Total: ${totalAudioReceived} bytes)`);

        if (!onAudioData) return;

        const pcm8k = resample24kTo8k(pcmBuffer);
        console.log(`📤 [Gemini -> Asterisk] Transmission de ${pcm8k.length} bytes audio (8 kHz) vers Asterisk`);
        onAudioData(pcm8k);
    }

    /** Extrait les chunks audio d'un message `serverContent` de Gemini. */
    function handleServerContent(response) {
        const parts = response.serverContent?.modelTurn?.parts;
        if (!parts) return;

        for (const part of parts) {
            const inlineData = part.inlineData;
            if (inlineData?.mimeType?.startsWith('audio/pcm')) {
                handleModelAudio(Buffer.from(inlineData.data, 'base64'));
            }
        }
    }

    ws.on('open', () => {
        sessionStartTime = Date.now();
        console.log('🤖 [Gemini] ✅ Connecté au serveur Gemini Live API');
        ws.send(JSON.stringify(buildSetupMessage(model)));
    });

    ws.on('message', (rawMessage) => {
        let response;
        try {
            response = JSON.parse(rawMessage);
        } catch (err) {
            console.error('[Gemini] Erreur lors du parsing du message:', err);
            return;
        }

        if (response.setupComplete) {
            setupComplete = true;
            console.log('[Gemini] Setup terminé. Déclenchement de la salutation (l\'IA parle en premier)...');

            if (openingPrompt) {
                ws.send(JSON.stringify(buildUserTurn(openingPrompt)));
            }

            // Libère l'audio d'Asterisk accumulé pendant le setup.
            for (const chunk of pendingChunks) transmitAudio(chunk);
            pendingChunks.length = 0;
            return;
        }

        handleServerContent(response);

        const unknownKeys = Object.keys(response).filter(key => !HANDLED_RESPONSE_KEYS.includes(key));
        if (unknownKeys.length > 0) {
            console.log('[Gemini] Message non géré:', JSON.stringify(response).slice(0, 300));
        }
    });

    ws.on('error', (err) => {
        console.error('[Gemini] Erreur WebSocket:', err.message);
    });

    ws.on('close', (code, reason) => {
        console.log(`[Gemini] Connexion fermée (${code}: ${reason})`);

        recorder.end()
            .then((wavPath) => {
                if (wavPath) console.log(`💾 Audio Gemini enregistré: ${wavPath}`);
                else console.log(`⚠️ Aucun audio Gemini reçu, fichier non généré: ${recordingPath}`);
            })
            .catch((err) => {
                console.warn('[Gemini] Impossible de finaliser l\'enregistrement:', err.message);
            });

        if (onClose) onClose();
    });

    /** Transmet un chunk PCM 8 kHz d'Asterisk vers Gemini (avec mise en attente avant setup). */
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

    /** Ferme la session Gemini (le résumé de session n'est logué qu'une fois). */
    function close() {
        if (closed) return;
        closed = true;

        const elapsed = sessionStartTime ? ((Date.now() - sessionStartTime) / 1000).toFixed(2) : '?';
        console.log('\n🧠 [Gemini] Fermeture de session');
        console.log(`   Durée: ${elapsed}s`);
        console.log(`   Audio envoyé: ${totalAudioSent} bytes`);
        console.log(`   Audio reçu: ${totalAudioReceived} bytes\n`);

        if (ws.readyState === WebSocket.OPEN || ws.readyState === WebSocket.CONNECTING) {
            ws.close();
        }
    }

    return { sendAudioChunk, close };
}
