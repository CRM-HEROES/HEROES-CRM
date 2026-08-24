FROM node:20-bookworm-slim

WORKDIR /app
ENV NODE_ENV=production

COPY package.json package-lock.json ./
# Les logs et délais explicites évitent un build silencieusement bloqué par
# un DNS, proxy ou pare-feu Docker. Le cache BuildKit accélère les rebuilds.
RUN --mount=type=cache,target=/root/.npm \
    npm ci --omit=dev --no-audit --no-fund --loglevel=verbose \
      --fetch-timeout=60000 --fetch-retries=2

COPY . ./

# Named Docker volumes are created as root. Keep the image entrypoint as root
# just long enough to grant the runtime user access to call recordings; it then
# drops privileges before launching Node.
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 4000 4001 4002 4010 4100
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["npm", "start"]
