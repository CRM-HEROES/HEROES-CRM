import WebSocket from 'ws';
import fs from 'fs';
import path from 'path';
import 'dotenv/config';

// Usage: node debug-short.js <passes> [raw] — raw = pas d'upsampling (8k étiqueté 16k, comme le TEST F historique)
const passes = Number(process.argv[2] || 1);
const raw = process.argv[3] === 'raw';
const model = process.env.GEMINI_LIVE_MODEL || 'models/gemini-2.5-flash-native-audio-preview-09-2025';
console.log(`=== TEST audio, ${passes} passe(s), mode=${raw ? 'RAW 8k étiqueté 16k' : 'upsamplé 16k'}`);

const apiKey = process.env.GEMINI_API_KEY;
const url = `wss://generativelanguage.googleapis.com/ws/google.ai.generativelanguage.v1alpha.GenerativeService.BidiGenerateContent?key=${apiKey}`;

const ws = new WebSocket(url);
let receivedAudio = 0;
let msgCount = 0;

function sendPcm16k(pcm8k) {
    if (raw) {
        ws.send(JSON.stringify({
            realtimeInput: { mediaChunks: [{ mimeType: 'audio/pcm;rate=16000', data: pcm8k.toString('base64') }] }
        }));
        return;
    }
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

function streamAudio() {
    const single = fs.readFileSync(path.join(process.cwd(), 'input.pcm'));
    // Boucles séquentielles : on concatène avant de streamer (rythme réel préservé)
    const buf = Buffer.concat(Array.from({ length: passes }, () => single));
    let offset = 0;
    const timer = setInterval(() => {
        if (offset >= buf.length) {
            clearInterval(timer);
            console.log(`=== Audio envoyé (${passes} passe(s), ${(buf.length / 2 / 8000).toFixed(2)}s). Attente réponse...`);
            setTimeout(() => {
                console.log(`=== FIN. Audio reçu: ${receivedAudio} octets`);
                ws.close();
                process.exit(receivedAudio > 0 ? 0 : 1);
            }, 15000);
            return;
        }
        sendPcm16k(buf.subarray(offset, offset + 320));
        offset += 320;
    }, 20);
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
    if (msg.setupComplete) {
        streamAudio();
        return;
    }
    if (msg.serverContent?.turnComplete || msg.serverContent?.interrupted) {
        console.log(`<<< MSG#${msgCount} événement:`, JSON.stringify(msg.serverContent).slice(0, 120));
    }
    if (msg.serverContent?.turnComplete && receivedAudio > 0) {
        console.log(`=== SUCCÈS: réponse audio reçue (${receivedAudio} octets) après ${passes} passe(s)`);
        ws.close();
        process.exit(0);
    }
});

ws.on('error', (err) => console.log('!!! WS ERROR:', err.message));
ws.on('close', (code, reason) => console.log(`=== WS fermé (${code}: ${reason})`));
