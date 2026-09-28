import net from 'net';
import crypto from 'crypto';
import fs from 'fs/promises';
import path from 'path';

// ─────────────────────────────────────────────────────────────────────────────
// Paramètres AMI (surchargeables via .env)
// ─────────────────────────────────────────────────────────────────────────────
const AMI_HOST = process.env.ASTERISK_AMI_HOST || 'ia-asterisk';
const AMI_PORT = parseInt(process.env.ASTERISK_AMI_PORT || '5038', 10);
const AMI_USERNAME = process.env.ASTERISK_AMI_USERNAME || 'default';
// Doit correspondre à `secret` de [default] dans asterisk/manager.conf
const AMI_SECRET = process.env.ASTERISK_AMI_SECRET || 'HeroesAmi2026!';
const AMI_TIMEOUT_MS = parseInt(process.env.ASTERISK_AMI_TIMEOUT_MS || '10000', 10);
const OUTGOING_DIAL_TIMEOUT_MS = parseInt(process.env.OUTGOING_DIAL_TIMEOUT_MS || '45000', 10);
const ORIGINATE_IMMEDIATE_FAILURE_GRACE_MS = parseInt(process.env.ASTERISK_ORIGINATE_IMMEDIATE_FAILURE_GRACE_MS || '3000', 10);
const PJSIP_ENDPOINT_READY_TIMEOUT_MS = parseInt(process.env.ASTERISK_PJSIP_ENDPOINT_READY_TIMEOUT_MS || '15000', 10);

// Asterisk 21+ n'embarque plus chan_sip : le trunk s'appelle "PJSIP" et non "SIP"
// ("SIP/..." échoue avec "Unable to create channel of type 'SIP'").
// L'AOR de Kavkom ne définit que le domaine (contact=sip:aria-madacom.kavkom.com:5061),
// le dial string correct est donc "PJSIP/<numero>@<endpoint>" ; la forme
// "PJSIP/kavkom-trunk/<numero>" échoue avec
// "Could not create dialog to invalid URI '<numero>'" (cf. Asterisk 22 / chan_pjsip).
const AMI_TRUNK_ENDPOINT = process.env.ASTERISK_TRUNK_ENDPOINT || 'kavkom-trunk';
const DYNAMIC_PJSIP_CONFIG_FILE = process.env.ASTERISK_DYNAMIC_CONFIG_FILE || '/data/asterisk/ai-agents.conf';

const AMI_LINE_END = '\r\n';
const AMI_BLOCK_END = '\r\n\r\n';
const AMI_GREETING_MARKER = 'Asterisk Call Manager';
const CHANNELS_END_MARKER = 'CoreShowChannelsComplete';
const CHANNELS_TIMEOUT_MS = 4000;

const CALL_CONTEXT = 'outgoing-call';
const CALL_EXTEN = 's';
const MANAGED_BLOCK_PREFIX = '; BEGIN HEROES AI AGENT';
const MANAGED_BLOCK_SUFFIX = '; END HEROES AI AGENT';
let pjsipConfigQueue = Promise.resolve();

/**
 * Assemble les lignes d'une action AMI (terminées par \r\n, bloc clos par \r\n\r\n).
 */
function formatAmiAction(lines) {
    return lines.map(line => line + AMI_LINE_END).join('') + AMI_LINE_END;
}

function buildLoginAction() {
    return formatAmiAction([
        'Action: Login',
        `Username: ${AMI_USERNAME}`,
        `Secret: ${AMI_SECRET}`,
        'ActionID: login'
    ]);
}

function buildOriginateAction({ channel, callUuid, callId, callerIdNumber }) {
    const lines = [
        'Action: Originate',
        `Channel: ${channel}`,
        `Context: ${CALL_CONTEXT}`,
        `Exten: ${CALL_EXTEN}`,
        'Priority: 1',
        `Variable: CALLID=${callUuid}`,
    ];

    if (callerIdNumber) {
        lines.push(`CallerID: ${callerIdNumber}`);
    }

    lines.push(
        `Timeout: ${OUTGOING_DIAL_TIMEOUT_MS}`,
        'Async: true',
        `ActionID: ${callId}`
    );

    return formatAmiAction(lines);
}

function buildHangupAction(channelName) {
    return formatAmiAction([
        'Action: Hangup',
        `Channel: ${channelName}`,
        `ActionID: hangup-${Date.now()}`
    ]);
}

function buildCommandAction(command) {
    return formatAmiAction([
        'Action: Command',
        `Command: ${command}`,
        `ActionID: command-${Date.now()}`
    ]);
}

/**
 * Convertit un bloc de réponse AMI ("Header: valeur\r\n...") en objet.
 *
 * @param {string} block
 * @returns {Record<string, string>}
 */
function parseAmiBlock(block) {
    const fields = {};

    for (const line of block.split(AMI_LINE_END)) {
        const separator = line.indexOf(':');
        if (separator > 0) {
            fields[line.slice(0, separator).trim()] = line.slice(separator + 1).trim();
        }
    }

    return fields;
}

/**
 * Ouvre une session AMI : greeting -> Login -> action, et résout dès que la
 * réponse complète est reçue.
 *
 * @param {string} action - Action AMI déjà formatée
 * @param {Object} [options]
 * @param {string} [options.host]
 * @param {number} [options.port]
 * @param {number} [options.timeout]
 * @param {string} [options.until] - Chaîne qui marque la fin des réponses
 *                                   multi-events (ex: CoreShowChannelsComplete)
 * @returns {Promise<string>} Réponse brute de l'action
 */
function amiAction(action, { host = AMI_HOST, port = AMI_PORT, timeout = AMI_TIMEOUT_MS, until = null } = {}) {
    return new Promise((resolve, reject) => {
        const socket = new net.Socket();
        // `buffer` = données non encore consommées, `response` = réponse cumulée de l'action
        let buffer = '';
        let response = '';
        let phase = 'greeting'; // greeting -> login -> action
        let settled = false;

        const finish = (error, result) => {
            if (settled) return;
            settled = true;
            clearTimeout(guard);
            socket.destroy();
            if (error) reject(error);
            else resolve(result);
        };

        /** Consomme le premier bloc complet du buffer. */
        const takeBlock = () => {
            const boundary = buffer.indexOf(AMI_BLOCK_END);
            const block = buffer.slice(0, boundary);
            buffer = buffer.slice(boundary + AMI_BLOCK_END.length);
            return block;
        };

        const guard = setTimeout(() => {
            if (until && response.includes(until)) return finish(null, response);
            finish(new Error(`Timeout AMI après ${timeout} ms`));
        }, timeout);

        const onGreeting = () => {
            // Le greeting tient sur UNE SEULE ligne ("Asterisk Call Manager/22.0.0\r\n"),
            // il ne faut donc surtout pas attendre un \r\n\r\n ici.
            if (!buffer.includes(AMI_GREETING_MARKER)) return false;

            console.log('✅ Identifié par Asterisk Manager');
            buffer = '';
            phase = 'login';
            socket.write(buildLoginAction());
            return true;
        };

        const onLogin = () => {
            if (!buffer.includes(AMI_BLOCK_END)) return false;

            const block = takeBlock();
            response = '';

            if (!block.includes('Response: Success')) {
                finish(new Error(`Authentification AMI refusée (${block.replace(/\r\n/g, ' | ')})`));
                return true;
            }

            console.log('✅ Authentifié auprès d\'Asterisk (AMI)');
            phase = 'action';
            socket.write(action);
            return true;
        };

        const onActionResponse = (chunk) => {
            response += chunk;

            if (until) {
                if (response.includes(until)) finish(null, response);
                return;
            }

            while (buffer.includes(AMI_BLOCK_END)) {
                const block = takeBlock();
                const fields = parseAmiBlock(block);

                if (fields.Event && !fields.Response) {
                    continue;
                }

                finish(null, block);
                return;
            }
        };

        socket.connect(port, host, () => {
            console.log(`✅ Connecté à Asterisk AMI sur ${host}:${port}`);
        });

        socket.on('error', (err) => {
            console.error(`❌ Erreur de connexion AMI: ${err.message}`);
            finish(err);
        });

        socket.on('data', (data) => {
            const chunk = data.toString();
            buffer += chunk;

            if (phase === 'greeting' && onGreeting()) return;
            if (phase === 'login' && onLogin()) return;
            if (phase === 'action') onActionResponse(chunk);
        });
    });
}

function describeOriginateResponse(fields) {
    const response = fields.Response || 'Unknown';
    const reasonLabels = {
        3: 'ringing/no-answer-before-timeout',
        4: 'answered',
        5: 'busy',
        8: 'congestion'
    };
    const reasonValue = fields.Reason ? Number(fields.Reason) : null;
    const reasonLabel = reasonLabels[reasonValue] ? ` (${reasonLabels[reasonValue]})` : '';
    const reason = fields.Reason ? ` reason=${fields.Reason}${reasonLabel}` : '';
    const channel = fields.Channel ? ` channel=${fields.Channel}` : '';
    const message = fields.Message ? ` message="${fields.Message}"` : '';
    return `${response}${reason}${channel}${message}`;
}

function trackOriginate(action, actionId, { host = AMI_HOST, port = AMI_PORT, timeout = AMI_TIMEOUT_MS } = {}) {
    return new Promise((resolve, reject) => {
        const socket = new net.Socket();
        let buffer = '';
        let phase = 'greeting';
        let settled = false;
        let trackingTimer = null;
        let queuedBlock = null;

        const cleanup = () => {
            clearTimeout(guard);
            clearTimeout(trackingTimer);
        };

        const closeTracking = (block = queuedBlock) => {
            if (settled) return;
            settled = true;
            cleanup();
            socket.destroy();
            resolve(block);
        };

        const resolveQueued = (block) => {
            if (settled) return;
            queuedBlock = block;
            clearTimeout(guard);

            trackingTimer = setTimeout(() => {
                closeTracking(block);
            }, ORIGINATE_IMMEDIATE_FAILURE_GRACE_MS);
            trackingTimer.unref?.();
        };

        const fail = (error) => {
            if (settled) {
                console.error(`❌ Erreur AMI pendant le suivi Originate: ${error.message}`);
                socket.destroy();
                return;
            }

            settled = true;
            cleanup();
            socket.destroy();
            reject(error);
        };

        const takeBlock = () => {
            const boundary = buffer.indexOf(AMI_BLOCK_END);
            const block = buffer.slice(0, boundary);
            buffer = buffer.slice(boundary + AMI_BLOCK_END.length);
            return block;
        };

        const handleEvent = (fields) => {
            switch (fields.Event) {
                case 'DialBegin':
                    if (fields.DestChannel || fields.Channel) {
                        console.log(`📡 DialBegin: ${fields.Channel || '?'} -> ${fields.DestChannel || '?'}`);
                    }
                    break;
                case 'DialEnd':
                    console.log(`📡 DialEnd: status=${fields.DialStatus || '?'} cause=${fields.Cause || '?'}`);
                    break;
                case 'Hangup':
                    if (fields.Channel?.includes('PJSIP/')) {
                        console.log(`📴 Hangup: ${fields.Channel} cause=${fields.Cause || '?'} ${fields['Cause-txt'] || ''}`.trim());
                    }
                    break;
                case 'OriginateResponse':
                    if (!fields.ActionID || fields.ActionID === actionId) {
                        console.log(`📞 Résultat AMI Originate: ${describeOriginateResponse(fields)}`);
                        if (fields.Response && fields.Response !== 'Success') {
                            fail(new Error(`Originate refusé: ${describeOriginateResponse(fields)}`));
                            return;
                        }

                        closeTracking(queuedBlock || formatAmiAction(Object.entries(fields).map(([key, value]) => `${key}: ${value}`)));
                    }
                    break;
                default:
                    break;
            }
        };

        const handleActionBlock = (block) => {
            const fields = parseAmiBlock(block);

            if (fields.Event) {
                handleEvent(fields);
                return;
            }

            if (fields.Response) {
                if (fields.Response === 'Success') {
                    resolveQueued(block);
                } else {
                    fail(new Error(fields.Message || 'Erreur AMI Originate'));
                }
            }
        };

        const guard = setTimeout(() => {
            fail(new Error(`Timeout AMI après ${timeout} ms`));
        }, timeout);

        socket.connect(port, host, () => {
            console.log(`✅ Connecté à Asterisk AMI sur ${host}:${port}`);
        });

        socket.on('error', fail);

        socket.on('data', (data) => {
            buffer += data.toString();

            if (phase === 'greeting') {
                if (!buffer.includes(AMI_GREETING_MARKER)) return;
                console.log('✅ Identifié par Asterisk Manager');
                buffer = '';
                phase = 'login';
                socket.write(buildLoginAction());
                return;
            }

            while (buffer.includes(AMI_BLOCK_END)) {
                const block = takeBlock();

                if (phase === 'login') {
                    const fields = parseAmiBlock(block);
                    if (fields.Response !== 'Success') {
                        fail(new Error(`Authentification AMI refusée (${block.replace(/\r\n/g, ' | ')})`));
                        return;
                    }

                    console.log('✅ Authentifié auprès d\'Asterisk (AMI)');
                    phase = 'action';
                    socket.write(action);
                    continue;
                }

                handleActionBlock(block);
            }
        });
    });
}

function promptPreview(prompt, maxLength = 220) {
    if (!prompt || prompt.length <= maxLength) return prompt;
    return `${prompt.slice(0, maxLength)}... [tronqué ${prompt.length - maxLength} caractères]`;
}

function buildCallFailure({ callId, channel, targetNumber, message }) {
    return {
        success: false,
        callId,
        channel,
        targetNumber,
        message,
        timestamp: new Date().toISOString()
    };
}

function logOutgoingCall({ targetNumber, channel, callId, asteriskHost, asteriskPort }) {
    console.log(`\n${'═'.repeat(70)}`);
    console.log('📞 [APPEL SORTANT] Placement d\'un appel via AMI');
    console.log(`   Numéro cible: ${targetNumber}`);
    console.log(`   Canal: ${channel}`);
    console.log(`   ID d'appel: ${callId}`);
    console.log(`   Serveur Asterisk: ${asteriskHost}:${asteriskPort}`);
    console.log(`${'═'.repeat(70)}\n`);
}

/**
 * Place un appel sortant via Asterisk AMI (Asterisk Manager Interface).
 * Utilise une socket TCP directe au lieu de la CLI, absente du conteneur Node.js.
 *
 * @param {string} targetNumber - Numéro à appeler (ex: "33612345678")
 * @param {string} asteriskHost - Hôte du conteneur Asterisk (ex: "ia-asterisk")
 * @param {number} asteriskPort - Port AMI (défaut: 5038)
 * @param {string} openingPrompt - Prompt à injecter dans la session Gemini
 * @returns {Promise<Object>} Résultat de l'appel {success, callId, message}
 */
const OUTGOING_PROMPT_STORE = {
    current: null
};

const OUTGOING_CALLS = new Map();

export function getCurrentOutgoingPrompt() {
    return OUTGOING_PROMPT_STORE.current;
}

export function setCurrentOutgoingPrompt(prompt) {
    const next = typeof prompt === 'string' ? prompt.trim() : null;
    OUTGOING_PROMPT_STORE.current = next && next.length > 0 ? next : null;
}

const OUTGOING_CALL_CONTEXT = {
    prospectId: null,
    projectId: null,
    projectSlug: null,
    agentId: null,
    callerNumber: null,
    destinationNumber: null,
    callUuid: null,
    geminiConfig: {},
    kavkomConfig: {},
};

export function getCurrentOutgoingContext() {
    return { ...OUTGOING_CALL_CONTEXT };
}

export function setCurrentOutgoingContext(context = {}) {
    Object.assign(OUTGOING_CALL_CONTEXT, {
        prospectId: context.prospectId ?? null,
        projectId: context.projectId ?? null,
        projectSlug: context.projectSlug ?? null,
        agentId: context.agentId ?? null,
        callerNumber: context.callerNumber ?? null,
        destinationNumber: context.destinationNumber ?? null,
        callUuid: context.callUuid ?? null,
        geminiConfig: normalizeGeminiConfig(context.geminiConfig ?? context.gemini_config ?? {}),
        kavkomConfig: normalizeKavkomConfig(context.kavkomConfig ?? context.kavkom_config ?? {}),
    });
}

export function getOutgoingCallContext(callUuid = null) {
    if (callUuid && OUTGOING_CALLS.has(callUuid)) {
        return { ...OUTGOING_CALLS.get(callUuid) };
    }

    if (callUuid) {
        return {
            prospectId: null,
            projectId: null,
            projectSlug: null,
            agentId: null,
            callerNumber: null,
            destinationNumber: null,
            callUuid,
            openingPrompt: null,
            geminiConfig: {},
            kavkomConfig: {},
        };
    }

    return getCurrentOutgoingContext();
}

export function releaseOutgoingCallContext(callUuid = null) {
    if (callUuid) OUTGOING_CALLS.delete(callUuid);
}

function registerOutgoingCallContext(callUuid, context = {}) {
    const next = {
        prospectId: context.prospectId ?? null,
        projectId: context.projectId ?? null,
        projectSlug: context.projectSlug ?? null,
        agentId: context.agentId ?? null,
        callerNumber: context.callerNumber ?? null,
        destinationNumber: context.destinationNumber ?? null,
        callUuid,
        openingPrompt: context.openingPrompt ?? null,
        geminiConfig: normalizeGeminiConfig(context.geminiConfig ?? context.gemini_config ?? {}),
        kavkomConfig: normalizeKavkomConfig(context.kavkomConfig ?? context.kavkom_config ?? {}),
    };

    OUTGOING_CALLS.set(callUuid, next);
    setCurrentOutgoingContext(next);
    setCurrentOutgoingPrompt(next.openingPrompt);

    return next;
}

function normalizeGeminiConfig(config = {}) {
    return {
        gemini_api_key: config.gemini_api_key ?? config.api_key ?? null,
        gemini_live_model: config.gemini_live_model ?? config.live_model ?? config.model ?? null,
        gemini_summary_model: config.gemini_summary_model ?? config.summary_model ?? null,
    };
}

function normalizeKavkomConfig(config = {}) {
    return {
        extension: config.extension ?? null,
        password: config.password ?? null,
        caller_id_number: config.caller_id_number ?? config.callerIdNumber ?? config.phone_number ?? null,
        user_context: config.user_context ?? process.env.KAVKOM_USER_CONTEXT ?? null,
        transport: (config.transport ?? process.env.KAVKOM_SIP_TRANSPORT ?? 'udp').toLowerCase(),
        sip_port: Number(config.sip_port ?? process.env.KAVKOM_SIP_PORT ?? 5060),
    };
}

function sanitizeEndpointPart(value) {
    return String(value || '')
        .toLowerCase()
        .replace(/[^a-z0-9_-]+/g, '-')
        .replace(/^-+|-+$/g, '')
        .slice(0, 48);
}

function assertSafePjsipValue(label, value) {
    if (String(value ?? '').match(/[\r\n]/)) {
        throw new Error(`Configuration SIP invalide: ${label} contient un saut de ligne.`);
    }
}

function managedEndpointName(context = {}) {
    const base = context.agentId
        ? `ai-agent-${context.agentId}`
        : `ai-agent-${crypto.createHash('sha1').update(String(context.kavkomConfig?.extension || Date.now())).digest('hex').slice(0, 10)}`;

    return sanitizeEndpointPart(base) || `ai-agent-${crypto.randomBytes(4).toString('hex')}`;
}

function buildAgentPjsipBlock(endpointName, kavkomConfig) {
    const extension = String(kavkomConfig.extension || '').trim();
    const password = String(kavkomConfig.password || '');
    const userContext = String(kavkomConfig.user_context || '').trim();
    const transport = ['udp', 'tcp', 'tls'].includes(kavkomConfig.transport) ? kavkomConfig.transport : 'udp';
    const sipPort = Number(kavkomConfig.sip_port || (transport === 'tls' ? 5061 : 5060));
    const callerIdNumber = String(kavkomConfig.caller_id_number || extension).replace(/[^\d+]/g, '');

    for (const [label, value] of Object.entries({ extension, password, userContext, transport, sipPort, callerIdNumber })) {
        assertSafePjsipValue(label, value);
    }

    if (!extension || !password || !userContext) {
        throw new Error("Configuration Kavkom de l'agent IA incomplète: extension, mot de passe et KAVKOM_USER_CONTEXT sont requis.");
    }

    return `${MANAGED_BLOCK_PREFIX} ${endpointName}
[${endpointName}-reg]
type=registration
outbound_auth=${endpointName}-auth
transport=transport-${transport}
server_uri=sip:${userContext}:${sipPort}
client_uri=sip:${extension}@${userContext}
retry_interval=60
expiration=3600
contact_user=${extension}
auth_rejection_permanent=no

[${endpointName}-auth]
type=auth
auth_type=userpass
username=${extension}
password=${password}
realm=${userContext}

[${endpointName}]
type=endpoint
context=from-kavkom
transport=transport-${transport}
disallow=all
allow=alaw,ulaw
outbound_auth=${endpointName}-auth
aors=${endpointName}-aor
direct_media=no
from_user=${extension}
from_domain=${userContext}
callerid=${callerIdNumber}

[${endpointName}-aor]
type=aor
contact=sip:${userContext}:${sipPort}
${MANAGED_BLOCK_SUFFIX} ${endpointName}`;
}

async function reloadPjsipConfig(asteriskHost, asteriskPort) {
    const response = await amiAction(
        buildCommandAction('pjsip reload'),
        { host: asteriskHost, port: asteriskPort, timeout: AMI_TIMEOUT_MS }
    );

    if (!response.includes('Response: Follows') && !response.includes('Response: Success')) {
        console.warn('[Asterisk] Réponse inattendue au rechargement PJSIP:', response.replace(/\r\n/g, ' | '));
    }
}

async function amiCommand(command, asteriskHost, asteriskPort, timeout = AMI_TIMEOUT_MS) {
    return amiAction(
        buildCommandAction(command),
        { host: asteriskHost, port: asteriskPort, timeout }
    );
}

function sleep(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

function escapeRegExp(value) {
    return String(value).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

async function waitForPjsipEndpointReady(endpointName, asteriskHost, asteriskPort) {
    const deadline = Date.now() + PJSIP_ENDPOINT_READY_TIMEOUT_MS;
    let lastEndpointOutput = '';
    let lastRegistrationOutput = '';

    while (Date.now() < deadline) {
        lastEndpointOutput = await amiCommand(`pjsip show endpoint ${endpointName}`, asteriskHost, asteriskPort, 3000)
            .catch((error) => error.message);
        const endpointLoaded = lastEndpointOutput.includes(`Endpoint:  ${endpointName}/`)
            || lastEndpointOutput.includes(`Endpoint:  ${endpointName} `);

        lastRegistrationOutput = await amiCommand('pjsip show registrations', asteriskHost, asteriskPort, 3000)
            .catch((error) => error.message);
        const registrationReady = new RegExp(`${escapeRegExp(endpointName)}-reg/[^\\r\\n]*Registered`).test(lastRegistrationOutput);

        if (endpointLoaded && registrationReady) {
            return;
        }

        await sleep(500);
    }

    throw new Error(
        `Endpoint PJSIP ${endpointName} non prêt après ${PJSIP_ENDPOINT_READY_TIMEOUT_MS} ms. `
        + `Endpoint: ${lastEndpointOutput.replace(/\s+/g, ' ').slice(0, 220)} `
        + `Registrations: ${lastRegistrationOutput.replace(/\s+/g, ' ').slice(0, 220)}`
    );
}

async function withPjsipConfigLock(task) {
    const previous = pjsipConfigQueue;
    let release;
    pjsipConfigQueue = new Promise((resolve) => {
        release = resolve;
    });

    await previous.catch(() => {});

    try {
        return await task();
    } finally {
        release();
    }
}

async function upsertManagedPjsipBlock(endpointName, block, asteriskHost, asteriskPort) {
    await fs.mkdir(path.dirname(DYNAMIC_PJSIP_CONFIG_FILE), { recursive: true });

    let current = '';
    try {
        current = await fs.readFile(DYNAMIC_PJSIP_CONFIG_FILE, 'utf8');
    } catch (error) {
        if (error.code !== 'ENOENT') throw error;
    }

    const pattern = new RegExp(`${MANAGED_BLOCK_PREFIX} ${endpointName}[\\s\\S]*?${MANAGED_BLOCK_SUFFIX} ${endpointName}`, 'm');
    const next = pattern.test(current)
        ? current.replace(pattern, block)
        : `${current.trim() ? `${current.trim()}\n\n` : ''}${block}\n`;

    if (next === current) {
        return false;
    }

    await fs.writeFile(DYNAMIC_PJSIP_CONFIG_FILE, next, 'utf8');
    await reloadPjsipConfig(asteriskHost, asteriskPort);

    return true;
}

async function resolveAsteriskEndpoint(context, asteriskHost, asteriskPort) {
    const kavkomConfig = normalizeKavkomConfig(context.kavkomConfig || {});

    if (!kavkomConfig.extension && !kavkomConfig.password) {
        return {
            endpointName: AMI_TRUNK_ENDPOINT,
            callerIdNumber: kavkomConfig.caller_id_number || null,
        };
    }

    const endpointName = managedEndpointName({ ...context, kavkomConfig });
    const block = buildAgentPjsipBlock(endpointName, kavkomConfig);

    await withPjsipConfigLock(async () => {
        const configChanged = await upsertManagedPjsipBlock(endpointName, block, asteriskHost, asteriskPort);

        try {
            await waitForPjsipEndpointReady(endpointName, asteriskHost, asteriskPort);
        } catch (error) {
            if (configChanged) throw error;

            await reloadPjsipConfig(asteriskHost, asteriskPort);
            await waitForPjsipEndpointReady(endpointName, asteriskHost, asteriskPort);
        }
    });

    return {
        endpointName,
        callerIdNumber: kavkomConfig.caller_id_number || null,
    };
}

export async function placeOutgoingCall(targetNumber, asteriskHost = AMI_HOST, asteriskPort = AMI_PORT, openingPrompt = null, callContext = {}) {
    const callId = `call-${Date.now()}-${crypto.randomBytes(6).toString('hex')}`;
    // AudioSocket(uuid,service) exige un UUID valide : on le transmet au dialplan
    // via la variable CALLID (utilisée dans [outgoing-call] de extensions.conf).
    const callUuid = crypto.randomUUID();
    const context = registerOutgoingCallContext(callUuid, {
        ...callContext,
        openingPrompt,
        callUuid,
        destinationNumber: callContext.destinationNumber ?? targetNumber,
    });

    let channel = null;

    try {
        const { endpointName, callerIdNumber } = await resolveAsteriskEndpoint(context, asteriskHost, asteriskPort);
        channel = `PJSIP/${targetNumber}@${endpointName}`;
        logOutgoingCall({ targetNumber, channel, callId, asteriskHost, asteriskPort });

        const block = await trackOriginate(
            buildOriginateAction({ channel, callUuid, callId, callerIdNumber }),
            callId,
            { host: asteriskHost, port: asteriskPort }
        );
        const fields = parseAmiBlock(block);

        if (fields.Response !== 'Success') {
            const errorMsg = fields.Message || 'Erreur inconnue';
            console.error(`❌ Erreur AMI: ${errorMsg}`);
            releaseOutgoingCallContext(callUuid);
            return buildCallFailure({
                callId, channel, targetNumber,
                message: `Erreur AMI: ${errorMsg}`
            });
        }

        console.log('✅ Appel sortant placé avec succès via AMI');
        console.log('   Attente de la connexion SIP vers Kavkom...\n');

        if (openingPrompt) {
            console.log('🧠 [calling][prompt] Opening prompt fourni pour la session Gemini:', promptPreview(openingPrompt));
        }

        return {
            success: true,
            callId,
            callUuid,
            channel,
            targetNumber,
            prospectId: context.prospectId,
            projectId: context.projectId,
            projectSlug: context.projectSlug,
            agentId: context.agentId,
            callerNumber: context.callerNumber,
            destinationNumber: context.destinationNumber,
            callerIdNumber,
            endpointName,
            openingPrompt,
            message: 'Appel sortant en cours de placement',
            timestamp: new Date().toISOString()
        };
    } catch (err) {
        releaseOutgoingCallContext(callUuid);
        console.error(`❌ Échec du placement de l'appel: ${err.message}`);
        return buildCallFailure({
            callId, channel, targetNumber,
            message: `Erreur: ${err.message}`
        });
    }
}

/**
 * Liste tous les canaux actifs dans Asterisk via AMI.
 *
 * @param {string} asteriskHost - Hôte du conteneur Asterisk
 * @param {number} asteriskPort - Port AMI
 * @returns {Promise<string>} Liste des canaux
 */
export async function listAsteriskChannels(asteriskHost = AMI_HOST, asteriskPort = AMI_PORT) {
    try {
        const raw = await amiAction(
            formatAmiAction(['Action: CoreShowChannels', 'ActionID: channels']),
            { host: asteriskHost, port: asteriskPort, timeout: CHANNELS_TIMEOUT_MS, until: CHANNELS_END_MARKER }
        );

        const channels = [];
        for (const block of raw.split(AMI_BLOCK_END)) {
            // Attention : "Event: CoreShowChannelsComplete" contient aussi la
            // sous-chaîne "Event: CoreShowChannel" -> comparer la ligne entière.
            if (!block.startsWith(`Event: CoreShowChannel${AMI_LINE_END}`)) continue;

            const fields = parseAmiBlock(block);
            channels.push(
                `${fields.Channel || '?'}  ${fields.ChannelStateDesc || '?'}  ${fields.Application || '-'}  ${fields.ApplicationData || ''}`
            );
        }

        if (!channels.length) return 'Aucun canal actif';
        return `${channels.join('\n')}\n${channels.length} canal(aux) actif(s)`;
    } catch (err) {
        return `Impossible de récupérer les canaux (${err.message})`;
    }
}

/**
 * Raccroche un appel spécifique via AMI.
 *
 * @param {string} channelName - Nom du canal (ex: "PJSIP/kavkom-trunk-00000001")
 * @param {string} asteriskHost - Hôte du conteneur Asterisk
 * @param {number} asteriskPort - Port AMI
 * @returns {Promise<boolean>} true si succès
 */
export async function hangupCall(channelName, asteriskHost = AMI_HOST, asteriskPort = AMI_PORT) {
    try {
        const block = await amiAction(
            buildHangupAction(channelName),
            { host: asteriskHost, port: asteriskPort }
        );
        const success = parseAmiBlock(block).Response === 'Success';

        if (success) console.log(`✅ Appel ${channelName} raccroché`);
        else console.warn(`⚠️  Raccrochage refusé pour ${channelName}`);

        return success;
    } catch (err) {
        console.error(`❌ Erreur lors du raccrochage: ${err.message}`);
        return false;
    }
}
