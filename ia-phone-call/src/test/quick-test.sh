#!/bin/bash
# 🚀 Script de démarrage rapide pour tester la chaîne Kavkom → Asterisk → Gemini

set -e

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║  🧪 TEST DE LA CHAÎNE KAVKOM → ASTERISK → GEMINI            ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# Étape 1: Vérifier que Docker Compose est en cours d'exécution
echo "📋 Étape 1: Vérification des conteneurs..."
if ! docker compose ps | grep -q "ia-asterisk.*Healthy"; then
    echo "❌ Asterisk n'est pas en cours d'exécution"
    echo "   Lancement: docker compose up -d"
    cd "$(dirname "${BASH_SOURCE[0]}")/../.."
    docker compose up -d
    echo "⏳ Attente du démarrage d'Asterisk (20s)..."
    sleep 20
fi

if ! docker compose ps | grep -q "ia-phone-call.*Started"; then
    echo "❌ ia-phone-call n'est pas en cours d'exécution"
    echo "   Relancement..."
    docker compose up -d ia-phone-call
    sleep 10
fi

echo "✅ Les conteneurs sont en cours d'exécution"
echo ""

# Étape 2: Afficher les informations de connexion
echo "📍 Étape 2: Informations de connexion"
echo "   - Asterisk:     Container ia-asterisk:5061 (TLS)"
echo "   - AudioSocket:  Container ia-phone-call:9701 (TCP)"
echo "   - Gemini:       WebSocket (généré dynamiquement)"
echo ""

# Étape 3: Lancer le test client
echo "🎙️  Étape 3: Lancement du client AudioSocket de test..."
echo "   Durée: 10 secondes"
echo "   Format: PCM 16-bit 8kHz (bruit blanc)"
echo ""

# Ouvrir 3 terminaux pour les logs (ou afficher les logs en direct)
echo "📺 LOGS OUVERT"
echo "   Terminal 1 (ia-phone-call):"
docker compose logs -f ia-phone-call 2>&1 &
LOG_PID1=$!
sleep 1

echo ""
echo "   Terminal 2 (ia-asterisk):"
docker compose logs -f ia-asterisk 2>&1 | grep -E "APPEL|Gemini|AudioSocket|erreur|ERROR" &
LOG_PID2=$!
sleep 1

echo ""
echo "   🎙️  LANCEMENT DU TEST..."
echo ""

# Lancer le test clients
node "$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/audio-socket-client.js" localhost 9701 10

echo ""
echo "✅ Test terminé!"
echo ""
echo "📊 Résultats attendus:"
echo "   ✓ Client connecté à localhost:9701"
echo "   ✓ Nouveul appel entrant depuis Asterisk"
echo "   ✓ Gemini s'initialise et répond"
echo "   ✓ Audio circule dans les deux directions"
echo ""

# Arrêter les logs
kill $LOG_PID1 $LOG_PID2 2>/dev/null || true

echo "ℹ️  Pour voir les logs complets:"
echo "   docker compose logs ia-phone-call"
echo "   docker compose logs ia-asterisk"
echo ""
