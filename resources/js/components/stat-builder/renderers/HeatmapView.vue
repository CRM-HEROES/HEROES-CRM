<template>
    <div class="sb-heat">
        <div class="sb-heat-scroll">
            <div class="sb-heat-grid" :style="{ gridTemplateColumns: `repeat(${weeks.length}, 14px)` }">
                <div v-for="(week, wi) in weeks" :key="wi" class="sb-heat-col">
                    <div
                        v-for="(cell, ci) in week"
                        :key="ci"
                        class="sb-heat-cell"
                        :class="{ 'is-empty': !cell }"
                        :style="cell ? { background: shade(cell.value) } : null"
                        :data-tip="cell ? `${cell.label} : ${fmt(cell.value)}` : null"
                    ></div>
                </div>
            </div>
        </div>
        <div class="sb-heat-legend">
            Moins
            <span v-for="a in [0.08, 0.3, 0.55, 0.8, 1]" :key="a" class="sb-heat-cell" :style="{ background: tint(a) }"></span>
            Plus
        </div>
    </div>
</template>

<script>
import { formatValue, hexToRgba } from "../format";

export default {
    props: {
        data: { type: Object, required: true },
        config: { type: Object, default: () => ({}) },
    },

    computed: {
        color() {
            return this.config.color || "#6366f1";
        },
        max() {
            const d = (this.data.series[0] && this.data.series[0].data) || [];
            return Math.max(1, ...d.map(Number));
        },
        // Colonnes = semaines (lundi → dimanche)
        weeks() {
            const labels = this.data.labels || [];
            const values = (this.data.series[0] && this.data.series[0].data) || [];
            if (!labels.length) return [];

            const weeks = [];
            let current = new Array(7).fill(null);

            labels.forEach((l, i) => {
                const d = new Date(l + "T00:00:00");
                const wd = (d.getDay() + 6) % 7;
                if (wd === 0 && current.some(Boolean)) {
                    weeks.push(current);
                    current = new Array(7).fill(null);
                }
                current[wd] = {
                    value: values[i] || 0,
                    label: d.toLocaleDateString("fr-FR", { weekday: "short", day: "2-digit", month: "short" }),
                };
            });
            weeks.push(current);

            return weeks;
        },
    },

    methods: {
        fmt(v) {
            return formatValue(v, this.data.format);
        },
        tint(a) {
            return hexToRgba(this.color, a);
        },
        shade(v) {
            if (!v) return "rgba(148,163,184,0.14)";
            return hexToRgba(this.color, 0.2 + 0.8 * (v / this.max));
        },
    },
};
</script>

<style scoped>
.sb-heat {
    display: flex;
    flex-direction: column;
    height: 100%;
    gap: 8px;
}
.sb-heat-scroll {
    flex: 1;
    overflow: auto;
    display: flex;
    align-items: center;
}
.sb-heat-grid {
    display: grid;
    gap: 3px;
    margin: 0 auto;
}
.sb-heat-col {
    display: grid;
    grid-template-rows: repeat(7, 14px);
    gap: 3px;
}
.sb-heat-cell {
    width: 14px;
    height: 14px;
    border-radius: 3px;
    display: inline-block;
}
.sb-heat-cell.is-empty {
    background: transparent;
}
.sb-heat-legend {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 4px;
    font-size: 11px;
    color: var(--sb-muted);
}
</style>
