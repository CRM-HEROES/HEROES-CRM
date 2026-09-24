import 'dotenv/config';
import net from 'net';
import http from 'http';
import url from 'url';
import { handleAsteriskConnection } from './services/asterisk.service.js';
import { placeOutgoingCall, listAsteriskChannels, hangupCall } from './services/outgoing-call.service.js';

const AUDIO_PORT = process.env.PORT || 9701;
const API_PORT = process.env.API_PORT || 3000;

// ─────────────────────────────────────────────────────────────────
// 1. Serveur TCP pour recevoir le flux audio brut d'Asterisk (AudioSocket)
// ─────────────────────────────────────────────────────────────────
const audioServer = net.createServer((socket) => {
    console.log('📞 Nouvel appel entrant depuis Asterisk/Kavkom');
    handleAsteriskConnection(socket);
});

audioServer.listen(AUDIO_PORT, () => {
    console.log(`\n${'═'.repeat(70)}`);
    console.log(`🚀 Passerelle IA Kavkom/Gemini DÉMARRÉE`);
    console.log(`   Port AudioSocket (TCP): ${AUDIO_PORT}`);
    console.log(`   Format: PCM 16-bit 8kHz`);
    console.log(`   Prête à recevoir des appels depuis Asterisk...`);
    console.log(`${'═'.repeat(70)}\n`);
});

// ─────────────────────────────────────────────────────────────────
// 2. Serveur HTTP pour l'API de contrôle (appels sortants, etc.)
// ─────────────────────────────────────────────────────────────────
const apiServer = http.createServer(async (req, res) => {
    const parsedUrl = url.parse(req.url, true);
    const pathname = parsedUrl.pathname;
    const query = parsedUrl.query;

    // En-têtes CORS
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Content-Type', 'application/json');

    // Répondre aux requêtes OPTIONS
    if (req.method === 'OPTIONS') {
        res.writeHead(200);
        res.end();
        return;
    }

    try {
        // 📞 POST /call - Placement d'appel sortant
        if (pathname === '/call' && req.method === 'POST') {
            let body = '';
            req.on('data', chunk => { body += chunk; });
            req.on('end', async () => {
                try {
                    const data = JSON.parse(body);
                    const phoneNumber = data.phoneNumber || data.number;
                    
                    if (!phoneNumber) {
                        res.writeHead(400);
                        res.end(JSON.stringify({ error: 'phoneNumber requis' }));
                        return;
                    }

                    const result = await placeOutgoingCall(phoneNumber);
                    res.writeHead(result.success ? 200 : 500);
                    res.end(JSON.stringify(result));
                } catch (err) {
                    res.writeHead(400);
                    res.end(JSON.stringify({ error: err.message }));
                }
            });
            return;
        }

        // 📊 GET /channels - Liste les canaux actifs
        if (pathname === '/channels' && req.method === 'GET') {
            const output = await listAsteriskChannels();
            res.writeHead(200);
            res.end(JSON.stringify({ channels: output }));
            return;
        }

        // 📋 GET /status - Statut du serveur
        if (pathname === '/status' && req.method === 'GET') {
            res.writeHead(200);
            res.end(JSON.stringify({
                status: 'ok',
                service: 'IA Kavkom Gateway',
                audioPort: AUDIO_PORT,
                apiPort: API_PORT,
                timestamp: new Date().toISOString()
            }));
            return;
        }

        // 🔴 POST /hangup - Raccrocher un appel
        if (pathname === '/hangup' && req.method === 'POST') {
            let body = '';
            req.on('data', chunk => { body += chunk; });
            req.on('end', async () => {
                try {
                    const data = JSON.parse(body);
                    const channelName = data.channelName || data.channel;
                    
                    if (!channelName) {
                        res.writeHead(400);
                        res.end(JSON.stringify({ error: 'channelName requis' }));
                        return;
                    }

                    const success = await hangupCall(channelName);
                    res.writeHead(success ? 200 : 500);
                    res.end(JSON.stringify({ success, channelName }));
                } catch (err) {
                    res.writeHead(400);
                    res.end(JSON.stringify({ error: err.message }));
                }
            });
            return;
        }

        // 📖 GET / - Documentation
        if (pathname === '/' && req.method === 'GET') {
            res.writeHead(200);
            res.end(JSON.stringify({
                name: 'IA Kavkom Gateway API',
                version: '1.0.0',
                endpoints: {
                    'GET /status': 'Statut du serveur',
                    'GET /channels': 'Liste des appels actifs',
                    'POST /call': 'Placer un appel sortant (body: {phoneNumber: "33..."})',
                    'POST /hangup': 'Raccrocher un appel (body: {channelName: "PJSIP/..."})'
                },
                audioSocket: {
                    port: AUDIO_PORT,
                    format: 'PCM 16-bit 8kHz'
                },
                examples: {
                    callOutgoing: 'curl -X POST http://localhost:3000/call -H "Content-Type: application/json" -d \'{"phoneNumber":"33612345678"}\'',
                    checkChannels: 'curl http://localhost:3000/channels',
                    checkStatus: 'curl http://localhost:3000/status'
                }
            }));
            return;
        }

        // 404
        res.writeHead(404);
        res.end(JSON.stringify({ error: 'Route not found' }));

    } catch (err) {
        console.error('❌ Erreur API:', err);
        res.writeHead(500);
        res.end(JSON.stringify({ error: err.message }));
    }
});

apiServer.listen(API_PORT, () => {
    console.log(`${'═'.repeat(70)}`);
    console.log(`🌐 API REST DÉMARRÉE`);
    console.log(`   Port: ${API_PORT}`);
    console.log(`   Documentation -> curl http://localhost:${API_PORT}/`);
    console.log(`${'═'.repeat(70)}\n`);
});

// Gestion propre de l'arrêt
process.on('SIGINT', () => {
    console.log('\n🛑 Arrêt du serveur...');
    audioServer.close(() => console.log('✅ Serveur AudioSocket arrêté'));
    apiServer.close(() => console.log('✅ Serveur API arrêté'));
    process.exit(0);
});