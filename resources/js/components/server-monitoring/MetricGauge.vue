<template>
    <div class="metric-gauge" :style="{ '--gauge-value': percent + '%', '--gauge-color': color }" role="meter" :aria-label="label" aria-valuemin="0" aria-valuemax="100" :aria-valuenow="available ? percent : undefined" :aria-valuetext="available ? percent + ' %' : 'Indisponible'">
        <div><strong>{{ available ? percent.toLocaleString('fr-FR', { maximumFractionDigits: 1 }) + ' %' : '—' }}</strong><span>{{ label }}</span></div>
    </div>
</template>

<script>
export default {
    props: { value: { default: null }, label: { type: String, required: true } },
    computed: {
        available() { return typeof this.value === "number" && Number.isFinite(this.value); },
        percent() { return this.available ? Math.max(0, Math.min(100, this.value)) : 0; },
        color() { return !this.available ? "#d7dce8" : this.percent >= 90 ? "#dc4960" : this.percent >= 80 ? "#ecad30" : "#7939b8"; },
    },
};
</script>

<style scoped>
.metric-gauge { width: 108px; height: 108px; flex-shrink: 0; border-radius: 50%; padding: 8px; background: conic-gradient(var(--gauge-color) var(--gauge-value), #edf0f6 0); }
.metric-gauge > div { background: white; height: 100%; border-radius: 50%; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.metric-gauge strong { font-size: 19px; color: #26334e; }
.metric-gauge span { color: #788297; font-size: 11px; margin-top: 2px; }
</style>
