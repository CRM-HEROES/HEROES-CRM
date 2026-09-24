import { createGeminiSession } from './gemini.service.js';

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
    console.log(`${'='.repeat(70)}\n`);

    // Initialisation de la session Gemini
    const geminiSession = createGeminiSession({
        // Réception de l'audio produit par Gemini -> Réinjection dans Asterisk
        onAudioData: (pcmBuffer) => {
            console.log(`🎵 [Gemini] Envoi de ${pcmBuffer.length} bytes vers Asterisk`);
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
                
                // Log tous les 10 paquets pour ne pas spammer
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
        geminiSession.close();
    });

    asteriskSocket.on('close', () => {
        clearInterval(silenceKeepAlive);
        const elapsed = ((Date.now() - callStartTime.getTime()) / 1000).toFixed(2);
        console.log(`\n${'='.repeat(70)}`);
        console.log(`📊 [APPEL] Appel terminé`);
        console.log(`   Durée: ${elapsed}s`);
        console.log(`   Paquets reçus: ${totalPacketsReceived}`);
        console.log(`   Bytes total: ${totalBytesReceived}`);
        console.log(`   Débit moyen: ${(totalBytesReceived / parseFloat(elapsed) / 1024).toFixed(1)} KB/s`);
        console.log(`${'='.repeat(70)}\n`);
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