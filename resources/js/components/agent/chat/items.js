/**
 * The lines shown in a conversation, from the messages the server returns and
 * the documents the agent produced.
 *
 * A document appears as a card right after the last message of the run that
 * produced it, so it sits where the agent mentioned it.
 *
 * @param {Array} messages {role: user|agent|system, kind: text|tool|system, text?, tool?, run_id}
 * @param {Array} reports {id, run_id, title}
 * @returns {Array} {kind: user|agent|tool|system|report, ...}
 */
export function buildItems(messages, reports = []) {
    const items = [];

    messages.forEach((message, i) => {
        if (message.kind === "tool") {
            items.push({ kind: "tool", tool: message.tool });
        } else if (message.kind === "system" || message.role === "system") {
            items.push({ kind: "system", text: message.text });
        } else if (message.role === "user") {
            items.push({ kind: "user", text: message.text });
        } else {
            items.push({ kind: "agent", text: message.text });
        }

        const next = messages[i + 1];

        if (message.run_id && (!next || next.run_id !== message.run_id)) {
            reports
                .filter((report) => report.run_id === message.run_id)
                .forEach((report) => items.push({ kind: "report", id: report.id, title: report.title }));
        }
    });

    return items;
}

/**
 * The documents that were not there before: the newest one is opened by itself.
 */
export function newReports(before, after) {
    const known = new Set(before.map((report) => report.id));

    return after.filter((report) => !known.has(report.id));
}
