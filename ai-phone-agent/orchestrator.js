"use strict";
const express = require("express");
const config = require("./config");
const { CallCorrelation } = require("./call-correlation");
const { KavkomCallsClient } = require("./kavkom-calls-client");

const correlation = new CallCorrelation();
let callsConnected = false;
const liveCallsEnabled = config.kavkom.callsWsEnabled;
const client = new KavkomCallsClient({ onStatus: (connected) => { callsConnected = connected; }, onError: (error) => console.error("[Kavkom /calls]", error.message), onCall: handleCall });

async function bridge(path, body) {
    const response = await fetch(`${config.orchestrator.bridgeUrl}${path}`, { method: "POST", headers: { "Content-Type": "application/json", "X-AI-Agent-Secret": config.sharedSecret }, body: JSON.stringify(body) });
    if (!response.ok) throw new Error(`Voice bridge returned ${response.status}: ${await response.text()}`);
    return response.json();
}

async function startCall(prospect) {
    const destination = String(prospect.destination_number || prospect.phone_number || "");
    const extension = String(prospect.extension || config.kavkom.extensions[0] || "");
    if (!prospect.prospect_id || !destination || !extension) throw new Error("Each prospect needs prospect_id, destination_number and an extension.");
    if (config.testMode && !config.testAllowedNumbers.includes(destination.replace(/\D/g, ""))) throw new Error("TEST_MODE: destination outside whitelist.");
    const response = await bridge("/calls", { prospect_id: prospect.prospect_id, destination_number: destination, user_extension: extension, context: prospect.context || "" });
    correlation.register({ destination, extension, prospectId: prospect.prospect_id, freeSwitchUuid: response.call_uuid, context: prospect.context || "" });
    return response;
}

async function handleCall(data) {
    const call = correlation.correlate(data) || correlation.get(data.callUuid);
    if (!call) return console.warn("[Kavkom /calls] Uncorrelated call", data.callUuid);
    if (data.state === "held") await bridge(`/calls/${call.freeSwitchUuid}/pause`, { paused: true });
    if (data.state === "active") await bridge(`/calls/${call.freeSwitchUuid}/pause`, { paused: false });
    if (data.state === "finished") {
        // The bridge posts the archived transcript/structured analysis to CRM
        // when FreeSWITCH closes. Keep Kavkom's authoritative duration here.
        console.log(`[Kavkom /calls] ${data.callUuid} finished after ${data.duration}s (FreeSWITCH ${call.freeSwitchUuid}).`);
    }
}

const app = express(); app.use(express.json({ limit: "1mb" }));
app.get("/health", (_req, res) => {
    const ok = !liveCallsEnabled || callsConnected;
    res.status(ok ? 200 : 503).json({ ok, kavkom_calls_ws: callsConnected, mode: liveCallsEnabled ? "kavkom-live" : "local-test" });
});
app.post("/campaigns", async (req, res) => {
    const prospects = req.body?.prospects;
    if (!Array.isArray(prospects) || !prospects.length) return res.status(422).json({ message: "prospects must be a non-empty array." });
    const results = await Promise.allSettled(prospects.map(startCall));
    res.status(results.some((item) => item.status === "rejected") ? 207 : 201).json(results.map((item) => item.status === "fulfilled" ? { success: true, ...item.value } : { success: false, message: item.reason.message }));
});
app.listen(config.orchestrator.httpPort, () => {
    console.log(`[Orchestrator] listening on ${config.orchestrator.httpPort}`);
    if (!liveCallsEnabled) {
        console.log("[Kavkom /calls] disabled: local test mode.");
        return;
    }
    client.connect().catch((error) => console.error("[Kavkom /calls] initial connection failed", error.message));
});
async function shutdown() { await client.stop(); process.exit(0); }
process.on("SIGTERM", shutdown); process.on("SIGINT", shutdown);
