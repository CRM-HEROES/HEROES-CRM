"use strict";
const WebSocket = require("ws");
const config = require("./config");

class KavkomTokenManager {
    constructor(http = global.fetch) { this.http = http; this.token = null; this.expiresAt = 0; }
    async acquire(force = false) {
        if (!force && this.token && Date.now() < this.expiresAt - 60000) return this.token;
        let refresh = config.kavkom.refreshAccessToken;
        if (!refresh && config.kavkom.refreshTokenEndpoint && config.kavkom.refreshBearer) {
            const response = await this.http(config.kavkom.refreshTokenEndpoint, { method: "POST", headers: { Authorization: `Bearer ${config.kavkom.refreshBearer}`, "Content-Type": "application/json" } });
            if (!response.ok) throw new Error(`Kavkom refresh-token request failed (${response.status}).`);
            const payload = await response.json();
            refresh = payload.refreshToken || payload.token;
        }
        if (!refresh || !config.kavkom.userUuid || !config.kavkom.extensions.length) throw new Error("Kavkom /calls credentials are incomplete.");
        const response = await this.http(`${config.kavkom.liveBaseUrl}/api/auth/acquire-access`, { method: "POST", headers: { Authorization: `Bearer ${refresh}`, "Content-Type": "application/json" }, body: JSON.stringify({ userUuid: config.kavkom.userUuid, extensions: config.kavkom.extensions, scope: "calls", application: "ai-phone-agent" }) });
        if (!response.ok) {
            // The response can explain a bad extension/user pairing. It never
            // contains the bearer token, but keep the message bounded for logs.
            const detail = (await response.text()).replace(/\s+/g, " ").slice(0, 500);
            throw new Error(`Kavkom access-token request failed (${response.status})${detail ? `: ${detail}` : "."}`);
        }
        const payload = await response.json();
        this.token = payload.token; this.expiresAt = Date.parse(payload.expiresAt) || Date.now() + 60000;
        return this.token;
    }
    async verify() { return this._tokenAction("verify-access"); }
    async revoke() { return this._tokenAction("revoke-access"); }
    async _tokenAction(action) {
        if (!this.token) return;
        const response = await this.http(`${config.kavkom.liveBaseUrl}/api/auth/${action}`, { method: "POST", headers: { Authorization: `Bearer ${this.token}`, "Content-Type": "application/json" }, body: JSON.stringify({}) });
        if (!response.ok) throw new Error(`Kavkom ${action} failed (${response.status}).`);
    }
}

class KavkomCallsClient {
    constructor({ tokenManager, onCall, onError, onStatus } = {}) {
        this.tokens = tokenManager || new KavkomTokenManager(); this.onCall = onCall || (() => {}); this.onError = onError || (() => {}); this.onStatus = onStatus || (() => {}); this.attempt = 0; this.stopped = false;
    }
    async connect() {
        const token = await this.tokens.acquire(true);
        const url = new URL(`${config.kavkom.liveBaseUrl.replace(/^http/, "ws")}/calls`);
        url.searchParams.set("t", token); url.searchParams.set("ext", config.kavkom.extensions.join(","));
        this.ws = new WebSocket(url);
        this.ws.on("open", () => { this.attempt = 0; this.onStatus(true); });
        this.ws.on("message", (raw) => this.handleMessage(raw));
        this.ws.on("error", (error) => this.onError(error));
        this.ws.on("close", () => { this.onStatus(false); if (!this.stopped) this.reconnect(); });
    }
    handleMessage(raw) {
        let message; try { message = JSON.parse(raw.toString()); } catch (_) { return; }
        if (message.event === "ping") { this.ws.send(JSON.stringify({ event: "pong", data: message.data })); return; }
        if (message.event === "call") return this.onCall(message.data);
        if (message.event === "error") return this.onError(new Error(message.data?.message || "Kavkom WebSocket error"));
    }
    reconnect() { const delay = Math.min(30000, 1000 * 2 ** this.attempt++); setTimeout(() => this.connect().catch((error) => { this.onError(error); this.reconnect(); }), delay); }
    async stop() { this.stopped = true; if (this.ws) this.ws.close(); await this.tokens.revoke().catch((error) => this.onError(error)); }
}
module.exports = { KavkomTokenManager, KavkomCallsClient };
