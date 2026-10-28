<template>
    <div class="monitoring-chart">
        <canvas ref="canvas" role="img" :aria-label="label"></canvas>
        <p v-if="error" class="chart-error">Graphique indisponible. Les valeurs restent consultables dans les cartes.</p>
    </div>
</template>

<script>
import { markRaw } from "vue";
import ChartJsInit from "@/utils/chart";

export default {
    props: {
        label: { type: String, required: true },
        samples: { type: Array, default: () => [] },
        series: { type: Array, required: true },
        maximum: { type: Number, default: undefined },
        unit: { type: String, default: "" },
    },
    data: () => ({ chart: null, destroyed: false, error: false }),
    mounted() {
        ChartJsInit().then(() => {
            if (!this.destroyed) this.render();
        }).catch(() => { this.error = true; });
    },
    beforeUnmount() {
        this.destroyed = true;
        if (this.chart) this.chart.destroy();
    },
    watch: {
        samples() { this.render(); },
    },
    methods: {
        render() {
            if (!window.Chart || !this.$refs.canvas || this.destroyed) return;
            const data = {
                labels: this.samples.map(sample => new Date(sample.time).toLocaleTimeString("fr-FR")),
                datasets: this.series.map(series => ({
                    label: series.label,
                    data: this.samples.map(sample => sample[series.key] ?? null),
                    borderColor: series.color,
                    backgroundColor: series.color + "14",
                    pointRadius: 0,
                    pointHoverRadius: 4,
                    borderWidth: 2,
                    tension: 0.2,
                    fill: false,
                    spanGaps: false,
                })),
            };
            if (this.chart) {
                this.chart.data = data;
                this.chart.update("none");
                return;
            }
            this.chart = markRaw(new window.Chart(this.$refs.canvas, {
                type: "line",
                data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    interaction: { mode: "index", intersect: false },
                    plugins: {
                        legend: { position: "bottom", labels: { usePointStyle: true, boxWidth: 8, font: { size: 11 } } },
                        tooltip: { callbacks: { label: ctx => `${ctx.dataset.label} : ${Number(ctx.parsed.y).toLocaleString("fr-FR", { maximumFractionDigits: 2 })} ${this.unit}` } },
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { maxTicksLimit: 6, maxRotation: 0, font: { size: 10 } } },
                        y: { beginAtZero: true, max: this.maximum, grid: { color: "#e9edf5" }, ticks: { maxTicksLimit: 5, callback: value => `${value} ${this.unit}`, font: { size: 10 } } },
                    },
                },
            }));
        },
    },
};
</script>

<style scoped>
.monitoring-chart { height: 225px; position: relative; width: 100%; }
.chart-error { color: #8b5264; font-size: 12px; padding: 20px; position: absolute; inset: 0; }
</style>
