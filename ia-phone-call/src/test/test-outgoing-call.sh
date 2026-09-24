#!/bin/bash
# 🚀 Test d'appel sortant: IA → Asterisk → Kavkom

set -e

HOST="${1:-localhost}"
PORT="${2:-3000}"
PHONE="${3:-33612345678}"
DURATION="${4:-15}"

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║  📞 TEST D'APPEL SORTANT: IA → ASTERISK → KAVKOM            ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""
echo "Configuration:"
echo "   Serveur API: $HOST:$PORT"
echo "   Numéro cible: $PHONE"
echo "   Durée: ${DURATION}s"
echo ""

# Étape 1: Vérifier la connexion à l'API
echo "📋 Étape 1: Vérification de l'API..."
if ! curl -s http://$HOST:$PORT/status > /dev/null; then
    echo "❌ Impossible de se connecter à http://$HOST:$PORT"
    echo "   Vérifiez que le serveur est en cours d'exécution:"
    echo "   docker compose logs ia-phone-call"
    exit 1
fi
echo "✅ Connexion à l'API établie"
echo ""

# Étape 2: Afficher la documentation de l'API
echo "📖 Étape 2: Documentation de l'API"
echo "Endpoints disponibles:"
curl -s http://$HOST:$PORT/ | jq '.endpoints'
echo ""

# Étape 3: Placer l'appel sortant
echo "📞 Étape 3: Placement de l'appel sortant..."
echo "Requête: POST /call"
echo "Body: {\"phoneNumber\": \"$PHONE\"}"
echo ""

RESPONSE=$(curl -s -X POST http://$HOST:$PORT/call \
  -H "Content-Type: application/json" \
  -d "{\"phoneNumber\": \"$PHONE\"}")

echo "Réponse:"
echo $RESPONSE | jq '.'
echo ""

CALL_ID=$(echo $RESPONSE | jq -r '.callId // empty')
if [ -z "$CALL_ID" ]; then
    echo "❌ Erreur: L'appel n'a pas pu être placé"
    exit 1
fi

echo "✅ Appel placé avec succès (ID: $CALL_ID)"
echo ""

# Étape 4: Ouvrir les logs en direct
echo "📺 Étape 4: Surveillance en direct..."
echo "   Terminal 1 (ia-phone-call):"
docker compose logs -f ia-phone-call 2>&1 &
LOG_PID1=$!

echo "   Terminal 2 (ia-asterisk):"  
docker compose logs -f ia-asterisk 2>&1 | grep -E "APPEL|Gemini|AudioSocket|channel|originate|ERROR" &
LOG_PID2=$!

sleep 1

# Étape 5: Attendre la durée de l'appel
echo ""
echo "⏳ Appel en cours... Durée: $DURATION secondes"
sleep $DURATION

# Étape 6: Arrêter les logs
echo ""
echo "🛑 Arrêt de la surveillance"
kill $LOG_PID1 $LOG_PID2 2>/dev/null || true

# Étape 7: Vérifier les canaux actifs
echo ""
echo "📊 Étape 7: État final des canaux"
curl -s http://$HOST:$PORT/channels | jq '.channels'
echo ""

echo "✅ Test d'appel sortant terminé!"
