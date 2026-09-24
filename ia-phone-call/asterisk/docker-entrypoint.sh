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

# Variables requises — arrêt explicite si manquantes
: "${KAVKOM_EXTENSION:?Variable KAVKOM_EXTENSION non définie dans .env}"
: "${KAVKOM_PASSWORD:?Variable KAVKOM_PASSWORD non définie dans .env}"
: "${KAVKOM_USER_CONTEXT:?Variable KAVKOM_USER_CONTEXT non définie dans .env}"
: "${KAVKOM_SIP_TRANSPORT:?Variable KAVKOM_SIP_TRANSPORT non définie dans .env}"
: "${KAVKOM_SIP_PORT:?Variable KAVKOM_SIP_PORT non définie dans .env}"

# User-Agent : le SBC de Kavkom ignore silencieusement toute requête SIP dont
# le User-Agent contient "Asterisk" (d'où un enregistrement en "Rejected").
# Une valeur neutre est donc imposée par défaut, surchargeable via .env.
: "${KAVKOM_USER_AGENT:=HeroesCRM-Phone/1.0}"
# envsubst (processus séparé) ne voit que les variables exportées
export KAVKOM_USER_AGENT

# Substitution des variables dans le template -> pjsip.conf final
envsubst '${KAVKOM_EXTENSION} ${KAVKOM_PASSWORD} ${KAVKOM_USER_CONTEXT} ${KAVKOM_SIP_TRANSPORT} ${KAVKOM_SIP_PORT} ${KAVKOM_USER_AGENT}' \
  < /etc/asterisk/pjsip.conf.template \
  > /etc/asterisk/pjsip.conf

echo "pjsip.conf généré avec succès :"
grep -E "server_uri|client_uri|username|transport=" /etc/asterisk/pjsip.conf | head -10

# Utiliser les CA système pour vérifier les certificats TLS
cp /etc/ssl/certs/ca-certificates.crt /etc/asterisk/keys/ca.pem

echo "Démarrage d'Asterisk..."
# Démarrer Asterisk directement avec les options verboses
exec /usr/sbin/asterisk -vvvdddf

