FROM node:22-alpine AS builder

WORKDIR /app

COPY package*.json ./
RUN npm ci --omit=dev


FROM node:22-alpine AS production

RUN sed -i 's|https://dl-cdn.alpinelinux.org|https://mirror.fcix.net|g' /etc/apk/repositories \
    && apk add --no-cache sox

WORKDIR /app

COPY --from=builder /app/node_modules ./node_modules

COPY package*.json ./
COPY src/ ./src/

RUN mkdir -p /data/calls

EXPOSE 9701 3000

CMD ["node", "src/server.js"]