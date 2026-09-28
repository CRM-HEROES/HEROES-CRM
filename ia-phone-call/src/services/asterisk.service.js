import path from 'path';
import { createGeminiSession } from './gemini.service.js';
import {
    RECORDINGS_DIR,
    createPcmRecorder,
    createMixedPcmRecorder,
    writeAudioSocketPacket
} from '../utils/audio.util.js';
import {
    getCurrentOutgoingContext,
    getCurrentOutgoingPrompt,
    getOutgoingCallContext,
    releaseOutgoingCallContext
} from './outgoing-call.service.js';

// Asterisk AudioSocket transporte du PCM 8 kHz 16 bits (slin).
const ASTERISK_SAMPLE_RATE = 8000;
const UUID_PACKET_TYPE = 0x01;
const AUDIO_PACKET_TYPE = 0x10;
const PACKET_HEADER_SIZE = 3;

// app_audiosocket.c interrompt la connexion si AUCUNE activité (frame venant du
// canal OU du socket) n'a lieu pendant MAX_WAIT_TIMEOUT_MSEC (2000 ms). Un
// correspondant silencieux (silence suppression mobile) et le temps de démarrage
// de Gemini suffisent donc à faire échouer l'appel : on émet du silence tant que
// Gemini n'a rien envoyé.
const SILENCE_FRAME = Buffer.alloc(320); // 40 ms de PCM 8 kHz 16 bits
const SILENCE_KEEPALIVE_INTERVAL_MS = 500;
const AUDIO_IDLE_TIMEOUT_MS = 1000;

const PROGRESS_LOG_EVERY_N_PACKETS = 10;

/**
 * Extrait les payloads audio complets d'un buffer AudioSocket.
 * Protocole : byte 0 = type de message, bytes 1-2 = longueur du payload
 * (Big-Endian 16 bits), puis le payload PCM brut.
 *
 * @param {Buffer} buffer - Données reçues, potentiellement incomplètes
 * @returns {{ payloads: Buffer[], remaining: Buffer }} Payloads complets + reste partiel à conserver
 */
export function extractAudioPayloads(buffer) {
    const { packets, remaining } = extractAudioSocketPackets(buffer);
    return {
        payloads: packets
            .filter((packet) => packet.type === AUDIO_PACKET_TYPE)
            .map((packet) => packet.payload),
        remaining
    };
}

export function extractAudioSocketPackets(buffer) {
    const packets = [];
    let remaining = buffer;

    while (remaining.length >= PACKET_HEADER_SIZE) {
        const messageType = remaining.readUInt8(0);
        const payloadLength = remaining.readUInt16BE(1);
        const packetLength = PACKET_HEADER_SIZE + payloadLength;

        if (remaining.length < packetLength) break;

        packets.push({
            type: messageType,
            payload: remaining.subarray(PACKET_HEADER_SIZE, packetLength)
        });

        remaining = remaining.subarray(packetLength);
    }

    return { packets, remaining };
}

function decodeAudioSocketUuid(payload) {
    if (payload.length === 16) {
        const hex = payload.toString('hex');
        return `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`;
    }

    const value = payload.toString('utf8').replace(/\0/g, '').trim();
    return value || null;
}

function logCallStart(callStartTime, recordingPath) {
    console.log(`\n${'='.repeat(70)}`);
    console.log('📞 [APPEL] Nouvel appel entrant depuis Asterisk (AudioSocket)');
    console.log(`⏰ Début: ${callStartTime.toLocaleTimeString('fr-FR')}`);
    console.log('📍 Distance: Asterisk → ia-phone-call → Gemini');
    console.log(`💾 Enregistrement: ${recordingPath}`);
    console.log(`${'='.repeat(70)}\n`);
}

function logCallSummary({ callStartTime, totalPacketsReceived, totalBytesReceived, wavPath, mixedWavPath }) {
    const elapsed = ((Date.now() - callStartTime.getTime()) / 1000).toFixed(2);

    console.log(`\n${'='.repeat(70)}`);
    console.log('📊 [APPEL] Appel terminé');
    console.log(`   Durée: ${elapsed}s`);
    console.log(`   Paquets reçus: ${totalPacketsReceived}`);
    console.log(`   Bytes total: ${totalBytesReceived}`);
    console.log(`   Débit moyen: ${(totalBytesReceived / parseFloat(elapsed) / 1024).toFixed(1)} KB/s`);
    console.log(`   Fichier WAV (appelant): ${wavPath || 'non généré'}`);
    console.log(`   Fichier WAV (mixé): ${mixedWavPath || 'non généré'}`);
    console.log(`${'='.repeat(70)}\n`);
}

/**
 * Traite une connexion entrante TCP venant du module AudioSocket d'Asterisk :
 * audio entrant -> Gemini, audio Gemini -> Asterisk, plus l'enregistrement de l'appel.
 *
 * @param {import('net').Socket} asteriskSocket
 */
export function handleAsteriskConnection(asteriskSocket) {
    const callStartTime = new Date();
    const recordingPath = path.join(
        RECORDINGS_DIR,
        `call-${callStartTime.toISOString().replace(/[:.]/g, '-')}.pcm`
    );
    const recorder = createPcmRecorder({ filePath: recordingPath, sampleRate: ASTERISK_SAMPLE_RATE });
    // Enregistrement unique contenant les deux voix mixées (appelant + IA).
    const mixedRecorder = createMixedPcmRecorder({
        filePath: recordingPath.replace(/\.pcm$/i, '-mixed.pcm'),
        sampleRate: ASTERISK_SAMPLE_RATE
    });

    let audioBuffer = Buffer.alloc(0);
    let totalBytesReceived = 0;
    let totalPacketsReceived = 0;
    let lastAudioSentAt = Date.now();
    let callUuid = null;
    let callContext = null;
    let geminiSession = null;

    const sendToAsterisk = (pcmBuffer) => {
        lastAudioSentAt = Date.now();
        writeAudioSocketPacket(asteriskSocket, pcmBuffer);
    };

    const silenceKeepAlive = setInterval(() => {
        // Gemini est en train de parler : ne rien intercaler dans le flux.
        if (Date.now() - lastAudioSentAt < AUDIO_IDLE_TIMEOUT_MS) return;
        sendToAsterisk(SILENCE_FRAME);
    }, SILENCE_KEEPALIVE_INTERVAL_MS);

    const stopCall = () => {
        clearInterval(silenceKeepAlive);
        recorder.end();
        mixedRecorder.end();
    };

    logCallStart(callStartTime, recordingPath);

    const ensureGeminiSession = () => {
        if (geminiSession) return geminiSession;

        callContext = callUuid
            ? getOutgoingCallContext(callUuid)
            : getCurrentOutgoingContext();

        geminiSession = createGeminiSession({
            // Audio produit par Gemini -> réinjection dans le canal Asterisk.
            onAudioData: (pcmBuffer) => {
                console.log(`🎵 [Gemini -> Asterisk] Réception de ${pcmBuffer.length} bytes depuis Gemini`);
                console.log(`📤 [Asterisk -> Kavkom] Envoi vers le trunk de ${pcmBuffer.length} bytes audio`);
                mixedRecorder.write(pcmBuffer, 'gemini');
                sendToAsterisk(pcmBuffer);
            },
            onClose: () => {
                console.log('[Gemini] ⚠️  Fermeture de Gemini - Fermeture du socket Asterisk');
                stopCall();
                asteriskSocket.end();
            },
            openingPrompt: callContext.openingPrompt || getCurrentOutgoingPrompt(),
            callContext,
        });

        return geminiSession;
    };

    asteriskSocket.on('data', (chunk) => {
        const { packets, remaining } = extractAudioSocketPackets(Buffer.concat([audioBuffer, chunk]));
        audioBuffer = remaining;

        for (const packet of packets) {
            if (packet.type === UUID_PACKET_TYPE) {
                callUuid = decodeAudioSocketUuid(packet.payload);
                console.log(`[Asterisk] AudioSocket UUID: ${callUuid || 'inconnu'}`);
                continue;
            }

            if (packet.type !== AUDIO_PACKET_TYPE) {
                continue;
            }

            const payload = packet.payload;
            totalBytesReceived += payload.length;
            totalPacketsReceived++;
            recorder.write(payload);
            mixedRecorder.write(payload, 'caller');

            if (totalPacketsReceived % PROGRESS_LOG_EVERY_N_PACKETS === 0) {
                const elapsed = ((Date.now() - callStartTime.getTime()) / 1000).toFixed(1);
                console.log(`🎤 [Asterisk] Paquet #${totalPacketsReceived} (${payload.length} bytes) - Total: ${totalBytesReceived} bytes - Élapsé: ${elapsed}s`);
            }

            // Le service Gemini gère lui-même le resampling 8 kHz -> 16 kHz.
            ensureGeminiSession().sendAudioChunk(payload);
        }
    });

    asteriskSocket.on('error', (err) => {
        console.error(`\n❌ [Asterisk] Erreur Socket: ${err.message}`);
        stopCall();
        if (geminiSession) geminiSession.close();
        releaseOutgoingCallContext(callUuid);
    });

    asteriskSocket.on('close', async () => {
        clearInterval(silenceKeepAlive);

        const [wavPath, mixedWavPath] = await Promise.all([recorder.end(), mixedRecorder.end()]);
        logCallSummary({ callStartTime, totalPacketsReceived, totalBytesReceived, wavPath, mixedWavPath });

        if (geminiSession) geminiSession.close();
        releaseOutgoingCallContext(callUuid);
    });
}
