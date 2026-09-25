import net from 'net';
import crypto from 'crypto';

// ─────────────────────────────────────────────────────────────────────────────
// Paramètres AMI (surchargeables via .env)
// ─────────────────────────────────────────────────────────────────────────────
const AMI_HOST = process.env.ASTERISK_AMI_HOST || 'ia-asterisk';
const AMI_PORT = parseInt(process.env.ASTERISK_AMI_PORT || '5038', 10);
const AMI_USERNAME = process.env.ASTERISK_AMI_USERNAME || 'default';
// Doit correspondre à `secret` de [default] dans asterisk/manager.conf
const AMI_SECRET = process.env.ASTERISK_AMI_SECRET || 'HeroesAmi2026!';
const AMI_TIMEOUT_MS = parseInt(process.env.ASTERISK_AMI_TIMEOUT_MS || '10000', 10);

// Asterisk 21+ n'embarque plus chan_sip : le trunk s'appelle "PJSIP" et non "SIP"
// ("SIP/..." échoue avec "Unable to create channel of type 'SIP'").
// L'AOR de Kavkom ne définit que le domaine (contact=sip:aria-madacom.kavkom.com:5061),
// le dial string correct est donc "PJSIP/<numero>@<endpoint>" ; la forme
// "PJSIP/kavkom-trunk/<numero>" échoue avec
// "Could not create dialog to invalid URI '<numero>'" (cf. Asterisk 22 / chan_pjsip).
const AMI_TRUNK_ENDPOINT = process.env.ASTERISK_TRUNK_ENDPOINT || 'kavkom-trunk';

const AMI_LINE_END = '\r\n';
const AMI_BLOCK_END = '\r\n\r\n';
const AMI_GREETING_MARKER = 'Asterisk Call Manager';
const CHANNELS_END_MARKER = 'CoreShowChannelsComplete';
const CHANNELS_TIMEOUT_MS = 4000;

const CALL_CONTEXT = 'outgoing-call';
const CALL_EXTEN = 's';

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

function buildOriginateAction({ channel, callUuid, callId }) {
    return formatAmiAction([
        'Action: Originate',
        `Channel: ${channel}`,
        `Context: ${CALL_CONTEXT}`,
        `Exten: ${CALL_EXTEN}`,
        'Priority: 1',
        `Variable: CALLID=${callUuid}`,
        'Async: true',
        `ActionID: ${callId}`
    ]);
}

function buildHangupAction(channelName) {
    return formatAmiAction([
        'Action: Hangup',
        `Channel: ${channelName}`,
        `ActionID: hangup-${Date.now()}`
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

        /** Consomme le premier bloc complet du buffer (le buffer est vidé). */
        const takeBlock = () => {
            const block = buffer.slice(0, buffer.indexOf(AMI_BLOCK_END));
            buffer = '';
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

            if (buffer.includes(AMI_BLOCK_END)) finish(null, takeBlock());
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
    });
}

export async function placeOutgoingCall(targetNumber, asteriskHost = AMI_HOST, asteriskPort = AMI_PORT, openingPrompt = null, callContext = {}) {
    const callId = `call-${Date.now()}-${crypto.randomBytes(6).toString('hex')}`;
    const channel = `PJSIP/${targetNumber}@${AMI_TRUNK_ENDPOINT}`;
    // AudioSocket(uuid,service) exige un UUID valide : on le transmet au dialplan
    // via la variable CALLID (utilisée dans [outgoing-call] de extensions.conf).
    const callUuid = crypto.randomUUID();

    setCurrentOutgoingContext({
        ...callContext,
        callUuid,
        destinationNumber: callContext.destinationNumber ?? targetNumber,
    });
    setCurrentOutgoingPrompt(openingPrompt);
    logOutgoingCall({ targetNumber, channel, callId, asteriskHost, asteriskPort });

    try {
        const block = await amiAction(
            buildOriginateAction({ channel, callUuid, callId }),
            { host: asteriskHost, port: asteriskPort }
        );
        const fields = parseAmiBlock(block);

        if (fields.Response !== 'Success') {
            const errorMsg = fields.Message || 'Erreur inconnue';
            console.error(`❌ Erreur AMI: ${errorMsg}`);
            return buildCallFailure({
                callId, channel, targetNumber,
                message: `Erreur AMI: ${errorMsg}`
            });
        }

        console.log('✅ Appel sortant placé avec succès via AMI');
        console.log('   Attente de la connexion SIP vers Kavkom...\n');

        if (openingPrompt) {
            console.log('🧠 [calling][prompt] Opening prompt fourni pour la session Gemini:', openingPrompt.slice(0, 220));
        }

        return {
            success: true,
            callId,
            callUuid,
            channel,
            targetNumber,
            prospectId: OUTGOING_CALL_CONTEXT.prospectId,
            projectId: OUTGOING_CALL_CONTEXT.projectId,
            projectSlug: OUTGOING_CALL_CONTEXT.projectSlug,
            agentId: OUTGOING_CALL_CONTEXT.agentId,
            callerNumber: OUTGOING_CALL_CONTEXT.callerNumber,
            destinationNumber: OUTGOING_CALL_CONTEXT.destinationNumber,
            openingPrompt,
            message: 'Appel sortant en cours de placement',
            timestamp: new Date().toISOString()
        };
    } catch (err) {
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
