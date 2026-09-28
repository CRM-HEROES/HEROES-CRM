/**
 * 📞 Test d'appel sortant via l'API REST
 * 
 * Usage:
 *   node outgoing-call-test.js [phone] [duration] [host] [port]
 *   
 * Exemples:
 *   node outgoing-call-test.js 33612345678
 *   node outgoing-call-test.js 33612345678 20 localhost 3000
 */

import http from 'http';

const phoneNumber = process.argv[2] || '33612345678';
const duration = parseInt(process.argv[3]) || 15;
const host = process.argv[4] || 'localhost';
const port = parseInt(process.argv[5]) || 3000;

console.log(`\n${'═'.repeat(70)}`);
console.log(`📞 TEST D'APPEL SORTANT`);
console.log(`   Serveur: ${host}:${port}`);
console.log(`   Numéro: ${phoneNumber}`);
console.log(`   Durée: ${duration}s`);
console.log(`${'═'.repeat(70)}\n`);

// Fonction pour faire l'appel
function makeOutgoingCall() {
    return new Promise((resolve, reject) => {
        const postData = JSON.stringify({
            phoneNumber: phoneNumber
        });

        const options = {
            hostname: host,
            port: port,
            path: '/call',
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Content-Length': Buffer.byteLength(postData)
            }
        };

        const req = http.request(options, (res) => {
            let data = '';

            res.on('data', (chunk) => {
                data += chunk;
            });

            res.on('end', () => {
                try {
                    const response = JSON.parse(data);
                    resolve(response);
                } catch (e) {
                    reject(new Error('Invalid JSON response: ' + data));
                }
            });
        });

        req.on('error', (e) => {
            reject(e);
        });

        req.write(postData);
        req.end();
    });
}

// Fonction pour récupérer les canaux actifs
function getChannels() {
    return new Promise((resolve) => {
        const options = {
            hostname: host,
            port: port,
            path: '/channels',
            method: 'GET'
        };

        http.request(options, (res) => {
            let data = '';

            res.on('data', (chunk) => {
                data += chunk;
            });

            res.on('end', () => {
                try {
                    console.log('📊 Canaux Asterisk actifs:');
                    const response = JSON.parse(data);
                    console.log('   ' + response.channels.split('\n').slice(0, 5).join('\n   '));
                    resolve();
                } catch (e) {
                    resolve();
                }
            });
        }).end();
    });
}

// Fonction pour raccrocher l'appel
function hangupChannel(channelName) {
    return new Promise((resolve) => {
        const postData = JSON.stringify({
            channelName: channelName
        });

        const options = {
            hostname: host,
            port: port,
            path: '/hangup',
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Content-Length': Buffer.byteLength(postData)
            }
        };

        http.request(options, (res) => {
            res.on('end', () => {
                resolve();
            });
        }).on('error', () => {
            resolve();
        }).end(postData);
    });
}

// Exécuter le test
(async () => {
    try {
        // 1. Vérifier la connexion
        console.log('🔗 Vérification de la connexion à l\'API...');
        let status = false;
        try {
            const statusRes = await new Promise((resolve, reject) => {
                http.get(`http://${host}:${port}/status`, (res) => {
                    if (res.statusCode === 200) resolve(true);
                    else reject(new Error('Status code: ' + res.statusCode));
                }).on('error', reject);
            });
            status = true;
        } catch (e) {
            console.error(`❌ Impossible de se connecter à ${host}:${port}`);
            console.error(`   Erreur: ${e.message}`);
            console.error(`\n💡 Assurez-vous que ia-phone-call est en cours d'exécution:`);
            console.error(`   docker compose logs ia-phone-call`);
            process.exit(1);
        }
        
        if (status) {
            console.log(`✅ Connecté à ${host}:${port}\n`);
        }

        // 2. Placer l'appel
        console.log('📞 Placement de l\'appel sortant...');
        const callResult = await makeOutgoingCall();
        
        if (!callResult.success) {
            console.error('❌ Erreur lors du placement de l\'appel:');
            console.error('   ' + (callResult.message || callResult.error));
            process.exit(1);
        }

        console.log(`✅ Appel placé avec succès`);
        console.log(`   ID: ${callResult.callId}`);
        console.log(`   Message: ${callResult.message}\n`);

        // 3. Afficher les canaux actifs
        await getChannels();
        console.log('');

        // 4. Attendre la fin de l'appel
        console.log(`⏳ Appel en cours... Durée: ${duration}s`);
        for (let i = 1; i <= duration; i++) {
            process.stdout.write(`\r   ${i}/${duration}s`);
            await new Promise(resolve => setTimeout(resolve, 1000));
        }
        console.log('\n');

        // 5. Résultats
        console.log(`${'═'.repeat(70)}`);
        console.log(`✅ TEST TERMINÉ`);
        console.log(`   Appel: ${phoneNumber}`);
        console.log(`   Durée: ${duration}s`);
        console.log(`   Timestamp: ${new Date().toISOString()}`);
        console.log(`${'═'.repeat(70)}\n`);

    } catch (error) {
        console.error('❌ Erreur:', error.message);
        process.exit(1);
    }
})();
