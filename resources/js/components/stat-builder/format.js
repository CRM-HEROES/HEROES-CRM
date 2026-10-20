const nf = new Intl.NumberFormat("fr-FR", { maximumFractionDigits: 2 });

export function formatNumber(v) {
    if (v === null || v === undefined || v === "") return "—";
    const n = Number(v);
    return Number.isNaN(n) ? String(v) : nf.format(n);
}

export function formatMinutes(v) {
    const n = Number(v);
    if (Number.isNaN(n)) return "—";
    if (n >= 120) {
        const h = Math.floor(n / 60);
        const m = Math.round(n % 60);
        return `${h} h ${String(m).padStart(2, "0")}`;
    }
    return `${nf.format(Math.round(n * 10) / 10)} min`;
}

/**
 * Formate une valeur selon un `format` (minutes, percentage...).
 */
export function formatValue(v, format) {
    if (format === "minutes") return formatMinutes(v);
    if (format === "percentage") return `${formatNumber(v)} %`;
    return formatNumber(v);
}

export function formatDateTime(v) {
    if (!v) return "—";
    const d = new Date(v);
    if (Number.isNaN(d.getTime())) return String(v);
    return d.toLocaleString("fr-FR", { day: "2-digit", month: "short", hour: "2-digit", minute: "2-digit" });
}

export function hexToRgba(hex, alpha) {
    const m = /^#?([0-9a-f]{6})$/i.exec(hex || "");
    if (!m) return `rgba(99,102,241,${alpha})`;
    const n = parseInt(m[1], 16);
    return `rgba(${(n >> 16) & 255},${(n >> 8) & 255},${n & 255},${alpha})`;
}
