<template>
    <section class="sb-section">
        <div class="sb-section-title">Indicateur (KPI)</div>

        <div class="sb-row">
            <label class="sb-field">
                <span class="sb-label">Valeur de comparaison</span>
                <select class="sb-input" v-model="config.comparison">
                    <option value="previous_period">Période précédente</option>
                    <option value="none">Aucune</option>
                </select>
            </label>
            <label class="sb-field">
                <span class="sb-label">Évolution</span>
                <select class="sb-input" v-model="config.positiveIsGood">
                    <option :value="true">Une hausse est positive</option>
                    <option :value="false">Une hausse est négative</option>
                </select>
            </label>
        </div>

        <div class="sb-label" style="margin-top: 12px">Icône</div>
        <div class="sb-icons">
            <button
                v-for="ic in icons"
                :key="ic"
                type="button"
                class="sb-icon-pick"
                :class="{ 'is-on': config.icon === ic }"
                @click="config.icon = ic"
            >
                <i class="fa" :class="ic"></i>
            </button>
        </div>

        <div class="sb-label" style="margin-top: 12px">Couleur</div>
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
    </section>
</template>

<script>
import { KPI_ICONS, PALETTE } from "../registry";

export default {
    props: { config: { type: Object, required: true } },

    data() {
        return { icons: KPI_ICONS, palette: PALETTE };
    },
};
</script>

<style scoped>
.sb-icons { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
.sb-icon-pick {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    border: 1.5px solid var(--sb-border);
    background: var(--sb-surface);
    color: var(--sb-muted);
    cursor: pointer;
}
.sb-icon-pick:hover { border-color: var(--sb-accent); }
.sb-icon-pick.is-on { border-color: var(--sb-accent); background: var(--sb-accent-soft); color: var(--sb-accent); }
.sb-swatches { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 6px; }
.sb-swatch { width: 24px; height: 24px; border-radius: 50%; border: 2px solid transparent; cursor: pointer; padding: 0; }
.sb-swatch.is-on { border-color: var(--sb-text); box-shadow: 0 0 0 2px var(--sb-surface) inset; }
</style>
