# 📞 Appels Sortants (IA → Kavkom)

Guide complet pour placer des appels sortants depuis votre IA Gemini via Astérisk vers Kavkom.

## 🏗️ Architecture

```
ia-phone-call (Node.js)
  ↓ API REST (POST /call)
Asterisk PBX
  ↓ SIP TLS:5061
Kavkom (Opérateur)
  ↓ Appel RTP
Destinataire
  ↓ Audio VoIP
Asterisk ↔ ia-phone-call (AudioSocket TCP:9701)
  ↓ WebSocket
Gemini Live API
  ↓ Réponse audio
```

## 🚀 Quickstart - Test en 30 secondes

### Option 1: Avec Node.js (Recommandé)

```bash
cd /home/ranto/Documents/crm/HEROES-CRM/ia-phone-call

# Redémarrer les conteneurs
docker compose up -d --build

# Attendre 5s
sleep 5

# Tester un appel sortant (15 secondes)
node src/test/outgoing-call-test.js 33612345678 15
```

**Résultat attendu:**
```
📞 TEST D'APPEL SORTANT
   Serveur: localhost:3000
   Numéro: 33612345678
   Durée: 15s

🔗 Vérification de la connexion à l'API...
✅ Connecté à localhost:3000

📞 Placement de l'appel sortant...
✅ Appel placé avec succès
   ID: call-1695489600000-abc123def
   Message: Appel sortant en cours de placement

📊 Canaux Asterisk actifs:
   PJSIP/kavkom-trunk/33612345678-00000001 Up

⏳ Appel en cours... Durée: 15s
   15/15s
```

### Option 2: Avec curl (pour tester l'API seule)

```bash
# Placer un appel
curl -X POST http://localhost:3000/call \
  -H "Content-Type: application/json" \
  -d '{"phoneNumber":"33612345678"}'

# Voir les canaux actifs
curl http://localhost:3000/channels

# Raccrocher un appel
curl -X POST http://localhost:3000/hangup \
  -H "Content-Type: application/json" \
  -d '{"channelName":"PJSIP/kavkom-trunk/33612345678-00000001"}'
```

---

## 🔌 API REST Détaillée

### `GET /`
Documentation de l'API

**Réponse:**
```json
{
  "name": "IA Kavkom Gateway API",
  "endpoints": {
    "GET /status": "Statut du serveur",
    "GET /channels": "Liste des appels actifs",
    "POST /call": "Placer un appel sortant",
    "POST /hangup": "Raccrocher un appel"
  }
}
```

---

### `GET /status`
Vérifie que le serveur est fonctionnel

**Requête:**
```bash
curl http://localhost:3000/status
```

**Réponse:**
```json
{
  "status": "ok",
  "service": "IA Kavkom Gateway",
  "audioPort": 9701,
  "apiPort": 3000,
  "asteriskHost": "ia-asterisk",
  "timestamp": "2026-09-23T19:30:00.000Z"
}
```

---

### `POST /call`
Place un appel sortant

**Requête:**
```bash
curl -X POST http://localhost:3000/call \
  -H "Content-Type: application/json" \
  -d '{
    "phoneNumber": "33612345678"
  }'
```

**Réponse (Succès):**
```json
{
  "success": true,
  "callId": "call-1695489600000-abc123def",
  "targetNumber": "33612345678",
  "message": "Appel sortant en cours de placement",
  "timestamp": "2026-09-23T19:30:00.000Z"
}
```

**Réponse (Erreur):**
```json
{
  "success": false,
  "callId": "call-1695489600000-abc123def",
  "targetNumber": "33612345678",
  "message": "Erreur: docker: command not found",
  "timestamp": "2026-09-23T19:30:00.000Z"
}
```

**Paramètres:**
- `phoneNumber` (required) - Numéro à appeler (ex: "33612345678")

---

### `GET /channels`
Liste tous les canaux Asterisk actifs

**Requête:**
```bash
curl http://localhost:3000/channels
```

**Réponse:**
```json
{
  "channels": "Channel              State   App            Data\nPJSIP/kavkom-trunk/33612345678-00000001   Up      AudioSocket    ia-phone-call:9701\n1 active channel"
}
```

---

### `POST /hangup`
Raccroche un appel spécifique

**Requête:**
```bash
curl -X POST http://localhost:3000/hangup \
  -H "Content-Type: application/json" \
  -d '{
    "channelName": "PJSIP/kavkom-trunk/33612345678-00000001"
  }'
```

**Réponse:**
```json
{
  "success": true,
  "channelName": "PJSIP/kavkom-trunk/33612345678-00000001"
}
```

---

## 📊 Flux d'un appel sortant (Détaillé)

### 1️⃣ Client appelle l'API

```bash
POST /call HTTP/1.1
Content-Type: application/json

{
  "phoneNumber": "33612345678"
}
```

### 2️⃣ ia-phone-call place l'appel via Asterisk

```
[ia-phone-call] 📞 Placement d'un appel sortant
[ia-phone-call]    Numéro cible: 33612345678
[ia-phone-call]    Commande: channel originate PJSIP/kavkom-trunk/33612345678 extension outgoing-call@outgoing-call
```

### 3️⃣ Asterisk route l'appel vers Kavkom

```
[Asterisk Context] outgoing-call
  exten => s,1,Answer()
  same => n,AudioSocket(call-id-123, ia-phone-call:9701)
[Asterisk SIP] Appel placé vers: sip:33612345678@aria-madacom.kavkom.com (TLS:5061)
```

### 4️⃣ Kavkom répond et connecte le destinataire

```
[Kavkom] Appel sortant reçu de l'extension 501
[Kavkom] Routage vers: 33612345678
[RTP Audio] Connexion établie
```

### 5️⃣ Audio circule via AudioSocket

```
[ia-phone-call] ─→ Asterisk (PCM 8kHz)
[ia-phone-call] ←─ Asterisk (PCM 8kHz)
[ia-phone-call] ─→ Gemini Live API (PCM 16kHz)
[ia-phone-call] ←─ Gemini Live API (PCM 24kHz)
[ia-phone-call] ─→ Asterisk (PCM 8kHz)
[Asterisk] ─→ Kavkom ─→ Destinataire (RTP)
```

### 6️⃣ Appel terminé

```
[ia-phone-call] Fin de l'appel
[Asterisk] Fermeture du contexte outgoing-call
[Kavkom] Déconnexion du destinataire
```

---

## 📺 Surveiller un appel sortant

Ouvre **3 terminaux**:

### Terminal 1: Logs ia-phone-call

```bash
docker compose logs -f ia-phone-call
```

Chercher:
```
📞 [APPEL SORTANT] Placement d'un appel
   Numéro cible: 33612345678
🧠 [Gemini] ✅ Connecté
🎤 [Gemini] Envoi de 640 bytes
🎵 [Gemini] Reçu 2400 bytes
📊 [APPEL] Appel terminé
```

### Terminal 2: Logs Asterisk

```bash
docker compose logs -f ia-asterisk | grep -E "originate|channel|answered|AudioSocket"
```

### Terminal 3: Test

```bash
node src/test/outgoing-call-test.js 33612345678 20
```

---

## 🔧 Configuration avancée

### 1. Modifier le port API

Éditer `.env`:
```env
API_PORT=3001  # Au lieu de 3000 (défaut)
```

Redémarrer:
```bash
docker compose up -d --build
```

### 2. Changer le host Asterisk

Éditer `.env`:
```env
ASTERISK_HOST=asterisk.example.com  # Au lieu de ia-asterisk
```

### 3. Personnaliser le message d'accueil

Éditer `src/services/gemini.service.js`, ligne ~40:

```javascript
const openingPrompt = "Bonjour! Je suis l'assistant IA de Heroes CRM...";
```

---

## 🐛 Dépannage

### ❌ "Connection refused" (localhost:3000)

```
curl: (7) Failed to connect to localhost port 3000
```

**Solution:**
```bash
# 1. Vérifier les conteneurs
docker compose ps

# 2. Redémarrer
docker compose down
docker compose up -d --build

# 3. Attendre le démarrage
sleep 5

# 4. Tester la connexion
curl http://localhost:3000/status
```

### ❌ "Appel placé mais pas de son"

**Cause:** Gemini ne se connecte pas

**Solution:**
```bash
# Voir les logs Gemini
docker compose logs ia-phone-call | grep -i gemini

# Vérifier la clé API
echo $GEMINI_API_KEY
```

### ❌ "Erreur: docker: command not found"

**Cause:** Le script Node.js n'est pas dans un conteneur Docker

**Solution:**
```bash
# Option 1: Exécuter depuis le container (recommandé)
docker exec ia-phone-call node /app/src/test/outgoing-call-test.js 33612345678

# Option 2: Utiliser curl (plus simple)
curl -X POST http://localhost:3000/call \
  -H "Content-Type: application/json" \
  -d '{"phoneNumber":"33612345678"}'
```

---

## 📚 Références

- [Asterisk Originate Command](https://wiki.asterisk.org/wiki/display/AST/Channel+Originate)
- [AudioSocket Protocol](https://wiki.asterisk.org/wiki/display/AST/AudioSocket)
- [Gemini Live API](https://ai.google.dev/docs/api_key)
- [Kavkom SIP Configuration](https://kavkom.fr/)
