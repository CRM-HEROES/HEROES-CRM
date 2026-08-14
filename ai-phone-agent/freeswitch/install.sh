#!/usr/bin/env bash
#
# Installe et configure FreeSWITCH + mod_audio_stream pour ai-phone-agent,
# en suivant exactement les étapes de
# ai-phone-agent/GUIDE_INSTALLATION_FREESWITCH.md.
#
# À lancer sur le serveur Ubuntu/Debian cible (pas sur ton PC), avec sudo :
#   sudo bash install.sh
#
# Le script demande les valeurs sensibles de façon interactive (rien n'est
# écrit en clair dans ce fichier ni dans l'historique du terminal).
# Il est conçu pour être relancé sans risque si une étape échoue
# (chaque partie vérifie si elle a déjà été faite avant d'agir).

set -euo pipefail

if [ "$(id -u)" -ne 0 ]; then
    echo "Ce script doit être lancé avec sudo : sudo bash install.sh"
    exit 1
fi

echo "=== Installation FreeSWITCH pour ai-phone-agent ==="
echo

# ---------------------------------------------------------------------------
# Valeurs demandées interactivement
# ---------------------------------------------------------------------------
read -rp "Token SignalWire (Personal Access Token) : " SIGNALWIRE_TOKEN
read -rp "Numéro d'extension Kavkom dédiée à l'IA (ex: 1000) : " KAVKOM_EXTENSION
read -rsp "Mot de passe SIP de cette extension : " KAVKOM_PASSWORD
echo
read -rp "Domaine SIP Kavkom (ex: aria-madacom.kavkom.com) : " KAVKOM_USER_CONTEXT
read -rsp "Choisis un mot de passe fort pour l'Event Socket (ESL) : " ESL_PASSWORD
echo
echo

# ===========================================================================
# Étape 1 — Installer FreeSWITCH
# ===========================================================================
echo "--- Étape 1/9 : installation de FreeSWITCH ---"

if ! command -v fs_cli >/dev/null 2>&1; then
    apt update
    apt install -y gnupg2 wget lsb-release software-properties-common apt-transport-https curl

    curl --http1.1 -fsSL https://freeswitch.signalwire.com/repo/deb/debian-release/signalwire-freeswitch-repo.gpg \
        | gpg --dearmor -o /usr/share/keyrings/signalwire-freeswitch-repo.gpg

    echo "machine freeswitch.signalwire.com login signalwire password $SIGNALWIRE_TOKEN" \
        > /etc/apt/auth.conf.d/freeswitch.conf
    chmod 600 /etc/apt/auth.conf.d/freeswitch.conf

    echo "deb [signed-by=/usr/share/keyrings/signalwire-freeswitch-repo.gpg] https://freeswitch.signalwire.com/repo/deb/debian-release/ $(lsb_release -cs) main" \
        > /etc/apt/sources.list.d/freeswitch.list

    apt update
    apt install -y freeswitch-meta-all
    echo "FreeSWITCH installé."
else
    echo "FreeSWITCH est déjà installé, on continue."
fi

systemctl enable freeswitch >/dev/null 2>&1 || true
systemctl start freeswitch

# ===========================================================================
# Étape 2 — Installer mod_audio_stream
# ===========================================================================
echo
echo "--- Étape 2/9 : installation de mod_audio_stream ---"

MOD_PATH=$(find /usr/lib*/freeswitch/mod -name "mod_audio_stream.so" 2>/dev/null | head -n1 || true)

if [ -z "$MOD_PATH" ]; then
    apt-get -y install git libfreeswitch-dev libssl-dev zlib1g-dev libevent-dev libspeexdsp-dev cmake build-essential

    BUILD_DIR=$(mktemp -d)
    git clone https://github.com/amigniter/mod_audio_stream.git "$BUILD_DIR/mod_audio_stream"
    cd "$BUILD_DIR/mod_audio_stream"
    git submodule init && git submodule update
    mkdir build && cd build
    cmake -DCMAKE_BUILD_TYPE=Release ..
    make
    make install
    cd /
    rm -rf "$BUILD_DIR"
    echo "mod_audio_stream compilé et installé."
else
    echo "mod_audio_stream est déjà installé, on continue."
fi

MODULES_CONF=/etc/freeswitch/autoload_configs/modules.conf.xml
if ! grep -q "mod_audio_stream" "$MODULES_CONF"; then
    sed -i 's#<modules>#<modules>\n    <load module="mod_audio_stream"/>#' "$MODULES_CONF"
    echo "mod_audio_stream activé dans modules.conf.xml."
else
    echo "mod_audio_stream déjà activé dans modules.conf.xml."
fi

echo "Vérifie manuellement que ces deux lignes existent bien et ne sont PAS en commentaire dans $MODULES_CONF :"
echo '    <load module="mod_loopback"/>'
echo '    <load module="mod_conference"/>'
grep -n "mod_loopback\|mod_conference" "$MODULES_CONF" || echo "  (aucune des deux trouvée — à ajouter manuellement si besoin)"

# ===========================================================================
# Étape 3 — Gateway vers Kavkom
# ===========================================================================
echo
echo "--- Étape 3/9 : configuration du gateway Kavkom ---"

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
echo "Gateway Kavkom écrit dans /etc/freeswitch/sip_profiles/external/kavkom.xml."

# ===========================================================================
# Étape 4 — Dialplan (tap IA)
# ===========================================================================
echo
echo "--- Étape 4/9 : dialplan du tap IA ---"

DIALPLAN=/etc/freeswitch/dialplan/default.xml
if ! grep -q "ai-agent-tap" "$DIALPLAN"; then
    sed -i '/<context name="default">/a\
    <extension name="ai-agent-tap">\
        <condition field="destination_number" expression="^ai-agent-tap$">\
            <action application="answer"/>\
            <action application="park"/>\
        </condition>\
    </extension>' "$DIALPLAN"
    echo "Extension ai-agent-tap ajoutée au dialplan."
else
    echo "Extension ai-agent-tap déjà présente dans le dialplan."
fi

# ===========================================================================
# Étape 5 — Profil de conférence
# ===========================================================================
echo
echo "--- Étape 5/9 : profil de conférence ---"

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
    echo "Profil de conférence ai-agent ajouté."
else
    echo "Profil de conférence ai-agent déjà présent."
fi

# ===========================================================================
# Étape 6 — Sécuriser l'Event Socket
# ===========================================================================
echo
echo "--- Étape 6/9 : mot de passe Event Socket ---"

ESL_CONF=/etc/freeswitch/autoload_configs/event_socket.conf.xml
sed -i "s#<param name=\"password\" value=\"[^\"]*\"/>#<param name=\"password\" value=\"${ESL_PASSWORD}\"/>#" "$ESL_CONF"
echo "Mot de passe Event Socket mis à jour."

# ===========================================================================
# Étape 7 — Recharger FreeSWITCH
# ===========================================================================
echo
echo "--- Étape 7/9 : redémarrage de FreeSWITCH ---"
systemctl restart freeswitch
sleep 5

# ===========================================================================
# Étape 8 — Vérification
# ===========================================================================
echo
echo "--- Étape 8/9 : vérification ---"
sleep 3
echo "Statut du module mod_audio_stream :"
fs_cli -x "module_exists mod_audio_stream" || true
echo "Statut du gateway Kavkom (doit afficher REGED après quelques secondes, relance la commande si besoin) :"
fs_cli -x "sofia status gateway kavkom" || true

# ===========================================================================
# Étape 9 — Rappel pour la suite
# ===========================================================================
echo
echo "--- Étape 9/9 : ce qu'il reste à faire ---"
cat <<'EOF'
FreeSWITCH est installé et configuré. Il reste, en dehors de ce script :

1. Déployer le service ai-phone-agent/ sur ce serveur :
     git clone <url_du_repo> && cd HEROES-CRM/ai-phone-agent
     npm install
     cp .env.example .env
     nano .env   # renseigner GEMINI_API_KEY, AI_PHONE_AGENT_SHARED_SECRET,
                 # LARAVEL_BASE_URL, FS_ESL_PASSWORD (celui saisi plus haut),
                 # WS_PUBLIC_URL
     npm install -g pm2
     pm2 start index.js --name ai-phone-agent
     pm2 save && pm2 startup

2. Attribuer un numéro de sortie (DID) à l'extension Kavkom dans le
   dashboard Kavkom.

3. Ajouter AI_PHONE_AGENT_SHARED_SECRET et AI_PHONE_AGENT_BRIDGE_URL dans
   le .env de production du CRM Laravel.

Détails complets : ai-phone-agent/GUIDE_INSTALLATION_FREESWITCH.md
EOF
