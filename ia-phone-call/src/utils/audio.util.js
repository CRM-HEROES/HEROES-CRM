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
 * Additionne `buffer` dans `mixed` à partir de `offset`, échantillon par échantillon,
 * avec saturation (+32767 / -32768). Étend `mixed` si nécessaire. Ne touche que la
 * zone recouverte par `buffer` (coût O(longueur du chunk), pas O(longueur du mix).
 *
 * @param {Buffer} mixed - Mix cumulatif (mutation en place)
 * @param {Buffer} buffer - Chunk PCM 16 bits à superposer
 * @param {number} offset - Position (en octets) où placer le chunk
 * @returns {Buffer} Le mix (même instance ou étendu)
 */
function mixInto(mixed, buffer, offset) {
    const end = offset + buffer.length;

    if (end > mixed.length) {
        const grown = Buffer.alloc(end);
        mixed.copy(grown, 0);
        mixed = grown;
    }

    for (let i = 0; i < buffer.length; i += 2) {
        const sum = mixed.readInt16LE(offset + i) + buffer.readInt16LE(i);
        mixed.writeInt16LE(Math.max(-32768, Math.min(32767, sum)), offset + i);
    }

    return mixed;
}

// Au-delà de cette dérive entre la position attendue d'un flux et l'horloge murale,
// on réaligne le curseur du flux (silence inter-rafale Gemini, perte de paquets,
// livraison TCP en retard...). En dessous, on écrit de façon purement séquentielle
// pour ne pas créer de trous/liés par le jitter du socket.
// 160 ms = 2560 octets à 8 kHz 16 bits.
const MIX_RESYNC_THRESHOLD_BYTES = 2560;

/**
 * Enregistreur PCM « mixé » temps réel : superpose plusieurs flux PCM 16 bits
 * (ex: voix de l'appelant + voix de l'IA) dans un unique tampon, sur une timeline
 * commune ancrée à la création de l'enregistreur.
 *
 * Chaque flux possède son propre curseur séquentiel ; si son avance/retard vis-à-vis
 * de l'horloge murale dépasse MIX_RESYNC_THRESHOLD_BYTES, le curseur est réancré sur
 * l'horloge. Ainsi une rafale de chunks reste continue (pas de trous dus au jitter),
 * tandis qu'un long silence d'un flux laisse la place à l'autre voix au bon
 * endroit temporel.
 *
 * @param {Object} options
 * @param {string} options.filePath - Fichier PCM mixé de destination
 * @param {number} options.sampleRate - Fréquence d'échantillonnage du PCM mixé (8 kHz ici)
 * @returns {{ write(buf: Buffer, streamKey?: string): void, bytesWritten: number, end(): Promise<string|null> }}
 */
export function createMixedPcmRecorder({ filePath, sampleRate }) {
    const recorder = createPcmRecorder({ filePath, sampleRate });
    const startedAt = Date.now();
    const bytesPerMs = (sampleRate * BYTES_PER_SAMPLE) / 1000;
    let mixed = Buffer.alloc(0); // mix cumulatif (timeline commune)
    const cursors = new Map();   // position d'écriture (octets) de chaque flux

    /** Position temporelle « maintenant » en octets, alignée sur un échantillon pair. */
    function wallClockOffset() {
        const bytes = Math.floor((Date.now() - startedAt) * bytesPerMs);
        return bytes - (bytes % BYTES_PER_SAMPLE);
    }

    return {
        get bytesWritten() {
            return mixed.length;
        },
        /**
         * Superpose un chunk PCM 8 kHz dans le mix.
         *
         * @param {Buffer} buffer - Chunk PCM 16 bits little-endian
         * @param {string} [streamKey] - Identifiant du flux (ex: 'caller', 'gemini')
         */
        write(buffer, streamKey = 'default') {
            if (!buffer.length) return;

            const ideal = wallClockOffset();
            let cursor = cursors.get(streamKey);

            // Premier chunk du flux, ou dérive trop grande : réancrage sur l'horloge.
            if (cursor === undefined || Math.abs(ideal - cursor) > MIX_RESYNC_THRESHOLD_BYTES) {
                cursor = ideal;
            }

            cursors.set(streamKey, cursor + buffer.length);
            mixed = mixInto(mixed, buffer, cursor);
        },
        /** Ferme le flux et résout avec le chemin du WAV mixé (ou null). */
        end() {
            recorder.write(mixed);
            return recorder.end();
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
