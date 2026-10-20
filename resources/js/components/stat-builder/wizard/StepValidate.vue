<template>
    <div>
        <h3 class="sb-step-title">{{ editing ? "Valider les modifications" : "Prêt à ajouter au dashboard" }}</h3>
        <p class="sb-step-help">Vérifiez le récapitulatif. Vous pourrez ensuite déplacer, redimensionner ou modifier ce widget à tout moment.</p>

        <div class="sb-validate-grid">
            <PreviewCard :config="config" :global-filters="globalFilters" />

            <div class="sb-summary">
                <div v-for="row in rows" :key="row.label" class="sb-summary-row">
                    <span>{{ row.label }}</span>
                    <strong>{{ row.value }}</strong>
                </div>
            </div>
        </div>

        <button type="button" class="sb-link-btn" @click="showJson = !showJson">
            <i class="fa" :class="showJson ? 'fa-chevron-down' : 'fa-chevron-right'"></i> Configuration avancée (JSON)
        </button>
        <pre v-if="showJson" class="sb-json">{{ json }}</pre>
    </div>
</template>

<script>
import PreviewCard from "./PreviewCard.vue";
import { WIDGET_TYPES, AGG_LABELS } from "../registry";

export default {
    components: { PreviewCard },

    props: {
        config: { type: Object, required: true },
        meta: { type: Object, required: true },
        globalFilters: { type: Object, default: () => ({}) },
        editing: { type: Boolean, default: false },
    },

    data() {
        return { showJson: false };
    },

    computed: {
        source() {
            return this.meta.sources.find((s) => s.key === this.config.dataSource);
        },

        json() {
            return JSON.stringify(this.config, (k, v) => (k === "_id" ? undefined : v), 2);
        },

        rows() {
            const c = this.config;
            const s = this.source;
            const rows = [{ label: "Type", value: WIDGET_TYPES[c.type].label }];

            if (c.type !== "funnel" && s) rows.push({ label: "Source", value: s.label });

            if (c.dimension && s) {
                const d = s.dimensions.find((x) => x.key === c.dimension);
                rows.push({ label: "Dimension", value: d ? d.label : c.dimension });
            }

            if (c.series && c.series.length && s) {
                rows.push({
                    label: "Séries",
                    value: c.series
                        .map((x) => {
                            const m = s.measures.find((y) => y.key === x.field);
                            return `${x.label || (m && m.label) || x.field} (${AGG_LABELS[x.agg] || x.agg})`;
                        })
                        .join(", "),
                });
            }

            if (c.type === "table" && c.columns) {
                rows.push({ label: "Colonnes", value: `${c.columns.length}` });
                rows.push({ label: "Pagination", value: c.pagination ? `${c.perPage} lignes / page` : "Non" });
            }

            if (c.type === "funnel") rows.push({ label: "Étapes", value: (c.steps || []).map((x) => x.label).join(" → ") });

            const follow = (c.globalFilters || []).includes("period");
            const p = c.period || {};
            const label = (this.meta.periods.find((x) => x.value === p.preset) || {}).label || p.preset;
            rows.push({ label: "Période", value: follow ? "Suit le filtre global" : label });

            rows.push({ label: "Filtres du widget", value: `${(c.filters || []).length}` });
            rows.push({
                label: "Filtres globaux",
                value: (c.globalFilters || []).length
                    ? this.meta.global_filters.filter((f) => c.globalFilters.includes(f.key)).map((f) => f.label).join(", ")
                    : "Aucun",
            });

            return rows;
        },
    },
};
</script>

<style scoped>
.sb-validate-grid { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr); gap: 16px; margin-bottom: 16px; }
.sb-summary { border: 1px solid var(--sb-border); border-radius: 14px; padding: 6px 14px; align-self: start; background: var(--sb-surface); }
.sb-summary-row { display: flex; justify-content: space-between; gap: 12px; padding: 9px 0; border-bottom: 1px solid var(--sb-border); font-size: 13px; }
.sb-summary-row:last-child { border-bottom: none; }
.sb-summary-row span { color: var(--sb-muted); flex: none; }
.sb-summary-row strong { text-align: right; font-weight: 600; color: var(--sb-text); word-break: break-word; }
.sb-link-btn { border: none; background: transparent; color: var(--sb-muted); font-size: 12px; cursor: pointer; padding: 4px 0; }
.sb-link-btn:hover { color: var(--sb-accent); }
.sb-json {
    margin-top: 8px;
    background: #0f172a;
    color: #e2e8f0;
    padding: 14px;
    border-radius: 12px;
    font-size: 12px;
    max-height: 260px;
    overflow: auto;
}
@media (max-width: 800px) { .sb-validate-grid { grid-template-columns: 1fr; } }
</style>
