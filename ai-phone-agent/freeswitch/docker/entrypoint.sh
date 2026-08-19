#!/usr/bin/env bash
#
# Injecte la configuration Kavkom/ai-phone-agent dans FreeSWITCH à chaque
# démarrage du conteneur, à partir de variables d'environnement (rien n'est
# codé en dur dans l'image). Ré-exécutable sans risque : chaque étape est
# idempotente, comme dans install.sh.

set -euo pipefail

: "${KAVKOM_EXTENSION:?Variable KAVKOM_EXTENSION manquante (extension Kavkom dediee a l-IA)}"
: "${KAVKOM_PASSWORD:?Variable KAVKOM_PASSWORD manquante}"
: "${KAVKOM_USER_CONTEXT:?Variable KAVKOM_USER_CONTEXT manquante (domaine SIP Kavkom)}"
: "${ESL_PASSWORD:?Variable ESL_PASSWORD manquante}"
: "${EXTERNAL_IP:?Variable EXTERNAL_IP manquante, IP publique du serveur Docker, indispensable pour que Kavkom puisse repondre}"

# ---------------------------------------------------------------------------
# Gateway Kavkom
# ---------------------------------------------------------------------------
cat > /etc/freeswitch/sip_profiles/external/kavkom.xml <<EOF
<include>
    <gateway name="kavkom">
        <param name="username" value="${KAVKOM_EXTENSION}"/>
        <param name="password" value="${KAVKOM_PASSWORD}"/>
        <param name="realm" value="${KAVKOM_USER_CONTEXT}"/>
        <param name="proxy" value="${KAVKOM_USER_CONTEXT}"/>
        <param name="register" value="true"/>
        <param name="expire-seconds" value="600"/>
        <param name="retry-seconds" value="30"/>
        <param name="caller-id-in-from" value="true"/>
    </gateway>
</include>
EOF
echo "[entrypoint] Gateway kavkom configuré."

# ---------------------------------------------------------------------------
# IP publique — sans ça, Kavkom reçoit l'IP interne du conteneur et ne peut
# jamais répondre (c'est la cause du blocage NAT expliqué précédemment).
# ---------------------------------------------------------------------------
EXTERNAL_PROFILE=/etc/freeswitch/sip_profiles/external.xml
sed -i "s#<param name=\"ext-rtp-ip\".*#<param name=\"ext-rtp-ip\" value=\"${EXTERNAL_IP}\"/>#" "$EXTERNAL_PROFILE"
sed -i "s#<param name=\"ext-sip-ip\".*#<param name=\"ext-sip-ip\" value=\"${EXTERNAL_IP}\"/>#" "$EXTERNAL_PROFILE"
echo "[entrypoint] IP externe réglée sur ${EXTERNAL_IP}."

# ---------------------------------------------------------------------------
# Dialplan (tap IA)
# ---------------------------------------------------------------------------
DIALPLAN=/etc/freeswitch/dialplan/default.xml
if ! grep -q "ai-agent-tap" "$DIALPLAN"; then
    sed -i '/<context name="default">/a\
    <extension name="ai-agent-tap">\
        <condition field="destination_number" expression="^ai-agent-tap$">\
            <action application="answer"/>\
            <action application="park"/>\
        </condition>\
    </extension>' "$DIALPLAN"
    echo "[entrypoint] Extension ai-agent-tap ajoutée au dialplan."
fi

# ---------------------------------------------------------------------------
# Profil de conférence
# ---------------------------------------------------------------------------
CONF_FILE=/etc/freeswitch/autoload_configs/conference.conf.xml
if ! grep -q 'profile name="ai-agent"' "$CONF_FILE"; then
    sed -i '/<profiles>/a\
    <profile name="ai-agent">\
        <param name="rate" value="16000"/>\
        <param name="domain" value="$${domain}"/>\
        <param name="caller-id-name" value="Heroes CRM"/>\
        <param name="caller-controls" value="none"/>\
        <param name="moderator-controls" value="none"/>\
    </profile>' "$CONF_FILE"
    echo "[entrypoint] Profil de conférence ai-agent ajouté."
fi

# ---------------------------------------------------------------------------
# Event Socket (ESL) — utilisé par ai-phone-agent pour piloter les appels
# ---------------------------------------------------------------------------
sed -i "s#<param name=\"password\" value=\"[^\"]*\"/>#<param name=\"password\" value=\"${ESL_PASSWORD}\"/>#" \
    /etc/freeswitch/autoload_configs/event_socket.conf.xml
echo "[entrypoint] Mot de passe Event Socket configuré."

exec "$@"
