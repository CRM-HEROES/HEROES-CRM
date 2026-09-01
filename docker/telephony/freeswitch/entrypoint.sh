#!/usr/bin/env bash
set -euo pipefail

: "${KAVKOM_EXTENSION:?KAVKOM_EXTENSION is required}"
: "${KAVKOM_PASSWORD:?KAVKOM_PASSWORD is required}"
: "${KAVKOM_USER_CONTEXT:?KAVKOM_USER_CONTEXT is required}"
: "${ESL_PASSWORD:?ESL_PASSWORD is required}"
: "${EXTERNAL_IP:?EXTERNAL_IP is required}"
: "${LOCAL_TEST_SIP_PASSWORD:?LOCAL_TEST_SIP_PASSWORD is required}"

RTP_START_PORT="${RTP_START_PORT:-26384}"
RTP_END_PORT="${RTP_END_PORT:-26483}"
EXTERNAL_SIP_PORT="${EXTERNAL_SIP_PORT:-5080}"
EXTERNAL_TLS_PORT="${EXTERNAL_TLS_PORT:-5081}"
KAVKOM_SIP_TRANSPORT="${KAVKOM_SIP_TRANSPORT:-udp}"
KAVKOM_SIP_PORT="${KAVKOM_SIP_PORT:-5060}"

case "$KAVKOM_SIP_TRANSPORT" in
  udp|tcp|tls) ;;
  *) echo "KAVKOM_SIP_TRANSPORT must be udp, tcp, or tls." >&2; exit 1 ;;
esac

if [ "${KAVKOM_SIP_TRANSPORT}" = "tls" ]; then
  stunnel /etc/stunnel/stunnel.conf &
  KAVKOM_PROXY_HOST="${KAVKOM_USER_CONTEXT}"
  KAVKOM_PROXY_PORT="${KAVKOM_SIP_PORT}"
  KAVKOM_REGISTER_HOST="127.0.0.1"
  KAVKOM_REGISTER_PORT="15062"
  KAVKOM_REGISTER_TRANSPORT="tcp"
else
  KAVKOM_PROXY_HOST="${KAVKOM_USER_CONTEXT}"
  KAVKOM_PROXY_PORT="${KAVKOM_SIP_PORT}"
  KAVKOM_REGISTER_HOST="${KAVKOM_USER_CONTEXT}"
  KAVKOM_REGISTER_PORT="${KAVKOM_SIP_PORT}"
  KAVKOM_REGISTER_TRANSPORT="${KAVKOM_SIP_TRANSPORT}"
fi

cat > /etc/freeswitch/sip_profiles/external/kavkom.xml <<EOF
<include>
  <gateway name="kavkom">
    <param name="username" value="${KAVKOM_EXTENSION}"/>
    <param name="password" value="${KAVKOM_PASSWORD}"/>
    <param name="realm" value="${KAVKOM_USER_CONTEXT}"/>
    <param name="from-user" value="${KAVKOM_EXTENSION}"/>
    <param name="from-domain" value="${KAVKOM_USER_CONTEXT}"/>
    <param name="proxy" value="${KAVKOM_PROXY_HOST}:${KAVKOM_PROXY_PORT}"/>
    <param name="register-proxy" value="${KAVKOM_REGISTER_HOST}:${KAVKOM_REGISTER_PORT}"/>
    <param name="register-transport" value="${KAVKOM_REGISTER_TRANSPORT}"/>
    <param name="register" value="true"/>
    <param name="expire-seconds" value="600"/>
    <param name="retry-seconds" value="30"/>
    <param name="caller-id-in-from" value="true"/>
  </gateway>
</include>
EOF

EXT_SIP_IP="${EXT_SIP_IP:-$EXTERNAL_IP}"
EXT_RTP_IP="${EXT_RTP_IP:-$EXTERNAL_IP}"
sed -i "s#<param name=\"ext-rtp-ip\".*#<param name=\"ext-rtp-ip\" value=\"${EXT_RTP_IP}\"/>#" /etc/freeswitch/sip_profiles/external.xml
sed -i "s#<param name=\"ext-sip-ip\".*#<param name=\"ext-sip-ip\" value=\"${EXT_SIP_IP}\"/>#" /etc/freeswitch/sip_profiles/external.xml
sed -i "s#external_sip_port=[0-9]*#external_sip_port=${EXTERNAL_SIP_PORT}#" /etc/freeswitch/vars.xml
sed -i "s#external_tls_port=[0-9]*#external_tls_port=${EXTERNAL_TLS_PORT}#" /etc/freeswitch/vars.xml
if [ "$KAVKOM_SIP_TRANSPORT" = "tls" ]; then
  sed -i 's#external_ssl_enable=[^\"]*#external_ssl_enable=true#' /etc/freeswitch/vars.xml
fi
sed -i "s#default_password=[^\"]*#default_password=${LOCAL_TEST_SIP_PASSWORD}#" /etc/freeswitch/vars.xml
sed -i "s#<param name=\"rtp-start-port\" value=\"[0-9]*\"/>#<param name=\"rtp-start-port\" value=\"${RTP_START_PORT}\"/>#" /etc/freeswitch/autoload_configs/switch.conf.xml
sed -i "s#<param name=\"rtp-end-port\" value=\"[0-9]*\"/>#<param name=\"rtp-end-port\" value=\"${RTP_END_PORT}\"/>#" /etc/freeswitch/autoload_configs/switch.conf.xml
sed -i "s#<param name=\"password\" value=\"[^\"]*\"/>#<param name=\"password\" value=\"${ESL_PASSWORD}\"/>#" /etc/freeswitch/autoload_configs/event_socket.conf.xml
# ESL n'est pas publié sur l'hôte : il est accessible uniquement par
# ai-phone-agent à travers le réseau Docker privé.
sed -i "s#<param name=\"listen-ip\" value=\"[^\"]*\"/>#<param name=\"listen-ip\" value=\"0.0.0.0\"/>#" /etc/freeswitch/autoload_configs/event_socket.conf.xml
# The default ESL ACL accepts loopback only.  The ESL port is deliberately
# not published to the host; allow IPv4 peers on this private Compose network
# so ai-phone-agent can control FreeSWITCH.
sed -i 's#<!--[[:space:]]*<param name="apply-inbound-acl" value="loopback.auto"/>[[:space:]]*-->#<param name="apply-inbound-acl" value="any_v4.auto"/>#' /etc/freeswitch/autoload_configs/event_socket.conf.xml

DIALPLAN=/etc/freeswitch/dialplan/default.xml
if ! grep -q "ai-agent-tap" "$DIALPLAN"; then
  sed -i '/<context name="default">/a\
    <extension name="ai-agent-tap">\
      <condition field="destination_number" expression="^ai-agent-tap$">\
        <action application="answer"/>\
        <action application="park"/>\
      </condition>\
    </extension>' "$DIALPLAN"
fi

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
fi

exec "$@"
