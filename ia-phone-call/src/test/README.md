# 🧪 Tests d'appels Kavkom → Asterisk → Gemini

Ce dossier contient des scripts pour tester la chaîne complète d'appels téléphoniques.

## Architecture testée

```
KAVKOM (SIP)
  ↓ Appel entrant
ASTERISK (PBX)
  ↓ AudioSocket TCP:9701
ia-phone-call (Node.js)
  ↓ WebSocket
Gemini Live API
```

## 📋 Scripts disponibles

### 1. Client AudioSocket simple (test de base)

```bash
cd /path/to/ia-phone-call
node src/test/audio-socket-client.js [host] [port] [duration]
```

**Paramètres:**
- `host` (défaut: `localhost`) - Serveur à tester
- `port` (défaut: `9701`) - Port AudioSocket
- `duration` (défaut: `5`) - Durée en secondes

**Exemple: Tester la connexion locale**
```bash
node src/test/audio-socket-client.js localhost 9701 10
```

**Résultat attendu:**
```
🎙️  Client AudioSocket - Connexion à localhost:9701
✅ Connecté au serveur AudioSocket
⏳ Transmission: 100.0%
🎵 [Gemini] Reçu 2400 bytes audio
🔌 Connexion fermée par le serveur
```

---

### 2. Test d'appel via Asterisk CLI

```bash
cd /path/to/ia-phone-call
bash src/test/test-call.sh [asterisk_host] [from_number] [duration]
```

**Paramètres:**
- `asterisk_host` (défaut: `ia-asterisk`) - Serveur Asterisk
- `from_number` (défaut: `33123456789`) - Numéro émetteur simulé
- `duration` (défaut: `10`) - Durée en secondes

**Exemple: Appel de test de 15s depuis le container**
```bash
bash src/test/test-call.sh ia-asterisk 33612345678 15
```

---

## 🔍 Logs à surveiller

### Via Docker Compose

```bash
# Terminal 1: Logs Asterisk
docker compose logs -f ia-asterisk

# Terminal 2: Logs ia-phone-call
docker compose logs -f ia-phone-call
```

### Flux d'appel attendu

**1. Client AudioSocket se connecte:**
```
✅ Connecté au serveur AudioSocket
📞 [APPEL] Nouvel appel entrant depuis Asterisk (AudioSocket)
```

**2. Gemini s'initialise:**
```
🧠 [Gemini] Initialisation de la session...
🤖 [Gemini] ✅ Connecté au serveur Gemini Live API
```

**3. Audio circule:**
```
🎤 [Asterisk] Paquet #1 (320 bytes) - Total: 320 bytes
🎤 [Gemini] Envoi de 640 bytes audio (Total: 640 bytes)
🎵 [Gemini] Reçu 2400 bytes audio
```

**4. Appel se termine:**
```
📊 [APPEL] Appel terminé
   Durée: 15.23s
   Paquets reçus: 120
   Bytes total: 38400
   Débit moyen: 2.5 KB/s
```

---

## 🐛 Dépannage

### "Connection refused" (ECONNREFUSED)

```
❌ Erreur: connect ECONNREFUSED 127.0.0.1:9701
```

**Cause:** Le serveur ia-phone-call n'est pas en cours d'exécution.

**Solution:**
```bash
docker compose up -d ia-phone-call ia-asterisk
docker compose logs ia-phone-call
```

### "Setup timeout" (Gemini ne répond pas)

```
❌ Timeout après 30s
```

**Cause:** Gemini n'a pas pu se connecter (problème API ou réseau).

**Solution:**
1. Vérifier la clé API: `echo $GEMINI_API_KEY`
2. Vérifier la connectivité réseau
3. Vérifier les logs Gemini: `docker compose logs ia-phone-call | grep "Gemini"`

### Audio ne circule pas correctement

**Symptôme:** Les logs montrent "Paquet #1" puis plus rien.

**Cause:** Configuration AudioSocket incorrecte.

**Solution:**
1. Vérifier [extensions.conf](../asterisk/extensions.conf)
2. Vérifier le port [server.js](../src/server.js)
3. Vérifier le réseau Docker: `docker network ls`

---

## 📊 Métriques de performance

Le débit attendu pour un appel normal:

| Métrique | Valeur | Explication |
|----------|--------|-------------|
| **Paquets/sec** | ~25 | 320 bytes tous les 40ms |
| **Débit Asterisk→Gemini** | ~6.4 KB/s | 8kHz 16-bit = 16 KB/s, compressé |
| **Latence** | <200ms | Gemini Live API garantit <500ms |
| **Durée appel** | Illimitée | Tant que la WebSocket reste ouverte |

---

## 🔧 Configuration pour tests avancés

### Modifier le format audio

**Client AudioSocket:**
- Voir ligne 40: Génération bruit blanc → remplacer par fichier WAV

**Gemini:**
- Voir `.env` pour la voix: `GEMINI_VOICE_NAME=Puck`

### Enregistrer les appels

```bash
# Voir docker-compose.yml, volume calls_data
ls -la data/calls/
```

---

## 📚 Références

- [AudioSocket Protocol](https://wiki.asterisk.org/wiki/display/AST/AudioSocket)
- [Gemini Live API Documentation](https://ai.google.dev/docs/api_key)
- [Docker Compose Networking](https://docs.docker.com/compose/networking/)
