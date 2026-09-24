# 📞 ia-phone-call — API & exploitation

Passerelle **Asterisk → Gemini Live → Kavkom** : reçoit les appels entrants du trunk SIP
Kavkom, les branche sur un agent IA (Gemini Live, audio temps réel) et sait aussi
placer des **appels sortants**.

---

## 1. Architecture

```
CRM Laravel / toi ──HTTP :3000──► ia-phone-call (Node)
                                     │  AMI 5038 (Login) ▼
Kavkom ◄──SIP/TLS :5061── ia-asterisk ┘
         (trunk)              │
                              └─ AudioSocket TCP :9701 ──► ia-phone-call ──WS──► Gemini Live
```

| Interface | Port (hôte → conteneur) | Sens | Rôle |
|---|---|---|---|
| **API REST HTTP** | `3000 → 3000` | toi → Node | placer / voir / raccrocher un appel |
| **AudioSocket** | `9701 → 9701` | Asterisk → Node | audio PCM 8 kHz brut (protocole binaire, pas du HTTP) |
| **AMI** | `5038 → 5038` | Node → Asterisk | pilotage (`default` / `HeroesAmi2026!`, cf. `asterisk/manager.conf`) |
| **SIP trunk** | sortant `5061/tcp` (TLS) | Asterisk → Kavkom | enregistrement + appels entrants/sortants |
| **RTP** | `10000-10099/udp` | — | média des appels |

> L'agent IA est déclenché par le dialplan `asterisk/extensions.conf` (`from-kavkom` pour
> l'entrant, `outgoing-call` pour le sortant) via `AudioSocket(uuid, ia-phone-call:9701)`.

---

## 2. Placer un appel sortant

```bash
curl -s -X POST http://localhost:3000/call \
  -H 'Content-Type: application/json' \
  -d '{"phoneNumber":"33660297317"}'
```

Réponse :

```json
{
  "success": true,
  "callId": "call-1790199469204-sj2olj63h",
  "callUuid": "8ee30681-7410-4e72-a487-783fa64e4d4f",
  "channel": "PJSIP/33660297317@kavkom-trunk",
  "targetNumber": "33660297317",
  "message": "Appel sortant en cours de placement",
  "timestamp": "2026-09-23T21:37:49.213Z"
}
```

Déroulé complet :

1. `POST /call` → le service Node ouvre une session AMI (Login + `Originate`).
2. Asterisk compose `PJSIP/<numéro>@kavkom-trunk` : `INVITE` → `407` → `INVITE` authentifié → `200 OK`.
3. Le dialplan `[outgoing-call]` exécute `Answer`, `Wait(1)` puis
   `AudioSocket(${CALLID}, ia-phone-call:9701)`.
4. L'agent Gemini parle au correspondant (aller-retour audio via AudioSocket).

⚠️ Le numéro doit être en **format international, sans `+` ni espaces** (`33660297317`).
Depuis un autre conteneur, remplacer `localhost` par `ia-phone-call`.

---

## 3. Endpoints

| Méthode | Route | Corps | Description |
|---|---|---|---|
| GET | `/` | — | Documentation + exemples |
| GET | `/status` | — | Ping : `{"status":"ok","audioPort":9701,"apiPort":3000}` |
| POST | `/call` | `{"phoneNumber":"33…"}` (ou `number`) | Place un appel sortant |
| GET | `/channels` | — | Liste les canaux/appels en cours |
| POST | `/hangup` | `{"channelName":"PJSIP/kavkom-trunk-00000003"}` (ou `channel`) | Raccroche un appel |

```bash
curl -s http://localhost:3000/            # documentation
curl -s http://localhost:3000/status      # état du service
curl -s http://localhost:3000/channels    # appels en cours
curl -s -X POST http://localhost:3000/hangup \
  -H 'Content-Type: application/json' \
  -d '{"channelName":"PJSIP/kavkom-trunk-00000003"}'
```

Codes HTTP : `200` si l'action est acceptée, `400` (paramètre manquant/invalide),
`404` (route inconnue), `500` (échec AMI — le message d'erreur est dans le JSON).

---

## 4. Voir les logs

```bash
# Passerelle IA (appels, Gemini, AudioSocket)
docker logs -f --tail 200 ia-phone-call | grep -E "APPEL|Gemini|AudioSocket|sortant|❌"

# Asterisk (signalisation, dialplan, erreurs)
docker logs -f --tail 200 ia-asterisk | grep -E "Executing|answered|ERROR|WARNING"

# Équivalent via compose
docker compose logs -f ia-phone-call ia-asterisk
```

**Trace SIP complète** (indispensable pour le trunk) :

```bash
docker exec ia-asterisk asterisk -rx "pjsip set logger on"
docker logs -f ia-asterisk | grep -E "SIP/2.0|INVITE|REGISTER|User-Agent"
docker exec ia-asterisk asterisk -rx "pjsip set logger off"
```

---

## 5. Voir et piloter les appels

```bash
# Appels/appels en cours
curl -s http://localhost:3000/channels
docker exec ia-asterisk asterisk -rx "core show channels verbose"
docker exec ia-asterisk asterisk -rx "core show channels count"

# Raccrocher
docker exec ia-asterisk asterisk -rx "channel request hangup all"

# État du trunk Kavkom
docker exec ia-asterisk asterisk -rx "pjsip show registrations"
docker exec ia-asterisk asterisk -rx "pjsip show endpoint kavkom-trunk"

# Sessions AMI ouvertes (service Node)
docker exec ia-asterisk asterisk -rx "manager show connected"
```

### Ce qui n'existe pas encore

- **Historique des appels (CDR)** : le module `cdr_csv` échoue
  (`Unable to open file /var/log/asterisk/cdr-csv/Master.csv`) → aucun historique n'est écrit.
  Seuls les logs console gardent une trace.
- **Enregistrement audio** : le volume `calls_data` est monté
  (`/var/spool/asterisk/monitor` côté Asterisk, `/data/calls` côté Node) mais aucun
  `Record()` n'est présent dans le dialplan.
- **Webhook CRM** : `LARAVEL_BASE_URL` et `AI_PHONE_AGENT_SHARED_SECRET` figurent dans `.env`
  mais ne sont pas lus par le code.

---

## 6. Variables d'environnement réellement utilisées

| Variable | Défaut | Usage |
|---|---|---|
| `PORT` | `9701` | port AudioSocket du serveur Node |
| `API_PORT` | `3000` | port de l'API REST |
| `ASTERISK_AMI_HOST` | `ia-asterisk` | hôte AMI |
| `ASTERISK_AMI_PORT` | `5038` | port AMI |
| `ASTERISK_AMI_USERNAME` / `ASTERISK_AMI_SECRET` | `default` / `HeroesAmi2026!` | compte AMI (doit correspondre à `asterisk/manager.conf`) |
| `ASTERISK_TRUNK_ENDPOINT` | `kavkom-trunk` | endpoint PJSIP de sortie → canal `PJSIP/<numéro>@<endpoint>` |
| `GEMINI_API_KEY`, `GEMINI_LIVE_MODEL`, `GEMINI_OPENING_PROMPT` | — | session Gemini Live |

Côté conteneur Asterisk (générées depuis `.env` dans `pjsip.conf` par
`asterisk/docker-entrypoint.sh`) : `KAVKOM_EXTENSION`, `KAVKOM_PASSWORD`,
`KAVKOM_USER_CONTEXT`, `KAVKOM_SIP_TRANSPORT`, `KAVKOM_SIP_PORT` et
`KAVKOM_USER_AGENT` (⚠️ doit rester **neutre** : le SBC de Kavkom abandonne
silencieusement toute requête dont le `User-Agent` contient « Asterisk »).

> `HTTP_PORT=4000`, `WS_PORT`, `TRANSCRIPT_WS_PORT`, `TEST_MODE`,
> `TEST_ALLOWED_NUMBERS`, `CALL_RECORDING_DIR` présents dans `.env` appartiennent à
> l'ancienne stack FreeSWITCH : **non utilisés** par ce service.

---

## 7. Pièges / dépannage

| Symptôme | Cause | Solution |
|---|---|---|
| `Rejected` dans `pjsip show registrations` | User-Agent contenant « Asterisk » ignoré par Kavkom | laisser `KAVKOM_USER_AGENT` neutre (`HeroesCRM-Phone/1.0`) |
| `Could not create dialog to invalid URI '<num>'` | mauvais format de canal | utiliser `PJSIP/<num>@kavkom-trunk`, pas `PJSIP/kavkom-trunk/<num>` |
| `Unable to create channel of type 'SIP'` | `chan_sip` supprimé depuis Asterisk 21 | préfixer par `PJSIP/` |
| `Message: Permission denied` (AMI) | action sans `Login` | `secret` dans `manager.conf` + Login dans `outgoing-call.service.js` |
| `Reached timeout after 2000 ms of no activity on AudioSocket` | correspondant silencieux / Gemini lent : `app_audiosocket` coupe après 2 s sans activité | keep-alive de silence dans `asterisk.service.js` |
| `Connection refused` sur `:3000` | service arrêté | `docker compose up -d ia-phone-call ia-asterisk` |

✨ **Toute modification de `src/*.js` nécessite un rebuild** (le code est copié dans
l'image, pas monté en volume) :

```bash
cd ia-phone-call && docker compose up -d --build ia-phone-call
```

---

## 8. Documentation liée

- [`OUTGOING_CALLS.md`](./OUTGOING_CALLS.md) — appels sortants en détail (flux, API, tests)
- [`src/test/README.md`](./src/test/README.md) — tests AudioSocket / Gemini
- `asterisk/pjsip.conf.template` — configuration du trunk Kavkom
- `asterisk/extensions.conf` — dialplan (`from-kavkom`, `outgoing-call`)
