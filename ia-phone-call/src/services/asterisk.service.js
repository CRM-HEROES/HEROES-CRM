import fs from 'fs';
import path from 'path';
import { createGeminiSession } from './gemini.service.js';

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
    wavBuffer.writeUInt32LE(8000, 24);
    wavBuffer.writeUInt32LE(8000 * 2, 28);
    wavBuffer.writeUInt16LE(2, 32);
    wavBuffer.writeUInt16LE(16, 34);
    wavBuffer.write('data', 36, 4, 'ascii');
    wavBuffer.writeUInt32LE(pcmBuffer.length, 40);
    pcmBuffer.copy(wavBuffer, 44);

    fs.writeFileSync(wavFilePath, wavBuffer);
    return wavFilePath;
}

/**
 * Traite une connexion entrante TCP venant du module AudioSocket d'Asterisk.
 * Protocole AudioSocket (3 octets d'en-tête) :
 * - Byte 0 : Type de message (0x10 = Audio)
 * - Bytes 1-2 : Longueur du payload (Big-Endian 16-bit)
 * - Payload : Données audio PCM brutes
 * 
 * @param {import('net').Socket} asteriskSocket 
 */
export function handleAsteriskConnection(asteriskSocket) {
    let audioBuffer = Buffer.alloc(0);
    let totalBytesReceived = 0;
    let totalPacketsReceived = 0;
    const callStartTime = new Date();
    const recordingTimestamp = callStartTime.toISOString().replace(/[:.]/g, '-');
    const rawRecordingPath = path.join(RECORDINGS_DIR, `call-${recordingTimestamp}.pcm`);
    const recordingStream = fs.createWriteStream(rawRecordingPath, { flags: 'w' });

    // app_audiosocket.c interrompt la connexion si AUCUNE activité (frame venant
    // du canal OU du socket) n'a lieu pendant MAX_WAIT_TIMEOUT_MSEC (2000 ms).
    // Un correspondant silencieux (silence suppression mobile) et le temps de
    // démarrage de Gemini suffisent donc à faire échouer l'appel : on émet du
    // silence tant que Gemini n'a rien envoyé.
    const SILENCE_FRAME = Buffer.alloc(320); // 40 ms de PCM 8 kHz 16 bits
    let lastAudioSentAt = Date.now();

    const silenceKeepAlive = setInterval(() => {
        // Gemini est en train de parler : ne rien intercaler dans le flux.
        if (Date.now() - lastAudioSentAt < 1000) return;

        writeAudioToAsterisk(asteriskSocket, SILENCE_FRAME);
        lastAudioSentAt = Date.now();
    }, 500);
    
    console.log(`\n${'='.repeat(70)}`);
    console.log(`📞 [APPEL] Nouvel appel entrant depuis Asterisk (AudioSocket)`);
    console.log(`⏰ Début: ${callStartTime.toLocaleTimeString('fr-FR')}`);
    console.log(`📍 Distance: Asterisk → ia-phone-call → Gemini`);
    console.log(`💾 Enregistrement: ${rawRecordingPath}`);
    console.log(`${'='.repeat(70)}\n`);

    // Initialisation de la session Gemini
    const geminiSession = createGeminiSession({
        // Réception de l'audio produit par Gemini -> Réinjection dans Asterisk
        onAudioData: (pcmBuffer) => {
            console.log(`🎵 [Gemini -> Asterisk] Réception de ${pcmBuffer.length} bytes depuis Gemini`);
            console.log(`📤 [Asterisk -> Kavkom] Envoi vers le trunk de ${pcmBuffer.length} bytes audio`);
            lastAudioSentAt = Date.now();
            writeAudioToAsterisk(asteriskSocket, pcmBuffer);
        },
        onClose: () => {
            console.log('[Gemini] ⚠️  Fermeture de Gemini - Fermeture du socket Asterisk');
            clearInterval(silenceKeepAlive);
            asteriskSocket.end();
        }
    });

    // Traitement du flux binaire entrant d'Asterisk
    asteriskSocket.on('data', (chunk) => {
        audioBuffer = Buffer.concat([audioBuffer, chunk]);

        // Parsing des paquets AudioSocket
        while (audioBuffer.length >= 3) {
            const messageType = audioBuffer.readUInt8(0);
            const payloadLength = audioBuffer.readUInt16BE(1);
            const totalPacketLength = 3 + payloadLength;

            // Attendre que le paquet complet soit arrivé dans le buffer
            if (audioBuffer.length < totalPacketLength) {
                break;
            }

            // Extraire le paquet courant du buffer
            const packetPayload = audioBuffer.subarray(3, totalPacketLength);
            audioBuffer = audioBuffer.subarray(totalPacketLength);

            // Si c'un paquet de type AUDIO (0x10), transmettre à Gemini
            // (le service gère lui-même le resampling 8 kHz -> 16 kHz attendu par Gemini)
            if (messageType === 0x10) {
                totalBytesReceived += payloadLength;
                totalPacketsReceived++;
                recordingStream.write(packetPayload);

                if (totalPacketsReceived % 10 === 0) {
                    const elapsed = ((Date.now() - callStartTime.getTime()) / 1000).toFixed(1);
                    console.log(`🎤 [Asterisk] Paquet #${totalPacketsReceived} (${payloadLength} bytes) - Total: ${totalBytesReceived} bytes - Élapsé: ${elapsed}s`);
                }

                geminiSession.sendAudioChunk(packetPayload);
            }
        }
    });

    asteriskSocket.on('error', (err) => {
        console.error(`\n❌ [Asterisk] Erreur Socket: ${err.message}`);
        clearInterval(silenceKeepAlive);
        recordingStream.end();
        geminiSession.close();
    });

    asteriskSocket.on('close', () => {
        clearInterval(silenceKeepAlive);
        recordingStream.end(() => {
            const wavPath = pcmToWavFile(rawRecordingPath);
            const elapsed = ((Date.now() - callStartTime.getTime()) / 1000).toFixed(2);
            console.log(`\n${'='.repeat(70)}`);
            console.log(`📊 [APPEL] Appel terminé`);
            console.log(`   Durée: ${elapsed}s`);
            console.log(`   Paquets reçus: ${totalPacketsReceived}`);
            console.log(`   Bytes total: ${totalBytesReceived}`);
            console.log(`   Débit moyen: ${(totalBytesReceived / parseFloat(elapsed) / 1024).toFixed(1)} KB/s`);
            console.log(`   Fichier WAV: ${wavPath || 'non généré'}`);
            console.log(`${'='.repeat(70)}\n`);
        });
        geminiSession.close();
    });
}

/**
 * Formate et envoie un buffer PCM brut vers Asterisk au format AudioSocket.
 * 
 * @param {import('net').Socket} socket 
 * @param {Buffer} pcmBuffer 
 */
function writeAudioToAsterisk(socket, pcmBuffer) {
    if (socket.destroyed || !socket.writable) return;

    // Découpage par paquets max de 320 octets (format standard AudioSocket pour éviter la saturation)
    const CHUNK_SIZE = 320; 
    for (let offset = 0; offset < pcmBuffer.length; offset += CHUNK_SIZE) {
        const chunk = pcmBuffer.subarray(offset, offset + CHUNK_SIZE);
        
        const header = Buffer.alloc(3);
        header.writeUInt8(0x10, 0);               // Type 0x10: Payload Audio
        header.writeUInt16BE(chunk.length, 1);    // Longueur du chunk sur 2 octets

        socket.write(Buffer.concat([header, chunk]));
    }
}