const WebSocket = require("ws");
const config = require("./config");

class TranscriptHub {
    start() {
        this.clients = new Set();
        this.wss = new WebSocket.Server({ port: config.transcriptWsPort });
        this.wss.on("connection", (ws) => {
            this.clients.add(ws);
            ws.on("close", () => this.clients.delete(ws));
        });
        console.log(`[Transcript] Dashboard WebSocket listening on ${config.transcriptWsPort}.`);
    }
    publish(event) {
        const message = JSON.stringify(event);
        for (const client of this.clients || []) if (client.readyState === WebSocket.OPEN) client.send(message);
    }
}
module.exports = new TranscriptHub();
