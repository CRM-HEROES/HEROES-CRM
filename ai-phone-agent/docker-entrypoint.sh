#!/bin/sh
set -eu

# Docker creates named volumes as root:root. The bridge must be able to write
# recordings and transcripts to the mounted /data/calls directory.
if [ "$(id -u)" = "0" ]; then
    mkdir -p /data/calls
    chown -R node:node /data/calls
    # With `su -c`, the first argument after the command becomes $0. Preserve
    # it too, otherwise `npm start` is reduced to `start`.
    exec su -s /bin/sh node -c 'exec "$0" "$@"' "$@"
fi

exec "$@"
