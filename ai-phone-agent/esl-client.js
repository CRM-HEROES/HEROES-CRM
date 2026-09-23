const esl = require("modesl");
const crypto = require("crypto");
const config = require("./config");

/**
 * Thin wrapper around a single persistent ESL (Event Socket Library)
 * connection to FreeSWITCH. Legs are originated with a caller-supplied
 * `origination_uuid` so we know each channel's UUID up front instead of
 * having to parse FreeSWITCH's text responses.
 *
 * NOTE: this is the part of the project that most needs hands-on
 * validation against a real FreeSWITCH box — dial-string syntax, gateway
 * naming and event semantics can vary with FreeSWITCH configuration. See
 * ai-phone-agent/README.md.
 */
class EslClient {
    constructor() {
        this.conn = null;
        this.ready = null;
        this.answeredChannels = new Set();
    }

    /**
     * modesl nulls out Connection.socket as soon as the TCP stream ends (e.g.
     * FreeSWITCH restart), and every later send() then blows up with
     * "Cannot read properties of null (reading 'write')". Treat a connection
     * without a writable socket as dead so connect() rebuilds it instead of
     * handing out a zombie.
     */
    _isAlive() {
        const conn = this.conn;
        return Boolean(conn && conn.socket && conn.socket.writable && conn.authed);
    }

    _teardown(reason) {
        const conn = this.conn;
        this.conn = null;
        this.ready = null;
        this.answeredChannels.clear();
        if (conn) {
            try {
                conn.removeAllListeners();
            } catch (_) {
                // Already torn down.
            }
            try {
                conn.end();
            } catch (_) {
                // Socket may already be gone.
            }
        }
        if (reason) {
            console.warn(`[ESL] Dropping ESL connection (${reason}); it will be re-established on demand.`);
        }
    }

    connect() {
        if (this.ready && this._isAlive()) {
            return this.ready;
        }
        if (this.conn) {
            this._teardown("stale connection");
        }

        this.ready = new Promise((resolve, reject) => {
            let settled = false;
            const timeout = setTimeout(() => {
                if (settled) return;
                settled = true;
                this._teardown("connect timed out");
                reject(new Error("Timed out connecting to FreeSWITCH ESL."));
            }, 10000);
            const conn = new esl.Connection(
                config.freeswitch.eslHost,
                config.freeswitch.eslPort,
                config.freeswitch.eslPassword,
                () => {
                    if (settled) return;
                    settled = true;
                    clearTimeout(timeout);
                    console.log("[ESL] Connected to FreeSWITCH.");
                    conn.subscribe(["CHANNEL_ANSWER", "CHANNEL_HANGUP", "BACKGROUND_JOB"]);
                    conn.on("esl::event::CHANNEL_ANSWER::*", (event) => {
                        const channelUuid = event.getHeader("Unique-ID");
                        if (channelUuid) this.answeredChannels.add(channelUuid);
                    });
                    conn.on("esl::end", () => {
                        // FreeSWITCH closed the socket (restart, network drop).
                        this._teardown("connection ended");
                    });
                    resolve(conn);
                }
            );
            this.conn = conn;

            conn.on("error", (error) => {
                if (!settled) {
                    settled = true;
                    clearTimeout(timeout);
                    this._teardown("connection error during boot");
                    console.error("[ESL] Connection error.", error);
                    reject(error);
                } else {
                    console.error("[ESL] Connection error.", error);
                    this._teardown("connection error");
                }
            });
        });

        return this.ready;
    }

    /**
     * Originates a call leg into `room` (a FreeSWITCH conference) and
     * returns the channel UUID immediately (via a caller-supplied
     * `origination_uuid`), without waiting for the leg to actually answer.
     * Use `waitForAnswer`/`waitForHangup` to react to what happens next.
     */
    async originateIntoConference(dialTarget, room, { callerIdNumber, callerIdName, sipAuth } = {}) {
        await this.connect();
        if (!this._isAlive()) {
            throw new Error("FreeSWITCH ESL connection is unavailable; cannot originate.");
        }

        const uuid = crypto.randomUUID();
        const vars = {
            origination_uuid: uuid,
            origination_caller_id_number: callerIdNumber || config.freeswitch.callerIdNumber,
            origination_caller_id_name: callerIdName || config.freeswitch.callerIdName,
            ignore_early_media: "true",
            sip_auth_username: sipAuth?.extension,
            sip_auth_password: sipAuth?.password,
            sip_auth_realm: sipAuth?.user_context,
        };
        const varString = Object.entries(vars)
            .filter(([, value]) => value !== undefined && value !== null && value !== "")
            .map(([key, value]) => `${key}='${value}'`)
            .join(",");

        const command = `originate {${varString}}${dialTarget} &conference(${room}@${config.freeswitch.conferenceProfile})`;

        return new Promise((resolve, reject) => {
            const jobUuid = crypto.randomUUID();
            const timer = setTimeout(() => {
                reject(new Error(`Timed out waiting for BACKGROUND_JOB ${jobUuid}`));
            }, 10000);
            const onJob = (event) => {
                clearTimeout(timer);
                const body = (event.getBody() || "").trim();
                if (body.startsWith("-ERR")) {
                    reject(new Error(`FreeSWITCH originate failed: ${body}`));
                } else {
                    resolve(uuid);
                }
            };
            this.conn.bgapi(command, "", jobUuid, onJob);
        });
    }

    waitForAnswer(channelUuid, timeoutMs = 45000) {
        if (this.answeredChannels.has(channelUuid)) {
            this.answeredChannels.delete(channelUuid);
            return Promise.resolve();
        }
        return this._waitForEvent("CHANNEL_ANSWER", channelUuid, timeoutMs);
    }

    waitForHangup(channelUuid) {
        // No timeout: a call can legitimately stay up for a long time.
        return this._waitForEvent("CHANNEL_HANGUP", channelUuid, 0);
    }

    _waitForEvent(eventName, channelUuid, timeoutMs) {
        return new Promise((resolve, reject) => {
            const conn = this.conn;
            if (!conn) {
                reject(new Error(`Cannot wait for ${eventName}: ESL connection is down.`));
                return;
            }
            let timer = null;
            let done = false;
            const finish = (settle, value) => {
                if (done) return;
                done = true;
                if (timer) clearTimeout(timer);
                conn.removeListener(`esl::event::${eventName}::*`, handler);
                conn.removeListener("esl::end", onEnd);
                settle(value);
            };
            const handler = (event) => {
                if (event.getHeader("Unique-ID") !== channelUuid) {
                    return;
                }
                finish(resolve, event);
            };
            const onEnd = () => {
                finish(reject, new Error(`ESL connection dropped while waiting for ${eventName} on ${channelUuid}.`));
            };
            conn.on(`esl::event::${eventName}::*`, handler);
            conn.on("esl::end", onEnd);

            if (timeoutMs > 0) {
                timer = setTimeout(() => {
                    finish(reject, new Error(`Timed out waiting for ${eventName} on ${channelUuid}`));
                }, timeoutMs);
            }
        });
    }

    /** Attaches mod_audio_stream to a channel, streaming its audio to `wsUrl`. */
    startAudioStream(channelUuid, wsUrl, metadata) {
        const metadataArg = JSON.stringify(metadata).replace(/'/g, "\\'");
        return this._api(`uuid_audio_stream ${channelUuid} start ${wsUrl} mono 16k ${metadataArg}`);
    }

    hangup(channelUuid) {
        return this._api(`uuid_kill ${channelUuid}`).catch(() => {
            // The channel may already be gone.
        });
    }

    _api(command) {
        if (!this._isAlive()) {
            return Promise.reject(new Error(`FreeSWITCH ESL connection is down; cannot run "${command}".`));
        }
        return new Promise((resolve, reject) => {
            this.conn.api(command, (response) => {
                const body = response && response.getBody ? response.getBody() : "";
                if (body.startsWith("-ERR")) {
                    reject(new Error(`FreeSWITCH API error for "${command}": ${body}`));
                } else {
                    resolve(body);
                }
            });
        });
    }
}

module.exports = new EslClient();
