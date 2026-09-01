# Image FreeSWITCH + mod_audio_stream pour ai-phone-agent, construite exactement
# comme ai-phone-agent/freeswitch/install.sh (même dépôt SignalWire, même module),
# mais empaquetée pour Docker. La configuration spécifique à Kavkom est injectée
# au démarrage du conteneur par entrypoint.sh, pas construite dans l'image.
#
# Nécessite BuildKit (export DOCKER_BUILDKIT=1) pour passer le token SignalWire
# via --secret, afin qu'il ne soit jamais écrit dans une couche de l'image.

FROM debian:bookworm-slim

RUN apt-get update && apt-get install -y --no-install-recommends \
        gnupg2 wget lsb-release software-properties-common apt-transport-https curl \
        ca-certificates git cmake pkg-config build-essential libssl-dev zlib1g-dev libevent-dev libspeexdsp-dev \
    && rm -rf /var/lib/apt/lists/*

# Dépôt FreeSWITCH (SignalWire), authentifié avec le token fourni au build
RUN --mount=type=secret,id=signalwire_token \
    curl --http1.1 -fsSL https://freeswitch.signalwire.com/repo/deb/debian-release/signalwire-freeswitch-repo.gpg \
        | gpg --dearmor -o /usr/share/keyrings/signalwire-freeswitch-repo.gpg \
    && echo "machine freeswitch.signalwire.com login signalwire password $(cat /run/secrets/signalwire_token)" \
        > /etc/apt/auth.conf.d/freeswitch.conf \
    && chmod 600 /etc/apt/auth.conf.d/freeswitch.conf \
    && echo "deb [signed-by=/usr/share/keyrings/signalwire-freeswitch-repo.gpg] https://freeswitch.signalwire.com/repo/deb/debian-release/ $(lsb_release -cs) main" \
        > /etc/apt/sources.list.d/freeswitch.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends freeswitch-meta-all libfreeswitch-dev \
    && rm -rf /var/lib/apt/lists/* /etc/apt/auth.conf.d/freeswitch.conf

# mod_audio_stream (même procédure que install.sh étape 2)
RUN git clone https://github.com/amigniter/mod_audio_stream.git /tmp/mod_audio_stream \
    && cd /tmp/mod_audio_stream && git submodule init && git submodule update \
    && mkdir build && cd build \
    && cmake -DCMAKE_BUILD_TYPE=Release .. \
    && make && make install \
    && rm -rf /tmp/mod_audio_stream \
    && sed -i 's#<modules>#<modules>\n    <load module="mod_audio_stream"/>#' \
        /etc/freeswitch/autoload_configs/modules.conf.xml


# --- À insérer dans le Dockerfile FreeSWITCH existant, avant ENTRYPOINT ---
# (juste après l'installation des paquets système habituels convient)

RUN apt-get update \
    && apt-get install -y --no-install-recommends stunnel4 \
    && rm -rf /var/lib/apt/lists/*

COPY stunnel/stunnel.conf /etc/stunnel/stunnel.conf

# --- Fin de l'ajout ---


COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# 5060 : signalisation SIP. 16384-32768 : plage audio RTP (voir explication NAT).
EXPOSE 5060/udp 5060/tcp 16384-32768/udp

ENTRYPOINT ["/entrypoint.sh"]
CMD ["freeswitch", "-nf"]
