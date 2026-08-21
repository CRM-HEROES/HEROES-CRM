FROM debian:bookworm-slim

RUN apt-get update && apt-get install -y --no-install-recommends \
        gnupg2 wget lsb-release software-properties-common apt-transport-https curl \
        ca-certificates git cmake pkg-config build-essential libssl-dev zlib1g-dev libevent-dev libspeexdsp-dev \
    && rm -rf /var/lib/apt/lists/*

RUN --mount=type=secret,id=signalwire_token \
    TOKEN="$(cat /run/secrets/signalwire_token)" \
    && wget --http-user=signalwire --http-password="$TOKEN" \
        -O /usr/share/keyrings/signalwire-freeswitch-repo.gpg \
        https://freeswitch.signalwire.com/repo/deb/debian-release/signalwire-freeswitch-repo.gpg \
    && printf 'machine freeswitch.signalwire.com login signalwire password %s\n' "$TOKEN" \
        > /etc/apt/auth.conf.d/freeswitch.conf \
    && chmod 600 /etc/apt/auth.conf.d/freeswitch.conf \
    && echo "deb [signed-by=/usr/share/keyrings/signalwire-freeswitch-repo.gpg] https://freeswitch.signalwire.com/repo/deb/debian-release/ $(lsb_release -cs) main" \
        > /etc/apt/sources.list.d/freeswitch.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends freeswitch-meta-all libfreeswitch-dev \
    && rm -rf /var/lib/apt/lists/* /etc/apt/auth.conf.d/freeswitch.conf

RUN git clone https://github.com/amigniter/mod_audio_stream.git /tmp/mod_audio_stream \
    && cd /tmp/mod_audio_stream && git submodule update --init \
    && mkdir build && cd build \
    && cmake -DCMAKE_BUILD_TYPE=Release .. \
    && make && make install \
    && rm -rf /tmp/mod_audio_stream \
    && sed -i 's#<modules>#<modules>\\n    <load module="mod_audio_stream"/>#' /etc/freeswitch/autoload_configs/modules.conf.xml

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 5080/tcp 5080/udp 26384-26483/udp
ENTRYPOINT ["/entrypoint.sh"]
CMD ["freeswitch", "-nf"]
