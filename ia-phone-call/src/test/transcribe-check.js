import 'dotenv/config';
import fs from 'fs';

// Transcrit input.pcm via l'API Gemini standard pour savoir ce qu'il contient réellement
const apiKey = process.env.GEMINI_API_KEY;
const pcm = fs.readFileSync('input.pcm');

async function transcribe(rate) {
    const body = {
        contents: [{
            parts: [
                { text: 'Transcris fidèlement ce fichier audio. S\'il ne contient pas de parole, décris ce que tu entends (musique, silence, bruit, bips...). Réponds en une ligne.' },
                { inlineData: { mimeType: `audio/pcm;rate=${rate}`, data: pcm.toString('base64') } }
            ]
        }]
    };
    const res = await fetch(`https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent?key=${apiKey}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
    });
    const json = await res.json();
    if (json.error) return `ERREUR: ${json.error.message}`;
    return json.candidates?.[0]?.content?.parts?.map(p => p.text).join(' ') || '(réponse vide)';
}

console.log('--- Interprété comme 8000 Hz ---');
console.log(await transcribe(8000));
console.log('--- Interprété comme 16000 Hz ---');
console.log(await transcribe(16000));
