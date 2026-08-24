"use strict";
class CallCorrelation {
    constructor(windowMs = 120000) { this.windowMs = windowMs; this.pending = []; this.calls = new Map(); }
    register({ destination, extension, prospectId, freeSwitchUuid, originatedAt = Date.now(), context = "" }) {
        const item = { destination: String(destination).replace(/\D/g, ""), extension: String(extension), prospectId, freeSwitchUuid, originatedAt, context };
        this.pending.push(item); return item;
    }
    correlate(data) {
        const extension = String(data.presenceId || "").split("@")[0];
        const number = String(data.number || "").replace(/\D/g, "");
        const at = Date.parse(data.createdAt || "") || Date.now();
        const matches = this.pending.filter((item) => item.extension === extension && item.destination === number && Math.abs(item.originatedAt - at) <= this.windowMs)
            .sort((a, b) => Math.abs(a.originatedAt - at) - Math.abs(b.originatedAt - at));
        if (!matches.length) return null;
        const match = matches[0];
        const call = { ...match, kavkomUuid: data.callUuid, state: data.state, duration: data.duration };
        this.calls.set(data.callUuid, call); return call;
    }
    get(kavkomUuid) { return this.calls.get(kavkomUuid); }
}
module.exports = { CallCorrelation };
