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

USER node
EXPOSE 4000 4001 4002 4010 4100
CMD ["npm", "start"]
