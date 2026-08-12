# ai-phone-agent

Bridge Node.js permanent qui met un prospect, l'utilisateur du CRM et
l'agent vocal Gemini Live dans une même conférence FreeSWITCH, quand
l'utilisateur clique "Appeler avec l'IA" sur une fiche prospect.

Voir le contexte complet et l'architecture dans le plan de conception ; en
résumé :

```
CRM (Slide.vue) → Laravel (AiPhoneAgentController::trigger) → ce service
   → FreeSWITCH (ESL) : conférence à 3 (utilisateur + prospect + tap IA)
   → Gemini Live (WebSocket, audio PCM 16k/24k)
   → à la fin de l'appel : POST vers Laravel (AiPhoneAgentController::ingest)
```

## Prérequis (à faire une fois, hors de ce dépôt)

1. **Extension Kavkom dédiée** pour ce service (voir
   `freeswitch/gateway-example.xml`) — FreeSWITCH s'enregistre dessus et
   compose à travers elle, aussi bien vers l'extension de l'utilisateur que
   vers le numéro externe du prospect.
2. **FreeSWITCH** avec les modules `mod_sofia`, `mod_loopback`,
   `mod_conference` (inclus par défaut) et
   [`mod_audio_stream`](https://github.com/amigniter/mod_audio_stream)
   (à compiler/installer séparément) chargés.
   - Ajoutez le gateway Kavkom : `freeswitch/gateway-example.xml`.
   - Ajoutez l'extension de dialplan : `freeswitch/dialplan-example.xml`.
   - Ajoutez le profil de conférence : `freeswitch/conference-profile-example.xml`.
   - Activez l'event socket (ESL) dans `conf/autoload_configs/event_socket.conf.xml`
     (host/port/password — à renseigner dans `.env`).
3. **Node.js 18+** sur ce même serveur (ou un serveur avec accès réseau à
   FreeSWITCH en ESL), pour faire tourner ce service en permanence.
4. Accès réseau sortant vers `generativelanguage.googleapis.com` (Gemini
   Live) et vers l'URL publique de votre instance Laravel.

## Installation

```sh
cd ai-phone-agent
npm install
cp .env.example .env
# éditer .env : GEMINI_API_KEY, AI_PHONE_AGENT_SHARED_SECRET (même valeur
# que dans le .env Laravel), LARAVEL_BASE_URL, FS_ESL_*, FS_DIAL_PREFIX_*,
# FS_CALLER_ID_NUMBER (votre DID Kavkom sortant), WS_PUBLIC_URL (adresse
# joignable par FreeSWITCH pour se connecter en retour)
```

Côté Laravel (`.env` du CRM) :

```
AI_PHONE_AGENT_SHARED_SECRET=   # même valeur que ci-dessus
AI_PHONE_AGENT_BRIDGE_URL=http://<ce-serveur>:4000
```

## Lancer le service

```sh
npm start
```

En production, superviser le processus (il doit rester actif en
permanence) :

- **pm2** : `pm2 start index.js --name ai-phone-agent`
- **systemd** : créez `/etc/systemd/system/ai-phone-agent.service` avec
  `ExecStart=/usr/bin/node /chemin/vers/ai-phone-agent/index.js`,
  `Restart=always`, `WorkingDirectory=/chemin/vers/ai-phone-agent`.

## Fichiers

- `index.js` — point d'entrée : connexion ESL, démarre l'API HTTP et le
  serveur WebSocket.
- `http-server.js` — `POST /calls` (appelé par Laravel), orchestre les 3
  legs de la conférence via `esl-client.js`.
- `esl-client.js` — connexion ESL à FreeSWITCH (origination, attache du
  flux audio, écoute des événements de réponse/raccroché). **C'est la
  partie la plus susceptible de nécessiter des ajustements** selon votre
  configuration FreeSWITCH exacte (nom du gateway, profil de conférence) —
  voir les commentaires dans le fichier.
- `ws-server.js` — reçoit les connexions WebSocket de `mod_audio_stream`
  (une par appel), instancie un `GeminiCallBridge` par appel.
- `gemini-call-bridge.js` — relaie l'audio vers/depuis Gemini Live,
  déclare l'outil `record_prospect_info` (function calling), accumule le
  transcript, notifie Laravel à la fin de l'appel.
- `freeswitch/` — extraits de configuration FreeSWITCH à copier/adapter
  (gateway Kavkom, extension de dialplan, profil de conférence).

## Vérification / mise au point

Aucun test automatisé de bout en bout n'est possible sans un vrai
FreeSWITCH connecté à Kavkom. Procédez par étapes :

1. **ESL** : lancez `npm start` et vérifiez le log
   `[ESL] Connected to FreeSWITCH.` (sinon vérifiez host/port/mot de passe
   dans `.env` et `event_socket.conf.xml`).
2. **Conférence à 2, sans IA** : depuis la console FreeSWITCH,
   `originate {origination_caller_id_number=...}sofia/gateway/kavkom/<votre extension> &conference(test@ai-agent)`
   puis un second `originate` similaire vers un numéro externe — vérifiez
   que les deux s'entendent.
3. **Tap IA** : déclenchez un appel via le bouton "Appeler avec l'IA" dans
   le CRM (avec une vraie config Kavkom utilisateur) et vérifiez dans les
   logs de ce service que la conférence est créée
   (`[HTTP] ...`), que `mod_audio_stream` se connecte
   (`[WS] Listening...` puis une connexion entrante), et que Gemini répond
   (`[Gemini <uuid>] Setup complete.`).
4. **Function calling / remplissage automatique** : à la fin d'un appel de
   test, vérifiez dans les logs Laravel (`storage/logs/ai-phone-agent.log`)
   que `AiPhoneAgentController::ingest()` a bien reçu et traité les
   données, et que la fiche prospect s'est mise à jour.

Le protocole `toolCall`/`toolResponse` (function calling) suit le format
standard de function calling de Gemini, mais n'a pas pu être testé contre
une session Gemini Live réelle faute de clé API dans cet environnement de
développement — si les champs ne remontent pas, activez des logs bruts des
messages Gemini dans `gemini-call-bridge.js` pour comparer au format
effectivement reçu.
