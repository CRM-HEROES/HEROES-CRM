<template>
    <div class="sb-chart">
        <canvas ref="canvas"></canvas>
    </div>
</template>

<script>
import ChartJsInit from "@/utils/chart";
import { PALETTE } from "../registry";
import { formatValue, formatNumber, hexToRgba } from "../format";

/**
 * Affiche les valeurs sur les barres / points / parts (sans dépendance externe).
 */
const valueLabels = {
    id: "sbValueLabels",
    afterDatasetsDraw(chart, args, opts) {
        if (!opts || !opts.enabled) return;
        const { ctx } = chart;
        ctx.save();
        ctx.font = "600 11px Inter, system-ui, sans-serif";
        ctx.textAlign = "center";
        ctx.textBaseline = "middle";

        chart.data.datasets.forEach((ds, di) => {
            const meta = chart.getDatasetMeta(di);
            if (meta.hidden) return;
            const total = ds.data.reduce((a, b) => a + (Number(b) || 0), 0);

            meta.data.forEach((el, i) => {
                const v = Number(ds.data[i]);
                if (!v) return;
                const pos = el.tooltipPosition();
                let text = formatValue(v, opts.format);
                const isArc = meta.type === "doughnut" || meta.type === "pie";

                if (isArc) {
                    const pct = total ? (v / total) * 100 : 0;
                    if (pct < 4) return;
                    text = opts.percent ? `${Math.round(pct)}%` : formatNumber(v);
                    ctx.fillStyle = "#fff";
                    ctx.fillText(text, pos.x, pos.y);
                    return;
                }

                const horizontal = chart.options.indexAxis === "y";
                ctx.fillStyle = "#475569";
                if (meta.type === "bar" && horizontal) {
                    ctx.textAlign = "left";
                    ctx.fillText(text, el.x + 4, el.y);
                    ctx.textAlign = "center";
                } else {
                    ctx.fillText(text, el.x, el.y - 10);
                }
            });
        });
        ctx.restore();
    },
};

/**
 * Total au centre d'un donut.
 */
const centerText = {
    id: "sbCenterText",
    afterDraw(chart, args, opts) {
        if (!opts || !opts.text) return;
        const { ctx, chartArea: a } = chart;
        const x = (a.left + a.right) / 2;
        const y = (a.top + a.bottom) / 2;
        ctx.save();
        ctx.textAlign = "center";
        ctx.textBaseline = "middle";
        ctx.fillStyle = "#0f172a";
        ctx.font = "700 22px Inter, system-ui, sans-serif";
        ctx.fillText(opts.text, x, y - 6);
        ctx.fillStyle = "#94a3b8";
        ctx.font = "500 11px Inter, system-ui, sans-serif";
        ctx.fillText("Total", x, y + 14);
        ctx.restore();
    },
};

export default {
    props: {
        type: { type: String, required: true },
        data: { type: Object, required: true },
        options: { type: Object, default: () => ({}) },
    },

    data() {
        return { chart: null, destroyed: false };
    },

    computed: {
        isPie() {
            return this.type === "pie" || this.type === "donut";
        },
    },

    mounted() {
        ChartJsInit()
            .then(() => {
                if (!this.destroyed) this.render();
            })
            .catch((e) => console.error("Chart.js load error", e));
    },

    beforeUnmount() {
        this.destroyed = true;
        if (this.chart) this.chart.destroy();
    },

    watch: {
        data: { handler() { this.render(); }, deep: true },
        options: { handler() { this.render(); }, deep: true },
        type() { this.render(); },
    },

    methods: {
        color(i, s) {
            return (s && s.color) || PALETTE[i % PALETTE.length];
        },

        build() {
            const o = this.options;
            const d = this.data;
            const t = this.type;
            const labels = d.labels;
            const fmt = d.format;

            let cfgType = "bar";
            let datasets;

            if (this.isPie) {
                cfgType = t === "donut" ? "doughnut" : "pie";
                const s = d.series[0] || { data: [] };
                datasets = [
                    {
                        label: s.label,
                        data: s.data,
                        backgroundColor: labels.map((_, i) => PALETTE[i % PALETTE.length]),
                        borderColor: "#fff",
                        borderWidth: 2,
                        hoverOffset: 6,
                    },
                ];
            } else {
                datasets = d.series.map((s, i) => {
                    const c = this.color(i, s);
                    let kind = "bar";
                    if (t === "line" || t === "multiline") kind = "line";
                    if (t === "combo") kind = s.type || (i === 0 ? "bar" : "line");

                    const base = { label: s.label, data: s.data, type: kind, borderColor: c, backgroundColor: c, order: kind === "line" ? 0 : 1 };

                    if (kind === "line") {
                        Object.assign(base, {
                            tension: o.smooth === false ? 0 : 0.35,
                            fill: t === "line" && i === 0 && !s.dashed,
                            backgroundColor: t === "line" ? hexToRgba(c.startsWith("#") ? c : "#6366f1", 0.12) : c,
                            pointRadius: s.data.length > 40 ? 0 : 3,
                            pointHoverRadius: 5,
                            borderWidth: 2,
                            borderDash: s.dashed ? [6, 4] : [],
                        });
                        if (s.dashed) {
                            base.borderColor = "#94a3b8";
                            base.backgroundColor = "#94a3b8";
                            base.fill = false;
                        }
                    } else {
                        Object.assign(base, { borderRadius: 4, maxBarThickness: 40 });
                    }

                    return base;
                });
            }

            const horizontal = t === "hbar";
            const gridColor = "rgba(148,163,184,0.18)";

            return {
                type: cfgType,
                data: { labels, datasets },
                plugins: [valueLabels, centerText],
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: horizontal ? "y" : "x",
                    interaction: { mode: this.isPie ? "nearest" : "index", intersect: false },
                    animation: { duration: 450 },
                    cutout: t === "donut" ? "66%" : 0,
                    layout: { padding: { top: o.showValues ? 16 : 4, right: horizontal && o.showValues ? 30 : 4 } },
                    plugins: {
                        legend: {
                            display: !!o.legend,
                            position: "bottom",
                            labels: { usePointStyle: true, boxWidth: 8, font: { size: 11 }, color: "#64748b" },
                        },
                        tooltip: {
                            backgroundColor: "#0f172a",
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: (ctx) => {
                                    const v = ctx.parsed && typeof ctx.parsed === "object" ? (horizontal ? ctx.parsed.x : ctx.parsed.y) : ctx.parsed;
                                    let text = `${ctx.dataset.label ? ctx.dataset.label + " : " : ""}${formatValue(v, fmt)}`;
                                    if (this.isPie) {
                                        const total = ctx.dataset.data.reduce((a, b) => a + (Number(b) || 0), 0);
                                        text += total ? ` (${Math.round((v / total) * 100)}%)` : "";
                                    }
                                    return text;
                                },
                            },
                        },
                        sbCenterText: {
                            text: t === "donut" ? formatValue((d.series[0] ? d.series[0].data : []).reduce((a, b) => a + (Number(b) || 0), 0), fmt) : "",
                        },
                        sbValueLabels: {
                            enabled: !!(o.showValues || (this.isPie && o.showLabels)),
                            percent: this.isPie ? o.showPercent !== false : false,
                            format: fmt,
                        },
                    },
                    scales: this.isPie
                        ? {}
                        : {
                              x: {
                                  stacked: !!o.stacked,
                                  grid: { display: horizontal, color: gridColor },
                                  ticks: { color: "#94a3b8", font: { size: 11 }, maxRotation: 0, autoSkip: true, maxTicksLimit: horizontal ? 12 : 8, callback: horizontal ? (v) => formatValue(v, fmt) : undefined },
                                  border: { display: false },
                              },
                              y: {
                                  stacked: !!o.stacked,
                                  beginAtZero: true,
                                  grid: { color: gridColor },
                                  ticks: { color: "#94a3b8", font: { size: 11 }, precision: 0, maxTicksLimit: horizontal ? 12 : 6, callback: horizontal ? undefined : (v) => formatValue(v, fmt) },
                                  border: { display: false },
                              },
                          },
                },
            };
        },

        render() {
            if (!window.Chart || !this.$refs.canvas) return;
            if (this.chart) {
                this.chart.destroy();
                this.chart = null;
            }
            this.chart = new window.Chart(this.$refs.canvas, this.build());
        },
    },
};
</script>

<style scoped>
.sb-chart {
    position: relative;
    width: 100%;
    height: 100%;
    min-height: 0;
}
</style>
