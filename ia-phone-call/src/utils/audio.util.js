import fs from 'fs';
import path from 'path';

/** Dossier où sont déposés les enregistrements PCM/WAV des appels. */
export const RECORDINGS_DIR = path.resolve(process.cwd(), 'recordings');
fs.mkdirSync(RECORDINGS_DIR, { recursive: true });

const WAV_HEADER_SIZE = 44;
const BYTES_PER_SAMPLE = 2; // PCM 16 bits

/** Type de message AudioSocket pour un payload audio. */
const AUDIO_SOCKET_AUDIO_TYPE = 0x10;
/** Taille max d'un payload AudioSocket (40 ms de PCM 8 kHz 16 bits). */
const AUDIO_SOCKET_CHUNK_SIZE = 320;

/**
 * Encapsule un fichier PCM brut (mono 16 bits) dans un conteneur WAV lisible.
 *
 * @param {string} rawFilePath - Chemin du fichier PCM source (ex: recordings/call-*.pcm)
 * @param {number} sampleRate - Fréquence d'échantillonnage du PCM, en Hz
 * @returns {string|null} Chemin du WAV généré, ou null si aucun audio exploitable
 */
export function pcmToWavFile(rawFilePath, sampleRate) {
    if (!fs.existsSync(rawFilePath)) return null;

    const pcmBuffer = fs.readFileSync(rawFilePath);
    if (!pcmBuffer.length) return null;

    const wavFilePath = rawFilePath.replace(/\.pcm$/i, '.wav');
    const wavBuffer = Buffer.alloc(WAV_HEADER_SIZE + pcmBuffer.length);

    wavBuffer.write('RIFF', 0, 4, 'ascii');
    wavBuffer.writeUInt32LE(36 + pcmBuffer.length, 4);
    wavBuffer.write('WAVE', 8, 4, 'ascii');
    wavBuffer.write('fmt ', 12, 4, 'ascii');
    wavBuffer.writeUInt32LE(16, 16);                                      // taille du bloc fmt
    wavBuffer.writeUInt16LE(1, 20);                                       // PCM non compressé
    wavBuffer.writeUInt16LE(1, 22);                                       // mono
    wavBuffer.writeUInt32LE(sampleRate, 24);                              // échantillons/seconde
    wavBuffer.writeUInt32LE(sampleRate * BYTES_PER_SAMPLE, 28);           // octets/seconde
    wavBuffer.writeUInt16LE(BYTES_PER_SAMPLE, 32);                        // alignement des blocs
    wavBuffer.writeUInt16LE(16, 34);                                      // bits par échantillon
    wavBuffer.write('data', 36, 4, 'ascii');
    wavBuffer.writeUInt32LE(pcmBuffer.length, 40);
    pcmBuffer.copy(wavBuffer, WAV_HEADER_SIZE);

    fs.writeFileSync(wavFilePath, wavBuffer);
    return wavFilePath;
}

/**
 * Crée un enregistreur PCM vers fichier, converti en WAV à la fermeture.
 *
 * @param {Object} options
 * @param {string} options.filePath - Fichier PCM de destination
 * @param {number} options.sampleRate - Fréquence d'échantillonnage du PCM enregistré
 * @returns {{ write(buf: Buffer): void, bytesWritten: number, end(): Promise<string|null> }}
 */
export function createPcmRecorder({ filePath, sampleRate }) {
    const stream = fs.createWriteStream(filePath, { flags: 'w' });
    let bytesWritten = 0;
    let finalized = false;
    let resolveFinalize;
    const wavPathPromise = new Promise((resolve) => { resolveFinalize = resolve; });

    const finalize = () => {
        if (finalized) return;
        finalized = true;
        resolveFinalize(bytesWritten > 0 ? pcmToWavFile(filePath, sampleRate) : null);
    };

    stream.on('finish', finalize);
    stream.on('close', finalize);

    return {
        get bytesWritten() {
            return bytesWritten;
        },
        write(buffer) {
            bytesWritten += buffer.length;
            stream.write(buffer);
        },
        /** Ferme le flux et résout avec le chemin du WAV généré (ou null). */
        end() {
            if (!stream.writableEnded) stream.end();
            return wavPathPromise;
        },
    };
}

/**
 * Découpe et envoie un buffer PCM vers Asterisk au format AudioSocket.
 * Chaque paquet = en-tête de 3 octets (type + longueur Big-Endian) + payload.
 *
 * @param {import('net').Socket} socket - Socket du canal Asterisk
 * @param {Buffer} pcmBuffer - PCM 8 kHz 16 bits à transmettre
 */
export function writeAudioSocketPacket(socket, pcmBuffer) {
    if (socket.destroyed || !socket.writable) return;

    for (let offset = 0; offset < pcmBuffer.length; offset += AUDIO_SOCKET_CHUNK_SIZE) {
        const chunk = pcmBuffer.subarray(offset, offset + AUDIO_SOCKET_CHUNK_SIZE);

        const header = Buffer.alloc(3);
        header.writeUInt8(AUDIO_SOCKET_AUDIO_TYPE, 0);
        header.writeUInt16BE(chunk.length, 1);

        socket.write(Buffer.concat([header, chunk]));
    }
}
