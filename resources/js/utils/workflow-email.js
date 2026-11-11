/**
 * Helpers of the email editor of the workflows. Pure functions, tested without Vue.
 */

// What the preview shows for each variable: an example, never real data
const SAMPLE = {
    prospect: {
        firstname: "Marie",
        lastname: "Dupont",
        fullname: "Marie Dupont",
        email: "marie.dupont@exemple.fr",
        phone: "01 23 45 67 89",
        mobile: "06 12 34 56 78",
        company: "Société Exemple",
        city: "Lyon",
        postal_code: "69001",
        country: "France",
    },
    user: {
        firstname: "Paul",
        lastname: "Martin",
        fullname: "Paul Martin",
        email: "paul.martin@exemple.fr",
    },
    project: {
        name: "Votre projet",
    },
};

export function escapeHtml(text) {
    return String(text).replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
}

/**
 * The text with the variables {{group.name}} replaced by an example. A
 * variable that does not exist is left as it is, so that a typo can be seen.
 */
export function withSamples(html, projectName = null) {
    return String(html || "").replace(/\{\{\s*(\w+)\.(\w+)\s*\}\}/g, (match, group, name) => {
        const value = group.toLowerCase() === "project" && name.toLowerCase() === "name" && projectName
            ? projectName
            : (SAMPLE[group.toLowerCase()] || {})[name.toLowerCase()];

        return value === undefined ? match : escapeHtml(value);
    });
}

/**
 * A link or an image address the editor accepts: web and mail addresses only
 * (never "javascript:"). Returns the address, or null.
 */
export function safeUrl(url, { image = false } = {}) {
    const value = String(url || "").trim();

    if (!value) {
        return null;
    }

    const allowed = image ? /^https?:\/\//i : /^(https?:\/\/|mailto:|tel:)/i;

    if (allowed.test(value)) {
        return value;
    }

    // "www.exemple.fr" and "exemple.fr/page" are links the way people write them
    if (!image && /^[\w-]+(\.[\w-]+)+([/?#].*)?$/.test(value) && !/\s/.test(value)) {
        return "https://" + value;
    }

    return null;
}

/**
 * Is there something to read in the HTML? (An empty paragraph is not content.)
 */
export function hasContent(html) {
    const text = String(html || "")
        .replace(/<img\b[^>]*>/gi, "x")
        .replace(/<[^>]*>/g, "")
        .replace(/&nbsp;/g, " ")
        .trim();

    return text !== "";
}

/**
 * The design of an email, with what the server uses when nothing was chosen
 * (App\Workflows\Actions\EmailDesign::defaults: keep both in step).
 */
export function defaultDesign(projectName = "") {
    const name = projectName || "";

    return {
        style: "branded",
        font: "arial",
        header_color: "#163b52",
        accent_color: "#4a8796",
        background_color: "#f3f6f8",
        text_color: "#42515b",
        logo: "none",
        show_title: true,
        show_signature: true,
        show_footer: true,
        brand_name: name,
        brand_label: "",
        category: "",
        closing: "Bien cordialement,",
        signature_name: name,
        sent_by: name ? `Un message envoyé par ${name}.` : "",
        rights: name ? `© ${new Date().getFullYear()} ${name} · Tous droits réservés.` : "",
    };
}

// Colour schemes to start from: the header and the accent, one click
export const PALETTES = [
    { key: "blue", header_color: "#163b52", accent_color: "#4a8796" },
    { key: "indigo", header_color: "#3a49c9", accent_color: "#7b88f0" },
    { key: "green", header_color: "#14633a", accent_color: "#2e9d5c" },
    { key: "purple", header_color: "#5b2a86", accent_color: "#8e5bd6" },
    { key: "orange", header_color: "#a8470f", accent_color: "#e8892f" },
    { key: "black", header_color: "#1f2330", accent_color: "#6b7080" },
];

export const FONTS = ["arial", "verdana", "tahoma", "trebuchet", "georgia", "times"];
export const STYLES = ["branded", "simple"];
export const COLOR_KEYS = ["header_color", "accent_color", "text_color", "background_color"];
export const TEXT_KEYS = ["brand_name", "brand_label", "category", "closing", "signature_name", "sent_by", "rights"];
