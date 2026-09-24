#!/bin/bash
# Script de test d'appel entrant depuis Asterisk CLI
# Simule un appel entrant depuis Kavkom vers l'extension de l'IA

set -e

ASTERISK_HOST="${1:-ia-asterisk}"
FROM_NUMBER="${2:-33123456789}"
DURATION="${3:-10}"

echo "📞 Test d'appel entrant Kavkom → Asterisk → ia-phone-call"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Serveur: $ASTERISK_HOST"
echo "Numéro émetteur: $FROM_NUMBER"
echo "Durée: ${DURATION}s"
echo ""

# Préparation: Lancer le test client AudioSocket en arrière-plan
echo "🎙️  Démarrage du client AudioSocket de test..."
node "$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/audio-socket-client.js" localhost 9701 "$DURATION" &
AUDIO_PID=$!
sleep 2

echo ""
echo "📡 Placement d'un appel de test via Asterisk CLI..."
echo ""

# Utiliser originate pour placer l'appel via AudioSocket
# Format: originate <channel> <data1> [data2] [data3]
docker exec ia-asterisk asterisk -rx "
channel originate PJSIP/$FROM_NUMBER@kavkom-trunk extension from-kavkom@from-kavkom
" || true

echo ""
echo "⏳ Appel en cours... (durée: ${DURATION}s)"
sleep "$DURATION"

wait $AUDIO_PID 2>/dev/null || true

echo ""
echo "✅ Test terminé"
echo ""
echo "📊 Vérification du statut Asterisk:"
docker exec ia-asterisk asterisk -rx "
core show channels
" || true
