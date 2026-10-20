<template>
    <section class="sb-section">
        <div class="sb-section-title">
            {{ single ? "Valeur" : "Séries de données" }}
            <span v-if="!single" class="sb-hint">(valeur Y)</span>
        </div>

        <div v-for="(s, i) in config.series" :key="i" class="sb-series">
            <div class="sb-series-grid">
                <label class="sb-field">
                    <span class="sb-label">Mesure</span>
                    <select class="sb-input" :value="s.field" @change="setField(s, $event.target.value)">
                        <option v-for="m in source.measures" :key="m.key" :value="m.key">{{ m.label }}</option>
                    </select>
                </label>
                <label class="sb-field">
                    <span class="sb-label">Agrégation</span>
                    <select class="sb-input" v-model="s.agg" :disabled="aggs(s).length < 2">
                        <option v-for="a in aggs(s)" :key="a" :value="a">{{ aggLabels[a] }}</option>
                    </select>
                </label>
                <label class="sb-field">
                    <span class="sb-label">Nom affiché</span>
                    <input class="sb-input" v-model="s.label" :placeholder="measure(s).label" />
                </label>
                <label v-if="typeDef.seriesTypes" class="sb-field">
                    <span class="sb-label">Rendu</span>
                    <select class="sb-input" v-model="s.type">
                        <option value="">Auto</option>
                        <option value="bar">Bâtons</option>
                        <option value="line">Courbe</option>
                    </select>
                </label>
            </div>
            <div class="sb-series-foot">
                <div v-if="!['pie', 'donut'].includes(config.type)" class="sb-swatches">
                    <button
                        v-for="c in palette"
                        :key="c"
                        type="button"
                        class="sb-swatch"
                        :class="{ 'is-on': s.color === c }"
                        :style="{ background: c }"
                        @click="s.color = s.color === c ? '' : c"
                    ></button>
                </div>
                <span v-else></span>
                <button v-if="config.series.length > 1" type="button" class="sb-btn-icon is-danger" data-tip="Retirer" @click="config.series.splice(i, 1)">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </div>

        <button v-if="canAdd" type="button" class="sb-btn-dashed" @click="add">
            <i class="fa fa-plus"></i> Ajouter une série
        </button>
    </section>
</template>

<script>
import { AGG_LABELS, PALETTE, WIDGET_TYPES } from "../registry";

export default {
    props: {
        config: { type: Object, required: true },
        source: { type: Object, required: true },
    },

    data() {
        return { aggLabels: AGG_LABELS, palette: PALETTE };
    },

    computed: {
        typeDef() {
            return WIDGET_TYPES[this.config.type];
        },
        single() {
            return !!this.typeDef.singleSeries || this.config.type === "kpi";
        },
        canAdd() {
            return !this.single && this.config.series.length < (this.typeDef.maxSeries || 5);
        },
    },

    methods: {
        measure(s) {
            return this.source.measures.find((m) => m.key === s.field) || this.source.measures[0];
        },
        aggs(s) {
            return this.measure(s).aggs;
        },
        setField(s, field) {
            s.field = field;
            const m = this.measure(s);
            if (!m.aggs.includes(s.agg)) s.agg = m.default;
        },
        add() {
            const used = this.config.series.map((s) => s.field);
            const m = this.source.measures.find((x) => !used.includes(x.key)) || this.source.measures[0];
            this.config.series.push({ field: m.key, agg: m.default, label: "", color: "", type: "" });
        },
    },
};
</script>

<style scoped>
.sb-series {
    border: 1px solid var(--sb-border);
    border-radius: 12px;
    padding: 12px;
    margin-bottom: 8px;
    background: var(--sb-bg-soft);
}
.sb-series-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; }
.sb-series-foot { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; }
.sb-swatches { display: flex; gap: 6px; flex-wrap: wrap; }
.sb-swatch { width: 18px; height: 18px; border-radius: 50%; border: 2px solid transparent; cursor: pointer; padding: 0; }
.sb-swatch.is-on { border-color: var(--sb-text); box-shadow: 0 0 0 2px var(--sb-surface) inset; }
</style>
