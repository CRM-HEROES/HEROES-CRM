/**
 * Client AudioSocket simple pour tester la connexion ia-phone-call
 * Envoie du bruit blanc en PCM 16-bit 8kHz
 * 
 * Usage: node audio-socket-client.js [host] [port] [duration_seconds]
 * Défaut: node audio-socket-client.js localhost 9701 5
 */

import net from 'net';

const host = process.argv[2] || 'localhost';
const port = parseInt(process.argv[3]) || 9701;
const duration = parseInt(process.argv[4]) || 5;

console.log(`🎙️  Client AudioSocket - Connexion à ${host}:${port}`);
console.log(`⏱️  Durée: ${duration}s, Format: PCM 16-bit 8kHz`);

const socket = net.createConnection({ host, port }, () => {
    console.log('✅ Connecté au serveur AudioSocket');
    
    // Générer du bruit blanc
    const SAMPLE_RATE = 8000;
    const CHUNK_SIZE = 320; // 40ms de audio à 8kHz
    const TOTAL_SAMPLES = SAMPLE_RATE * duration;
    let sampleCount = 0;

    const interval = setInterval(() => {
        if (sampleCount >= TOTAL_SAMPLES) {
            console.log('🔇 Fin de la transmission audio');
            socket.end();
            clearInterval(interval);
            return;
        }

        // Générer 320 échantillons de bruit blanc
        const audioBuffer = Buffer.alloc(CHUNK_SIZE * 2); // 16-bit = 2 bytes per sample
        for (let i = 0; i < CHUNK_SIZE; i++) {
            // Bruit blanc : valeur aléatoire entre -32768 et +32767
            const sample = Math.floor((Math.random() * 2 - 1) * 32767);
            audioBuffer.writeInt16LE(sample, i * 2);
        }

        // Format AudioSocket: [0x10] [length_hi] [length_lo] [payload]
        const header = Buffer.alloc(3);
        header.writeUInt8(0x10, 0);                    // Type: Audio
        header.writeUInt16BE(audioBuffer.length, 1);   // Longueur Big-Endian

        socket.write(Buffer.concat([header, audioBuffer]));

        sampleCount += CHUNK_SIZE;
        const elapsed = ((sampleCount / SAMPLE_RATE) * 100).toFixed(1);
        process.stdout.write(`\r⏳ Transmission: ${elapsed}%`);
    }, 40); // 40ms par chunk = 8000 samples/sec
});

socket.on('data', (chunk) => {
    console.log(`\n📨 Réceptionné ${chunk.length} bytes de Gemini`);
});

socket.on('error', (err) => {
    console.error(`❌ Erreur: ${err.message}`);
    process.exit(1);
});

socket.on('end', () => {
    console.log('\n🔌 Connexion fermée par le serveur');
    process.exit(0);
});

setTimeout(() => {
    console.error('\n❌ Timeout après 30s');
    socket.destroy();
    process.exit(1);
}, 30000);
