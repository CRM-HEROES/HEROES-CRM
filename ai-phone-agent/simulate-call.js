#!/usr/bin/env node
/**
 * Simulates an AI phone call without any telephony infrastructure
 * (no Kavkom, no FreeSWITCH): you type what "the prospect" says in the
 * terminal, Gemini Live answers (as audio — discarded here — plus a live
 * text transcript, printed to the terminal), and when you end the
 * conversation the exact same finalize()/Laravel-ingest code path used by
 * a real call runs. This is a real test of the Gemini integration, the
 * record_prospect_info tool, and the CRM auto-fill, just with a keyboard
 * instead of a phone.
 *
 * It does NOT test: SIP registration, the FreeSWITCH conference, or
 * actual call audio quality — that part still needs the real
 * infrastructure described in README.md.
 *
 * Usage:
 *   node simulate-call.js <prospect_id>
 */
const readline = require("readline");
const config = require("./config");
const { GeminiCallBridge } = require("./gemini-call-bridge");

const prospectId = Number(process.argv[2]);

if (!prospectId) {
    console.error("Usage: node simulate-call.js <prospect_id>");
    console.error("(prospect_id doit exister dans la base — sa fiche sera réellement mise à jour.)");
    process.exit(1);
}

if (!config.gemini.apiKey) {
    console.error("GEMINI_API_KEY manquant dans ai-phone-agent/.env — impossible de simuler l'appel.");
    process.exit(1);
}
if (!config.sharedSecret) {
    console.error("AI_PHONE_AGENT_SHARED_SECRET manquant dans ai-phone-agent/.env (doit matcher celui du .env Laravel).");
    process.exit(1);
}

const bridge = new GeminiCallBridge({
    callUuid: `simulation-${Date.now()}`,
    prospectId,
});

bridge.onText = (text) => {
    process.stdout.write(`\nIA: ${text}\n> `);
};

console.log(`Simulation d'appel IA pour le prospect #${prospectId}.`);
console.log(`Tape ce que dirait le prospect, ligne par ligne. Tape "fin" (ou Ctrl+C) pour raccrocher.\n`);

// Lines can arrive (piped/scripted input, or just fast typing) before
// Gemini is ready, or before it has finished responding to the previous
// one. Queue them and only send one at a time, waiting for
// onReady/onTurnComplete in between — otherwise a scripted test sends
// everything (including "fin") before Gemini gets a chance to reply.
const pendingLines = [];
let waitingForResponse = false;
let hungUp = false;

function processNext() {
    if (hungUp || waitingForResponse || !bridge.ready || !pendingLines.length) {
        return;
    }

    const text = pendingLines.shift();
    if (text.toLowerCase() === "fin") {
        hangUp();
        return;
    }

    waitingForResponse = true;
    bridge.pushText(text);

    // Safety net: don't hang forever if Gemini never sends turnComplete.
    setTimeout(() => {
        if (waitingForResponse) {
            waitingForResponse = false;
            processNext();
        }
    }, 20000);
}

// Hanging up must only happen when "fin" is actually dequeued and
// processed — NOT when the input stream happens to end. With piped/
// scripted input (used for automated testing), stdin reaches EOF and
// readline emits "close" as soon as every line has been read, which can
// be well before Gemini has even replied to the first one. If that
// premature "close" ended the call, the rest of the scripted
// conversation — and the tool calls/auto-fill it was supposed to
// exercise — would never happen.
function hangUp() {
    if (hungUp) return;
    hungUp = true;
    endCall();
}

async function endCall() {
    console.log("\nRaccroché. Envoi du résultat à Laravel...");
    await bridge.finalize();
    console.log("\nDonnées envoyées à AiPhoneAgentController::ingest() :");
    console.log(JSON.stringify(bridge.buildAnalysis(), null, 2));
    console.log(`\nTranscript :\n${bridge.transcript.join("\n")}`);
    console.log(`\nVérifie la fiche du prospect #${prospectId} dans le CRM pour confirmer le remplissage automatique.`);
    process.exit(0);
}

bridge.onReady = () => processNext();
bridge.onTurnComplete = () => {
    waitingForResponse = false;
    processNext();
};

bridge.connect();

const rl = readline.createInterface({ input: process.stdin, output: process.stdout, prompt: "> " });

rl.prompt();
rl.on("line", (line) => {
    const text = line.trim();
    if (!text) {
        rl.prompt();
        return;
    }
    pendingLines.push(text);
    processNext();
});

// Real interactive use (Ctrl+D/Ctrl+C with nothing left to say): hang up
// immediately. Piped/scripted input reaching EOF while lines are still
// queued or a response is in flight: let the queue keep draining on its
// own (driven by onTurnComplete, not by new "line" events) and hang up
// once it naturally reaches "fin".
rl.on("close", () => {
    if (!hungUp && !waitingForResponse && !pendingLines.length) {
        hangUp();
    }
});
