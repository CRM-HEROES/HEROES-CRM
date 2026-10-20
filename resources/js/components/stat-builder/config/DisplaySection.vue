<template>
    <section class="sb-section">
        <div class="sb-section-title">Affichage</div>

        <div class="sb-row">
            <label class="sb-field sb-grow">
                <span class="sb-label">Titre</span>
                <input class="sb-input" v-model="config.title" placeholder="Ex. Performance des utilisateurs" maxlength="120" />
            </label>
            <label class="sb-field sb-grow">
                <span class="sb-label">Sous-titre</span>
                <input class="sb-input" v-model="config.subtitle" placeholder="Optionnel" maxlength="160" />
            </label>
        </div>

        <div class="sb-switches">
            <label v-if="has('legend')" class="sb-check"><input type="checkbox" v-model="config.legend" /><span>Légende</span></label>
            <label v-if="has('values')" class="sb-check"><input type="checkbox" v-model="config.showValues" /><span>Afficher les valeurs</span></label>
            <label v-if="has('labels')" class="sb-check"><input type="checkbox" v-model="config.showLabels" /><span>Afficher les labels</span></label>
            <label v-if="has('percent')" class="sb-check"><input type="checkbox" v-model="config.showPercent" /><span>Afficher en pourcentage</span></label>
            <label v-if="has('stacked')" class="sb-check"><input type="checkbox" v-model="config.stacked" /><span>Empiler les séries</span></label>
            <label v-if="has('smooth')" class="sb-check"><input type="checkbox" v-model="config.smooth" /><span>Courbe lissée</span></label>
        </div>

        <template v-if="config.type === 'heatmap'">
            <div class="sb-label" style="margin-top: 10px">Couleur</div>
            <div class="sb-swatches">
                <button
                    v-for="c in palette"
                    :key="c"
                    type="button"
                    class="sb-swatch"
                    :class="{ 'is-on': config.color === c }"
                    :style="{ background: c }"
                    @click="config.color = c"
                ></button>
            </div>
        </template>

        <div class="sb-subtitle">Filtres globaux pris en compte</div>
        <div class="sb-switches">
            <label v-for="f in globalFilters" :key="f.key" class="sb-check">
                <input type="checkbox" :checked="respects(f.key)" @change="toggle(f.key, $event.target.checked)" />
                <span>{{ f.label }}</span>
            </label>
        </div>
    </section>
</template>

<script>
import { PALETTE } from "../registry";

const FEATURES = {
    legend: ["bar", "hbar", "line", "multiline", "combo", "pie", "donut"],
    values: ["bar", "hbar", "line", "multiline", "combo"],
    labels: ["pie", "donut"],
    percent: ["pie", "donut", "funnel"],
    stacked: ["bar"],
    smooth: ["line", "multiline"],
};

export default {
    props: {
        config: { type: Object, required: true },
        globalFilters: { type: Array, required: true },
    },

    data() {
        return { palette: PALETTE };
    },

    methods: {
        has(f) {
            return FEATURES[f].includes(this.config.type);
        },
        respects(key) {
            return (this.config.globalFilters || []).includes(key);
        },
        toggle(key, on) {
            const list = (this.config.globalFilters || []).filter((k) => k !== key);
            if (on) list.push(key);
            this.config.globalFilters = list;
        },
    },
};
</script>

<style scoped>
.sb-grow { flex: 1 1 220px; }
.sb-switches { display: flex; gap: 8px 20px; flex-wrap: wrap; margin-top: 10px; }
.sb-subtitle { font-size: 12px; font-weight: 600; color: var(--sb-muted); margin: 18px 0 2px; }
.sb-swatches { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 6px; }
.sb-swatch { width: 24px; height: 24px; border-radius: 50%; border: 2px solid transparent; cursor: pointer; padding: 0; }
.sb-swatch.is-on { border-color: var(--sb-text); box-shadow: 0 0 0 2px var(--sb-surface) inset; }
</style>
