const fs = require("fs");
const path = require("path");
const config = require("./config");

class CallArchive {
    constructor(callUuid) {
        this.dir = path.join(config.recordingDir, callUuid);
        fs.mkdirSync(this.dir, { recursive: true });
        this.transcript = fs.createWriteStream(path.join(this.dir, "transcript.jsonl"), { flags: "a" });
        this.inbound = fs.createWriteStream(path.join(this.dir, "caller-16khz.pcm"), { flags: "a" });
        this.outbound = fs.createWriteStream(path.join(this.dir, "assistant-24khz.pcm"), { flags: "a" });
    }
    event(speaker, text) { this.transcript.write(`${JSON.stringify({ at: new Date().toISOString(), speaker, text })}\n`); }
    writeInbound(buffer) { this.inbound.write(buffer); }
    writeOutbound(buffer) { this.outbound.write(buffer); }
    close() { this.transcript.end(); this.inbound.end(); this.outbound.end(); }
}
module.exports = { CallArchive };
