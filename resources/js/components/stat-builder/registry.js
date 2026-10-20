/**
 * Registre des types de widgets du Dashboard Builder.
 *
 * Pour ajouter un nouveau type de visualisation :
 *  1. déclarer le type ici (libellé, sections de configuration, valeurs par défaut, taille),
 *  2. ajouter le moteur correspondant côté PHP (DashboardEngine) si la forme de donnée est nouvelle,
 *  3. brancher le rendu dans `renderers/WidgetRenderer.vue`.
 *
 * `sections` pilote dynamiquement l'étape "Données" du wizard :
 *   source | dimension | series | period | compare | sort | limit | filters | columns | tableOptions |
 *   kpi | steps | display
 */

export const PALETTE = [
    "#6366f1",
    "#0ea5e9",
    "#10b981",
    "#f59e0b",
    "#ef4444",
    "#8b5cf6",
    "#ec4899",
    "#14b8a6",
    "#84cc16",
    "#f97316",
];

export const WIDGET_TYPES = {
    kpi: {
        label: "KPI",
        icon: "fa-gauge-high",
        group: "Indicateurs",
        description: "Une valeur clé avec évolution",
        sections: ["source", "series", "period", "filters", "kpi", "display"],
        layout: { w: 3, h: 2 },
        minLayout: { w: 2, h: 2 },
        defaults: { comparison: "previous_period", positiveIsGood: true, icon: "fa-chart-line", color: "#6366f1" },
    },
    bar: {
        label: "Graphique en bâtons",
        icon: "fa-chart-column",
        group: "Graphiques",
        description: "Comparer des valeurs par catégorie ou par période",
        sections: ["source", "dimension", "series", "period", "compare", "sort", "limit", "filters", "display"],
        layout: { w: 6, h: 5 },
        defaults: { dimension: "day", legend: true, showValues: false, stacked: false },
    },
    hbar: {
        label: "Barres horizontales",
        icon: "fa-chart-bar",
        group: "Graphiques",
        description: "Classements et libellés longs",
        sections: ["source", "dimension", "series", "period", "sort", "limit", "filters", "display"],
        layout: { w: 6, h: 5 },
        defaults: { dimension: "user", legend: false, showValues: true, sort: { by: "value", direction: "desc" }, limit: 10 },
    },
    line: {
        label: "Courbe",
        icon: "fa-chart-line",
        group: "Graphiques",
        description: "Suivre une tendance dans le temps",
        sections: ["source", "dimension", "series", "period", "compare", "filters", "display"],
        layout: { w: 6, h: 5 },
        defaults: { dimension: "day", legend: true, showValues: false, compare: false, smooth: true },
    },
    multiline: {
        label: "Courbes multi-séries",
        icon: "fa-chart-area",
        group: "Graphiques",
        description: "Comparer plusieurs mesures sur le même axe",
        sections: ["source", "dimension", "series", "period", "compare", "filters", "display"],
        layout: { w: 8, h: 5 },
        maxSeries: 6,
        defaults: { dimension: "day", legend: true, showValues: false, smooth: true },
    },
    combo: {
        label: "Graphique combiné",
        icon: "fa-chart-simple",
        group: "Graphiques",
        description: "Bâtons + courbes sur le même graphique",
        sections: ["source", "dimension", "series", "period", "filters", "display"],
        layout: { w: 8, h: 5 },
        maxSeries: 4,
        seriesTypes: true,
        defaults: { dimension: "day", legend: true, showValues: false },
    },
    pie: {
        label: "Camembert",
        icon: "fa-chart-pie",
        group: "Graphiques",
        description: "Répartition en parts",
        sections: ["source", "dimension", "series", "period", "sort", "limit", "filters", "display"],
        layout: { w: 4, h: 5 },
        singleSeries: true,
        defaults: { dimension: "source", legend: true, showLabels: true, showPercent: true },
    },
    donut: {
        label: "Donut",
        icon: "fa-circle-notch",
        group: "Graphiques",
        description: "Répartition avec total au centre",
        sections: ["source", "dimension", "series", "period", "sort", "limit", "filters", "display"],
        layout: { w: 4, h: 5 },
        singleSeries: true,
        defaults: { dimension: "source", legend: true, showLabels: true, showPercent: true },
    },
    funnel: {
        label: "Funnel",
        icon: "fa-filter",
        group: "Graphiques",
        description: "Conversion étape par étape",
        sections: ["steps", "period", "display"],
        layout: { w: 6, h: 5 },
        defaults: {
            steps: [
                { label: "Prospects", dataSource: "prospects", field: "records", agg: "count", filters: [] },
                { label: "Appels", dataSource: "interactions", field: "records", agg: "count", filters: [] },
                { label: "Commandes", dataSource: "orders", field: "records", agg: "count", filters: [] },
            ],
            showPercent: true,
        },
    },
    heatmap: {
        label: "Calendrier / Heatmap",
        icon: "fa-calendar-days",
        group: "Graphiques",
        description: "Intensité d'activité jour après jour",
        sections: ["source", "series", "period", "filters", "display"],
        layout: { w: 12, h: 4 },
        singleSeries: true,
        defaults: { dimension: "day", color: "#6366f1", period: { preset: "last_90_days" } },
    },
    table: {
        label: "Tableau de données",
        icon: "fa-table",
        group: "Données",
        description: "Lignes détaillées, tri, recherche, actions",
        sections: ["source", "columns", "period", "filters", "tableOptions", "display"],
        layout: { w: 12, h: 6 },
        defaults: { pagination: true, perPage: 10, search: true, limit: 0 },
    },
};

export const TYPE_ORDER = ["kpi", "bar", "hbar", "line", "multiline", "pie", "donut", "funnel", "heatmap", "table", "combo"];

export const AGG_LABELS = {
    count: "Nombre",
    sum: "Somme",
    avg: "Moyenne",
    min: "Minimum",
    max: "Maximum",
};

export const FILTER_OPS = [
    { value: "eq", label: "est égal à" },
    { value: "neq", label: "est différent de" },
    { value: "contains", label: "contient" },
    { value: "in", label: "fait partie de (a, b, c)" },
    { value: "gt", label: "supérieur à" },
    { value: "lt", label: "inférieur à" },
];

export const KPI_ICONS = [
    "fa-chart-line",
    "fa-phone",
    "fa-clock",
    "fa-comment",
    "fa-shopping-bag",
    "fa-users",
    "fa-calendar",
    "fa-euro-sign",
    "fa-bolt",
    "fa-star",
    "fa-bullseye",
    "fa-trophy",
];

export function uid() {
    return "w" + Date.now().toString(36) + Math.random().toString(36).slice(2, 8);
}

export function clone(o) {
    return JSON.parse(JSON.stringify(o));
}

/**
 * Configuration initiale d'un widget d'un type donné, sur une source donnée.
 */
export function buildDefaultConfig(type, source) {
    const def = WIDGET_TYPES[type];
    const cfg = {
        type,
        dataSource: source ? source.key : "interactions",
        title: "",
        subtitle: "",
        series: [],
        period: { preset: "last_30_days" },
        filters: [],
        globalFilters: ["period", "user", "team", "role", "status", "source"],
        ...clone(def.defaults || {}),
    };

    if (source) {
        applySourceDefaults(cfg, source);
    }

    return cfg;
}

/**
 * Réaligne la configuration quand la source change (dimension / séries / colonnes).
 */
export function applySourceDefaults(cfg, source) {
    const def = WIDGET_TYPES[cfg.type];
    cfg.dataSource = source.key;
    cfg.filters = [];

    if (def.sections.includes("dimension")) {
        const dims = source.dimensions.map((d) => d.key);
        if (!dims.includes(cfg.dimension)) {
            cfg.dimension = dims.includes("day") ? (["pie", "donut", "hbar"].includes(cfg.type) ? dims.find((k) => k !== "day") || dims[0] : "day") : dims[0];
        }
        if (["pie", "donut", "hbar"].includes(cfg.type) && ["day", "week", "month", "hour", "weekday"].includes(cfg.dimension)) {
            cfg.dimension = dims.find((k) => !["day", "week", "month", "hour", "weekday"].includes(k)) || dims[0];
        }
    }

    if (def.sections.includes("series") || cfg.type === "heatmap") {
        const m = source.measures[0];
        cfg.series = [{ field: m.key, agg: m.default, label: "", color: "", type: "" }];
    }

    if (def.sections.includes("columns")) {
        cfg.columns = source.kind === "users"
            ? (source.default_columns || []).map((f) => ({ field: f, label: "" }))
            : [{ field: source.dimensions[0].key, label: "" }, { field: source.measures[0].key, label: "" }];
        cfg.sorting = source.kind === "users"
            ? { field: "performance", direction: "desc" }
            : { field: source.measures[0].key, direction: "desc" };
    }
}

/**
 * Libellé utilisable d'un widget.
 */
export function widgetTitle(widget) {
    return (widget.config && widget.config.title) || widget.title || WIDGET_TYPES[widget.type]?.label || "Widget";
}
