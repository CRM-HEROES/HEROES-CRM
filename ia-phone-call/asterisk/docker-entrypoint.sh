#!/bin/sh
set -e

# Créer le répertoire des certificats
mkdir -p /etc/asterisk/keys

# Générer un certificat auto-signé s'il n'existe pas
if [ ! -f /etc/asterisk/keys/asterisk.pem ]; then
  openssl req -new -x509 -days 365 -nodes \
    -out /etc/asterisk/keys/asterisk.pem \
    -keyout /etc/asterisk/keys/asterisk.pem \
    -subj "/CN=asterisk"
fi

# Créer une CA vide pour éviter les erreurs de validation
touch /etc/asterisk/keys/ca.pem

echo "Génération de pjsip.conf depuis les variables d'environnement..."

# Variables requises pour les transports. Les identifiants SIP
# extension/password ne sont plus obligatoires ici : ia-phone-call génère un
# endpoint dédié par agent IA dans /etc/asterisk/dynamic/ai-agents.conf.
: "${KAVKOM_EXTERNAL_ADDRESS:?Variable KAVKOM_EXTERNAL_ADDRESS non définie dans .env}"
: "${KAVKOM_SIP_TRANSPORT:=udp}"
: "${KAVKOM_SIP_PORT:=5060}"

# User-Agent : le SBC de Kavkom ignore silencieusement toute requête SIP dont
# le User-Agent contient "Asterisk" (d'où un enregistrement en "Rejected").
# Une valeur neutre est donc imposée par défaut, surchargeable via .env.
: "${KAVKOM_USER_AGENT:=HeroesCRM-Phone/1.0}"
# envsubst (processus séparé) ne voit que les variables exportées
export KAVKOM_USER_AGENT KAVKOM_EXTERNAL_ADDRESS

mkdir -p /etc/asterisk/dynamic
touch /etc/asterisk/dynamic/ai-agents.conf

# Substitution des variables dans le template -> pjsip.conf final
envsubst '${KAVKOM_USER_AGENT} ${KAVKOM_EXTERNAL_ADDRESS}' \
  < /etc/asterisk/pjsip.conf.template \
  > /etc/asterisk/pjsip.conf

# Compatibilité avec l'ancienne configuration à téléphone unique. Si ces
# variables sont présentes, on conserve un endpoint kavkom-trunk utilisable par
# les appels manuels qui ne fournissent pas de config agent IA.
if [ -n "${KAVKOM_EXTENSION:-}" ] && [ -n "${KAVKOM_PASSWORD:-}" ] && [ -n "${KAVKOM_USER_CONTEXT:-}" ]; then
  cat >> /etc/asterisk/pjsip.conf <<EOF

; --- Fallback historique optionnel: trunk Kavkom unique depuis .env ---
[kavkom-reg]
type=registration
outbound_auth=kavkom-auth
transport=transport-${KAVKOM_SIP_TRANSPORT}
server_uri=sip:${KAVKOM_USER_CONTEXT}:${KAVKOM_SIP_PORT}
client_uri=sip:${KAVKOM_EXTENSION}@${KAVKOM_USER_CONTEXT}
retry_interval=60
expiration=3600
user_agent=${KAVKOM_USER_AGENT}
contact_user=${KAVKOM_EXTENSION}
auth_rejection_permanent=no

[kavkom-auth]
type=auth
auth_type=userpass
username=${KAVKOM_EXTENSION}
password=${KAVKOM_PASSWORD}
realm=${KAVKOM_USER_CONTEXT}

[kavkom-trunk]
type=endpoint
context=from-kavkom
transport=transport-${KAVKOM_SIP_TRANSPORT}
disallow=all
allow=alaw,ulaw
outbound_auth=kavkom-auth
aors=kavkom-aor
direct_media=no
from_user=${KAVKOM_EXTENSION}
from_domain=${KAVKOM_USER_CONTEXT}

[kavkom-aor]
type=aor
contact=sip:${KAVKOM_USER_CONTEXT}:${KAVKOM_SIP_PORT}

[kavkom-identify]
type=identify
endpoint=kavkom-trunk
match=${KAVKOM_USER_CONTEXT}
EOF
fi

echo "pjsip.conf généré avec succès :"
grep -E "server_uri|client_uri|username|transport=|#include" /etc/asterisk/pjsip.conf | head -20

# Utiliser les CA système pour vérifier les certificats TLS
cp /etc/ssl/certs/ca-certificates.crt /etc/asterisk/keys/ca.pem

echo "Démarrage d'Asterisk..."
# Démarrer Asterisk directement avec les options verboses
exec /usr/sbin/asterisk -vvvdddf
