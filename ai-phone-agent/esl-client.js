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
    }

    connect() {
        if (this.ready) {
            return this.ready;
        }

        this.ready = new Promise((resolve, reject) => {
            const timeout = setTimeout(() => {
                this.ready = null;
                reject(new Error("Timed out connecting to FreeSWITCH ESL."));
            }, 10000);
            this.conn = new esl.Connection(
                config.freeswitch.eslHost,
                config.freeswitch.eslPort,
                config.freeswitch.eslPassword,
                () => {
                    clearTimeout(timeout);
                    console.log("[ESL] Connected to FreeSWITCH.");
                    this.conn.subscribe(["CHANNEL_ANSWER", "CHANNEL_HANGUP", "BACKGROUND_JOB"]);
                    resolve(this.conn);
                }
            );

            this.conn.on("error", (error) => {
                clearTimeout(timeout);
                console.error("[ESL] Connection error.", error);
                this.ready = null;
                reject(error);
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
    async originateIntoConference(dialTarget, room, { callerIdNumber, callerIdName } = {}) {
        await this.connect();

        const uuid = crypto.randomUUID();
        const vars = {
            origination_uuid: uuid,
            origination_caller_id_number: callerIdNumber || config.freeswitch.callerIdNumber,
            origination_caller_id_name: callerIdName || config.freeswitch.callerIdName,
            ignore_early_media: "true",
        };
        const varString = Object.entries(vars)
            .filter(([, value]) => value !== undefined && value !== null && value !== "")
            .map(([key, value]) => `${key}='${value}'`)
            .join(",");

        const command = `originate {${varString}}${dialTarget} &conference(${room}@${config.freeswitch.conferenceProfile})`;

        return new Promise((resolve, reject) => {
            const jobUuid = crypto.randomUUID();
            const onJob = (event) => {
                if (event.getHeader("Job-UUID") !== jobUuid) {
                    return;
                }
                this.conn.removeListener("esl::event::BACKGROUND_JOB::*", onJob);
                const body = (event.getBody() || "").trim();
                if (body.startsWith("-ERR")) {
                    reject(new Error(`FreeSWITCH originate failed: ${body}`));
                } else {
                    resolve(uuid);
                }
            };
            this.conn.on("esl::event::BACKGROUND_JOB::*", onJob);
            this.conn.bgapi(command, jobUuid);
        });
    }

    waitForAnswer(channelUuid, timeoutMs = 45000) {
        return this._waitForEvent("CHANNEL_ANSWER", channelUuid, timeoutMs);
    }

    waitForHangup(channelUuid) {
        // No timeout: a call can legitimately stay up for a long time.
        return this._waitForEvent("CHANNEL_HANGUP", channelUuid, 0);
    }

    _waitForEvent(eventName, channelUuid, timeoutMs) {
        return new Promise((resolve, reject) => {
            let timer = null;
            const handler = (event) => {
                if (event.getHeader("Unique-ID") !== channelUuid) {
                    return;
                }
                if (timer) clearTimeout(timer);
                this.conn.removeListener(`esl::event::${eventName}::*`, handler);
                resolve(event);
            };
            this.conn.on(`esl::event::${eventName}::*`, handler);

            if (timeoutMs > 0) {
                timer = setTimeout(() => {
                    this.conn.removeListener(`esl::event::${eventName}::*`, handler);
                    reject(new Error(`Timed out waiting for ${eventName} on ${channelUuid}`));
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
