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

// Asterisk 21+ n'embarque plus chan_sip : le trunk s'appelle "PJSIP" et non "SIP"
// ("SIP/..." échoue avec "Unable to create channel of type 'SIP'").
// L'AOR de Kavkom ne définit que le domaine (contact=sip:aria-madacom.kavkom.com:5061),
// le dial string correct est donc "PJSIP/<numero>@<endpoint>" ; la forme
// "PJSIP/kavkom-trunk/<numero>" échoue avec
// "Could not create dialog to invalid URI '<numero>'" (cf. Asterisk 22 / chan_pjsip).
const AMI_TRUNK_ENDPOINT = process.env.ASTERISK_TRUNK_ENDPOINT || 'kavkom-trunk';

// Durée maximale du dialogue de test : 10s pour toute la phase de mise en relation
const AMI_TIMEOUT_MS = parseInt(process.env.ASTERISK_AMI_TIMEOUT_MS || '10000', 10);

/**
 * Convertit un bloc de réponse AMI ("Header: valeur\r\n...) en objet.
 */
function parseAmiBlock(block) {
    const fields = {};

    for (const line of block.split('\r\n')) {
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
 * @param {string} action - Action AMI déjà formatée (lignes terminées par \r\n)
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
        // `buffer` = données non encore consommées, `response` = réponse de l'action
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

        const guard = setTimeout(() => {
            if (until && response.includes(until)) return finish(null, response);
            finish(new Error(`Timeout AMI après ${timeout} ms`));
        }, timeout);

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

            // 1) Greeting : une SEULE ligne ("Asterisk Call Manager/22.0.0\r\n"),
            //    il ne faut donc surtout pas attendre un \r\n\r\n ici.
            if (phase === 'greeting' && buffer.includes('Asterisk Call Manager')) {
                console.log('✅ Identifié par Asterisk Manager');
                buffer = '';
                phase = 'login';
                socket.write(
                    `Action: Login\r\n` +
                    `Username: ${AMI_USERNAME}\r\n` +
                    `Secret: ${AMI_SECRET}\r\n` +
                    `ActionID: login\r\n\r\n`
                );
                return;
            }

            // 2) Réponse au Login
            if (phase === 'login' && buffer.includes('\r\n\r\n')) {
                const block = buffer.slice(0, buffer.indexOf('\r\n\r\n'));
                buffer = '';
                response = '';

                if (!block.includes('Response: Success')) {
                    return finish(new Error(`Authentification AMI refusée (${block.replace(/\r\n/g, ' | ')})`));
                }

                console.log('✅ Authentifié auprès d\'Asterisk (AMI)');
                phase = 'action';
                socket.write(action);
                return;
            }

            // 3) Réponse à l'action
            if (phase === 'action') {
                response += chunk;
                if (until) {
                    if (response.includes(until)) finish(null, response);
                    return;
                }
                if (buffer.includes('\r\n\r\n')) {
                    finish(null, buffer.slice(0, buffer.indexOf('\r\n\r\n')));
                }
            }
        });
    });
}

/**
 * Service pour placer des appels sortants via Asterisk AMI (Asterisk Manager Interface).
 * Utilise une socket TCP directe au lieu de CLI qui n'existe pas dans le conteneur Node.js.
 *
 * @param {string} targetNumber - Numéro à appeler (ex: "33612345678")
 * @param {string} asteriskHost - Hôte du conteneur Asterisk (ex: "ia-asterisk")
 * @param {number} asteriskPort - Port AMI (défaut: 5038)
 * @returns {Promise<Object>} Résultat de l'appel {success, callId, message}
 */
export async function placeOutgoingCall(targetNumber, asteriskHost = AMI_HOST, asteriskPort = AMI_PORT) {
    const callId = `call-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
    const channel = `PJSIP/${targetNumber}@${AMI_TRUNK_ENDPOINT}`;
    // AudioSocket(uuid,service) exige un UUID valide : on le transmet au dialplan
    // via la variable CALLID (utilisée dans [outgoing-call] de extensions.conf).
    const callUuid = crypto.randomUUID();

    console.log(`\n${'═'.repeat(70)}`);
    console.log(`📞 [APPEL SORTANT] Placement d'un appel via AMI`);
    console.log(`   Numéro cible: ${targetNumber}`);
    console.log(`   Canal: ${channel}`);
    console.log(`   ID d'appel: ${callId}`);
    console.log(`   Serveur Asterisk: ${asteriskHost}:${asteriskPort}`);
    console.log(`${'═'.repeat(70)}\n`);

    const originateCmd =
        `Action: Originate\r\n` +
        `Channel: ${channel}\r\n` +
        `Context: outgoing-call\r\n` +
        `Exten: s\r\n` +
        `Priority: 1\r\n` +
        `Variable: CALLID=${callUuid}\r\n` +
        `Async: true\r\n` +
        `ActionID: ${callId}\r\n` +
        `\r\n`;

    try {
        const block = await amiAction(originateCmd, { host: asteriskHost, port: asteriskPort });
        const fields = parseAmiBlock(block);

        if (fields.Response === 'Success') {
            console.log(`✅ Appel sortant placé avec succès via AMI`);
            console.log(`   Attente de la connexion SIP vers Kavkom...\n`);
            return {
                success: true,
                callId,
                callUuid,
                channel,
                targetNumber,
                message: 'Appel sortant en cours de placement',
                timestamp: new Date().toISOString()
            };
        }

        const errorMsg = fields.Message || 'Erreur inconnue';
        console.error(`❌ Erreur AMI: ${errorMsg}`);
        return {
            success: false,
            callId,
            channel,
            targetNumber,
            message: `Erreur AMI: ${errorMsg}`,
            timestamp: new Date().toISOString()
        };
    } catch (err) {
        console.error(`❌ Échec du placement de l'appel: ${err.message}`);
        return {
            success: false,
            callId,
            channel,
            targetNumber,
            message: `Erreur: ${err.message}`,
            timestamp: new Date().toISOString()
        };
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
            `Action: CoreShowChannels\r\nActionID: channels\r\n\r\n`,
            { host: asteriskHost, port: asteriskPort, timeout: 4000, until: 'CoreShowChannelsComplete' }
        );

        const channels = [];
        for (const block of raw.split('\r\n\r\n')) {
            // Attention : "Event: CoreShowChannelsComplete" contient aussi la
            // sous-chaîne "Event: CoreShowChannel" -> comparer la ligne entière.
            if (!block.startsWith('Event: CoreShowChannel\r\n')) continue;
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
            `Action: Hangup\r\nChannel: ${channelName}\r\nActionID: hangup-${Date.now()}\r\n\r\n`,
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
