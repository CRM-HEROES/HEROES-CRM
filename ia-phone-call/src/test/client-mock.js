import net from 'net';
import fs from 'fs';
import path from 'path';

const PCM_FILE = path.join(process.cwd(), 'input.pcm');
const OUTPUT_FILE = path.join(process.cwd(), 'gemini-response.pcm');
const PORT = 9701;

// Vérification du fichier d'entrée
if (!fs.existsSync(PCM_FILE)) {
    console.error(`❌ Fichier d'entrée introuvable : ${PCM_FILE}`);
    console.log("👉 Créez un fichier input.pcm de 3-5 secondes avant de lancer le test.");
    process.exit(1);
}

const outputStream = fs.createWriteStream(OUTPUT_FILE);
let initialGreetingReceived = false;

const client = net.connect({ port: PORT }, () => {
    console.log('✅ Connecté à la passerelle.');
    console.log('⏳ En attente de la salutation initiale de Gemini...');
});

// 1. Réception de l'audio renvoyé par Gemini (Accueil + Réponses)
client.on('data', (data) => {
    if (data.length > 3 && data.readUInt8(0) === 0x10) {
        const pcmPayload = data.subarray(3);
        outputStream.write(pcmPayload);

        if (!initialGreetingReceived) {
            initialGreetingReceived = true;
            console.log('🔊 [Gemini a pris la parole en premier !] Enregistrement du flux d\'accueil...');

            // Une fois que l'IA a commencé à saluer, on simule la réponse de l'utilisateur après un court délai
            setTimeout(() => {
                sendUserAudio();
            }, 1500);
        } else {
            console.log(`🔊 [Réponse audio de Gemini] +${pcmPayload.length} octets reçus`);
        }
    }
});

// 2. Envoi cadencé de l'audio utilisateur (simule la voix humaine)
function sendUserAudio() {
    console.log('🎙️ Envoi de la voix utilisateur (input.pcm) en temps réel...');

    const fileBuffer = fs.readFileSync(PCM_FILE);
    const CHUNK_SIZE = 320; // 20ms d'audio PCM 8kHz mono
    let offset = 0;

    const timer = setInterval(() => {
        if (offset >= fileBuffer.length) {
            clearInterval(timer);
            console.log('📤 Voix utilisateur entièrement envoyée. Attente de la suite de la conversation...');
            return;
        }

        const chunk = fileBuffer.subarray(offset, offset + CHUNK_SIZE);
        offset += CHUNK_SIZE;

        // Entête AudioSocket : Type 0x10 + Taille Big-Endian sur 2 octets
        const header = Buffer.alloc(3);
        header.writeUInt8(0x10, 0);
        header.writeUInt16BE(chunk.length, 1);

        client.write(Buffer.concat([header, chunk]));
    }, 20); // 20ms
}

client.on('close', () => {
    console.log('🔌 Connexion fermée.');
    outputStream.end();
    console.log(`💾 Réponse audio sauvegardée sous : ${OUTPUT_FILE}`);
});

client.on('error', (err) => {
    console.error('❌ Erreur Client Socket:', err.message);
});