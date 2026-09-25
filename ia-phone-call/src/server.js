import 'dotenv/config';
import net from 'net';
import http from 'http';
import { handleAsteriskConnection } from './services/asterisk.service.js';
import { placeOutgoingCall, listAsteriskChannels, hangupCall } from './services/outgoing-call.service.js';
import { sendJson, readJsonBody, HttpError } from './utils/http.util.js';

const AUDIO_PORT = Number(process.env.PORT || 9701);
const API_PORT = Number(process.env.API_PORT || 3000);

const AUDIO_FORMAT = 'PCM 16-bit 8kHz';
const BANNER_WIDTH = 70;

function buildDocumentation() {
    return {
        name: 'IA Kavkom Gateway API',
        version: '1.0.0',
        endpoints: {
            'GET /status': 'Statut du serveur',
            'GET /channels': 'Liste des appels actifs',
            'POST /call': 'Placer un appel sortant (body: {phoneNumber: "33..."})',
            'POST /calls': 'Alias CRM pour placer un appel sortant (body: {destination_number: "33..."})',
            'POST /hangup': 'Raccrocher un appel (body: {channelName: "PJSIP/..."})'
        },
        audioSocket: {
            port: AUDIO_PORT,
            format: AUDIO_FORMAT
        },
        examples: {
            callOutgoing: `curl -X POST http://localhost:${API_PORT}/call -H "Content-Type: application/json" -d '{"phoneNumber":"33612345678"}'`,
            checkChannels: `curl http://localhost:${API_PORT}/channels`,
            checkStatus: `curl http://localhost:${API_PORT}/status`
        }
    };
}

// ─────────────────────────────────────────────────────────────────
// Routes de l'API REST (chaque handler renvoie { statusCode, payload })
// ─────────────────────────────────────────────────────────────────
const ROUTES = [
    {
        method: 'GET',
        path: '/',
        handler: async () => ({ statusCode: 200, payload: buildDocumentation() })
    },
    {
        method: 'GET',
        path: '/status',
        handler: async () => ({
            statusCode: 200,
            payload: {
                status: 'ok',
                service: 'IA Kavkom Gateway',
                audioPort: AUDIO_PORT,
                apiPort: API_PORT,
                timestamp: new Date().toISOString()
            }
        })
    },
    {
        method: 'GET',
        path: '/channels',
        handler: async () => ({
            statusCode: 200,
            payload: { channels: await listAsteriskChannels() }
        })
    },
    {
        method: 'POST',
        path: '/call',
        handler: async (req) => {
            const data = await readJsonBody(req);
            const phoneNumber = (data.phoneNumber || data.number || data.destinationNumber || data.destination_number || '').replace(/\D/g, '');
            const prospectId = data.prospectId ?? data.prospect_id ?? null;
            const projectId = data.projectId ?? data.project_id ?? null;
            const projectSlug = data.projectSlug ?? data.project_slug ?? null;
            const agentId = data.agentId ?? data.agent_id ?? null;
            const callerNumber = data.callerNumber ?? data.caller_number ?? null;
            const destinationNumber = data.destinationNumber ?? data.destination_number ?? phoneNumber ?? null;
            const openingPrompt = data.openingPrompt || data.prompt || data.script || data.instructions || null;

            if (!phoneNumber) throw new HttpError(400, 'phoneNumber requis');

            const result = await placeOutgoingCall(phoneNumber, undefined, undefined, openingPrompt, {
                prospectId,
                projectId,
                projectSlug,
                agentId,
                callerNumber,
                destinationNumber,
            });
            return { statusCode: result.success ? 200 : 500, payload: result };
        }
    },
    {
        method: 'POST',
        path: '/calls',
        handler: async (req) => findRoute('POST', '/call').handler(req)
    },
    {
        method: 'POST',
        path: '/hangup',
        handler: async (req) => {
            const data = await readJsonBody(req);
            const channelName = data.channelName || data.channel;

            if (!channelName) throw new HttpError(400, 'channelName requis');

            const success = await hangupCall(channelName);
            return { statusCode: success ? 200 : 500, payload: { success, channelName } };
        }
    }
];

function findRoute(method, pathname) {
    return ROUTES.find(route => route.method === method && route.path === pathname);
}

function setCorsHeaders(res) {
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
    res.setHeader('Content-Type', 'application/json');
}

async function handleApiRequest(req, res) {
    setCorsHeaders(res);

    if (req.method === 'OPTIONS') {
        res.writeHead(204);
        res.end();
        return;
    }

    const { pathname } = new URL(req.url, `http://${req.headers.host || 'localhost'}`);
    const route = findRoute(req.method, pathname);

    if (!route) {
        sendJson(res, 404, { error: 'Route not found' });
        return;
    }

    try {
        const { statusCode, payload } = await route.handler(req);
        sendJson(res, statusCode, payload);
    } catch (err) {
        const statusCode = err.statusCode || 500;
        if (statusCode >= 500) console.error('❌ Erreur API:', err);
        sendJson(res, statusCode, { error: err.message });
    }
}

// ─────────────────────────────────────────────────────────────────
// 1. Serveur TCP pour recevoir le flux audio brut d'Asterisk (AudioSocket)
// ─────────────────────────────────────────────────────────────────
const audioServer = net.createServer((socket) => {
    try {
        console.log('📞 Nouvel appel entrant depuis Asterisk/Kavkom');
        handleAsteriskConnection(socket);
    } catch (err) {
        console.error('❌ Impossible de traiter la connexion AudioSocket:', err);
        socket.destroy();
    }
});

// ─────────────────────────────────────────────────────────────────
// 2. Serveur HTTP pour l'API de contrôle (appels sortants, etc.)
// ─────────────────────────────────────────────────────────────────
const apiServer = http.createServer(handleApiRequest);

audioServer.listen(AUDIO_PORT, () => {
    console.log(`\n${'═'.repeat(BANNER_WIDTH)}`);
    console.log('🚀 Passerelle IA Kavkom/Gemini DÉMARRÉE');
    console.log(`   Port AudioSocket (TCP): ${AUDIO_PORT}`);
    console.log(`   Format: ${AUDIO_FORMAT}`);
    console.log('   Prête à recevoir des appels depuis Asterisk...');
    console.log(`${'═'.repeat(BANNER_WIDTH)}\n`);
});

apiServer.listen(API_PORT, () => {
    console.log(`${'═'.repeat(BANNER_WIDTH)}`);
    console.log('🌐 API REST DÉMARRÉE');
    console.log(`   Port: ${API_PORT}`);
    console.log(`   Documentation -> curl http://localhost:${API_PORT}/`);
    console.log(`${'═'.repeat(BANNER_WIDTH)}\n`);
});

// ─────────────────────────────────────────────────────────────────
// Arrêt propre
// ─────────────────────────────────────────────────────────────────
const managedServers = [
    { server: audioServer, label: 'AudioSocket' },
    { server: apiServer, label: 'API' }
];
let shuttingDown = false;

function shutdown(signal) {
    if (shuttingDown) return;
    shuttingDown = true;
    console.log(`\n🛑 Arrêt du serveur (${signal})...`);

    // Un appel en cours peut maintenir une connexion ouverte : on force la sortie.
    const forceExit = setTimeout(() => process.exit(0), 5000);
    forceExit.unref();

    Promise.all(managedServers.map(({ server, label }) => new Promise((resolve) => {
        server.close(() => {
            console.log(`✅ Serveur ${label} arrêté`);
            resolve();
        });
    }))).then(() => process.exit(0));
}

process.on('SIGINT', () => shutdown('SIGINT'));
process.on('SIGTERM', () => shutdown('SIGTERM'));
