/**
 * Markdown to HTML for what an AI agent writes (answers and documents).
 *
 * What an agent writes is untrusted: it reads names, notes and messages of the
 * CRM, which anybody can fill in, so a prompt injection can make it write
 * anything. The renderer is therefore built to be safe by construction:
 *
 *  - the whole text is HTML-escaped FIRST; no tag of the input ever survives,
 *    and only the tags written here are produced;
 *  - links are limited to http(s) and mailto, and open in a new tab with
 *    rel="noopener noreferrer";
 *  - images are NOT supported: an image address would send data to a stranger's
 *    server as soon as the page is displayed (a classic way to steal data).
 *
 * Supported: titles, paragraphs, bold, italic, strikethrough, inline code,
 * code blocks, quotes, bulleted and numbered lists (nested), tables, rules, links.
 */

const ESCAPES = { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" };

export function escapeHtml(text) {
    return String(text).replace(/[&<>"']/g, (c) => ESCAPES[c]);
}

// An address already escaped by escapeHtml: only http(s) and mailto
function safeUrl(escaped) {
    const url = escaped.replace(/&amp;/g, "&");

    return /^(https?:\/\/|mailto:)[^\s"'<>`]+$/i.test(url) ? escaped : null;
}

/**
 * Inline formatting of text that is already escaped
 */
function inline(text) {
    const codes = [];

    // Code first, so that nothing inside it is formatted
    text = text.replace(/`([^`\n]+)`/g, (_, code) => {
        codes.push(code);

        return `\u0001${codes.length - 1}\u0001`;
    });

    text = text.replace(/\[([^\]\n]+)\]\(([^)\s]+)\)/g, (match, label, url) => {
        const safe = safeUrl(url);

        return safe ? `<a href="${safe}" target="_blank" rel="noopener noreferrer">${label}</a>` : match;
    });

    text = text
        .replace(/\*\*([^*\n]+?)\*\*/g, "<strong>$1</strong>")
        .replace(/__([^_\n]+?)__/g, "<strong>$1</strong>")
        .replace(/(^|[^*\w])\*([^*\s][^*\n]*?)\*(?!\w)/g, "$1<em>$2</em>")
        .replace(/(^|[^_\w])_([^_\s][^_\n]*?)_(?!\w)/g, "$1<em>$2</em>")
        .replace(/~~([^~\n]+?)~~/g, "<del>$1</del>");

    return text.replace(/\u0001(\d+)\u0001/g, (_, i) => `<code>${codes[i]}</code>`);
}

const FENCE = /^ {0,3}(```|~~~)\s*([\w+-]*)\s*$/;
const HEADING = /^ {0,3}(#{1,6})\s+(.*?)\s*#*\s*$/;
const RULE = /^ {0,3}([-*_])(\s*\1){2,}\s*$/;
const QUOTE = /^ {0,3}&gt;\s?(.*)$/;
const BULLET = /^(\s*)[-*+]\s+(.*)$/;
const NUMBERED = /^(\s*)\d+[.)]\s+(.*)$/;
const TABLE_SEPARATOR = /^\s*\|?\s*:?-{2,}:?\s*(\|\s*:?-{2,}:?\s*)*\|?\s*$/;

function cells(line) {
    return line
        .trim()
        .replace(/^\|/, "")
        .replace(/\|$/, "")
        .split("|")
        .map((cell) => cell.trim());
}

function startsBlock(line, next) {
    return (
        FENCE.test(line) ||
        HEADING.test(line) ||
        RULE.test(line) ||
        QUOTE.test(line) ||
        BULLET.test(line) ||
        NUMBERED.test(line) ||
        (line.includes("|") && next !== undefined && TABLE_SEPARATOR.test(next) && next.includes("-"))
    );
}

function list(lines, start) {
    const first = lines[start];
    const ordered = NUMBERED.test(first);
    const marker = ordered ? NUMBERED : BULLET;
    const baseIndent = first.match(/^\s*/)[0].length;
    const items = [];
    let i = start;

    while (i < lines.length) {
        const line = lines[i];
        const match = line.match(marker);

        if (!match || match[1].length < baseIndent) {
            break;
        }

        if (match[1].length > baseIndent + 1) {
            // Deeper than the list: it belongs to the item above (handled below)
            break;
        }

        const body = [match[2]];
        i++;

        // Lines indented deeper than the marker belong to this item (sub-lists, continuations)
        const nested = [];
        while (i < lines.length) {
            const next = lines[i];
            const indent = next.match(/^\s*/)[0].length;

            if (next.trim() === "" || indent <= baseIndent + 1) {
                break;
            }

            nested.push(next.slice(Math.min(indent, baseIndent + 2)));
            i++;
        }

        let html = inline(body.join(" "));
        if (nested.length) {
            html += blocks(nested);
        }

        items.push(`<li>${html}</li>`);
    }

    const tag = ordered ? "ol" : "ul";

    return [`<${tag}>${items.join("")}</${tag}>`, i];
}

function table(lines, start) {
    const header = cells(lines[start]);
    const aligns = cells(lines[start + 1]).map((cell) =>
        cell.startsWith(":") && cell.endsWith(":") ? "center" : cell.endsWith(":") ? "right" : cell.startsWith(":") ? "left" : null
    );
    const align = (i) => (aligns[i] ? ` style="text-align:${aligns[i]}"` : "");

    let html = "<div class=\"hc-md-table\"><table><thead><tr>";
    html += header.map((cell, i) => `<th${align(i)}>${inline(cell)}</th>`).join("");
    html += "</tr></thead><tbody>";

    let i = start + 2;
    while (i < lines.length && lines[i].trim() !== "" && lines[i].includes("|")) {
        const row = cells(lines[i]);
        html += "<tr>" + header.map((_, c) => `<td${align(c)}>${inline(row[c] ?? "")}</td>`).join("") + "</tr>";
        i++;
    }

    return [html + "</tbody></table></div>", i];
}

function blocks(lines) {
    const out = [];
    let i = 0;

    while (i < lines.length) {
        const line = lines[i];

        if (line.trim() === "") {
            i++;
            continue;
        }

        const fence = line.match(FENCE);
        if (fence) {
            const code = [];
            i++;
            while (i < lines.length && !new RegExp(`^ {0,3}${fence[1]}\\s*$`).test(lines[i])) {
                code.push(lines[i]);
                i++;
            }
            i++; // the closing fence (or the end of the text)
            out.push(`<pre><code>${code.join("\n")}</code></pre>`);
            continue;
        }

        const heading = line.match(HEADING);
        if (heading) {
            out.push(`<h${heading[1].length}>${inline(heading[2])}</h${heading[1].length}>`);
            i++;
            continue;
        }

        if (RULE.test(line)) {
            out.push("<hr>");
            i++;
            continue;
        }

        if (QUOTE.test(line)) {
            const quoted = [];
            while (i < lines.length && QUOTE.test(lines[i])) {
                quoted.push(lines[i].match(QUOTE)[1]);
                i++;
            }
            out.push(`<blockquote>${blocks(quoted)}</blockquote>`);
            continue;
        }

        if (BULLET.test(line) || NUMBERED.test(line)) {
            const [html, next] = list(lines, i);
            out.push(html);
            i = next;
            continue;
        }

        if (line.includes("|") && lines[i + 1] !== undefined && lines[i + 1].includes("-") && TABLE_SEPARATOR.test(lines[i + 1])) {
            const [html, next] = table(lines, i);
            out.push(html);
            i = next;
            continue;
        }

        // A paragraph: lines up to a blank line or the start of another block
        const paragraph = [line];
        i++;
        while (i < lines.length && lines[i].trim() !== "" && !startsBlock(lines[i], lines[i + 1])) {
            paragraph.push(lines[i]);
            i++;
        }
        out.push(`<p>${paragraph.map((l) => inline(l.trim())).join("<br>")}</p>`);
    }

    return out.join("");
}

/**
 * @param {string} text Markdown written by an agent
 * @returns {string} HTML that is safe to put in v-html
 */
export function renderMarkdown(text) {
    if (text === null || text === undefined) {
        return "";
    }

    // The escape comes first, and the markers used internally cannot be forged
    const escaped = escapeHtml(String(text).replace(/\u0000|\u0001/g, "")).replace(/\r\n?/g, "\n");

    return blocks(escaped.split("\n"));
}
