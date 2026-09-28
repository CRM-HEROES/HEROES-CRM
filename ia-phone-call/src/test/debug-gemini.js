import WebSocket from 'ws';
import fs from 'fs';
import path from 'path';
import 'dotenv/config';

// Modes:
//  - concurrent : salutation clientContent + audio envoyé en même temps (comme le serveur actuel)
//  - afterTurn  : salutation clientContent, on attend turnComplete, PUIS on envoie l'audio
//  - audio      : audio seul (comportement historique)
const mode = process.argv[2] || 'concurrent';
const model = process.env.GEMINI_LIVE_MODEL || 'models/gemini-2.5-flash-native-audio-preview-09-2025';

console.log(`=== TEST mode=${mode} model=${model}`);

const apiKey = process.env.GEMINI_API_KEY;
const url = `wss://generativelanguage.googleapis.com/ws/google.ai.generativelanguage.v1alpha.GenerativeService.BidiGenerateContent?key=${apiKey}`;

const ws = new WebSocket(url);
let receivedAudio = 0;
let msgCount = 0;
let audioSent = false;

function sendPcm16k(pcm8k) {
    // Upsample 8k -> 16k (duplication)
    const out = Buffer.alloc(pcm8k.length * 2);
    for (let i = 0; i < pcm8k.length; i += 2) {
        const s = pcm8k.readInt16LE(i);
        out.writeInt16LE(s, i * 2);
        out.writeInt16LE(s, i * 2 + 2);
    }
    ws.send(JSON.stringify({
        realtimeInput: { mediaChunks: [{ mimeType: 'audio/pcm;rate=16000', data: out.toString('base64') }] }
    }));
}

function streamAudio(onDone) {
    const buf = fs.readFileSync(path.join(process.cwd(), 'input.pcm'));
    let offset = 0;
    const timer = setInterval(() => {
        if (offset >= buf.length) {
            clearInterval(timer);
            console.log('=== Audio (0.62s) envoyé.');
            audioSent = true;
            if (onDone) onDone();
            return;
        }
        sendPcm16k(buf.subarray(offset, offset + 320));
        offset += 320;
    }, 20);
}

function finish() {
    console.log(`=== FIN. Audio reçu: ${receivedAudio} octets, messages: ${msgCount}, audioEnvoyé=${audioSent}`);
    ws.close();
    process.exit(receivedAudio > 0 ? 0 : 1);
}

ws.on('open', () => {
    ws.send(JSON.stringify({
        setup: {
            model,
            generationConfig: { responseModalities: ['AUDIO'] },
            systemInstruction: { parts: [{ text: 'Tu es un assistant téléphonique professionnel et courtois. Réponds de manière très concise.' }] }
        }
    }));
});

ws.on('message', (raw) => {
    const msg = JSON.parse(raw);
    msgCount++;
    const parts = msg.serverContent?.modelTurn?.parts || [];
    for (const p of parts) {
        if (p.inlineData?.mimeType?.startsWith('audio/')) {
            receivedAudio += Buffer.from(p.inlineData.data, 'base64').length;
        }
    }
    const label = msg.serverContent?.turnComplete ? ' (turnComplete)'
        : msg.serverContent?.generationComplete ? ' (generationComplete)'
        : msg.serverContent?.interrupted ? ' (INTERRUPTED)'
        : '';
    console.log(`<<< MSG#${msgCount}${label}:`, JSON.stringify(msg).slice(0, 150));

    if (msg.setupComplete) {
        if (mode === 'audio') {
            streamAudio(() => setTimeout(finish, 12000));
        } else {
            // Salutation : l'IA parle en premier
            ws.send(JSON.stringify({
                clientContent: {
                    turns: [{ role: 'user', parts: [{ text: 'L\'appel vient de démarrer. Tu dois parler en premier : salue l\'appelant.' }] }],
                    turnComplete: true
                }
            }));
            if (mode === 'concurrent') {
                streamAudio(() => setTimeout(finish, 15000));
            } else if (mode === 'afterTurn') {
                // On attend le turnComplete de la salutation avant d'envoyer l'audio
            }
        }
        return;
    }

    if (mode === 'afterTurn' && msg.serverContent?.turnComplete && !audioSent) {
        console.log('=== Salutation terminée (turnComplete). Envoi de l\'audio utilisateur maintenant...');
        streamAudio(() => setTimeout(finish, 15000));
    }
});

ws.on('error', (err) => console.log('!!! WS ERROR:', err.message));
ws.on('close', (code, reason) => console.log(`=== WS fermé (${code}: ${reason})`));
