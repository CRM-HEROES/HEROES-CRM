<template>
    <div class="sb-kpi">
        <div class="sb-kpi-main">
            <div class="sb-kpi-label">{{ label }}</div>
            <div class="sb-kpi-value">{{ value }}</div>
            <div v-if="data.change !== null && data.change !== undefined" class="sb-kpi-change" :class="trend">
                <i class="fa" :class="data.change >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'"></i>
                {{ data.change > 0 ? "+" : "" }}{{ data.change }}%
                <span class="sb-kpi-vs">vs période précédente</span>
            </div>
        </div>
        <div class="sb-kpi-icon" :style="{ background: tint, color: color }">
            <i class="fa" :class="config.icon || 'fa-chart-line'"></i>
        </div>
    </div>
</template>

<script>
import { formatValue, hexToRgba } from "../format";

export default {
    props: {
        data: { type: Object, required: true },
        config: { type: Object, default: () => ({}) },
        label: { type: String, default: "" },
    },

    computed: {
        color() {
            return this.config.color || "#6366f1";
        },
        tint() {
            return hexToRgba(this.color, 0.12);
        },
        value() {
            return formatValue(this.data.value, this.data.format);
        },
        trend() {
            if (!this.data.change) return "is-flat";
            const positive = this.data.change > 0;
            const good = this.config.positiveIsGood === false ? !positive : positive;
            return good ? "is-good" : "is-bad";
        },
    },
};
</script>

<style scoped>
.sb-kpi {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    height: 100%;
}
.sb-kpi-main {
    min-width: 0;
}
.sb-kpi-label {
    font-size: 12px;
    color: var(--sb-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.sb-kpi-value {
    font-size: clamp(22px, 3vw, 30px);
    font-weight: 700;
    color: var(--sb-text);
    line-height: 1.15;
    letter-spacing: -0.02em;
}
.sb-kpi-change {
    margin-top: 4px;
    font-size: 12px;
    font-weight: 600;
}
.sb-kpi-change.is-good { color: #059669; }
.sb-kpi-change.is-bad { color: #dc2626; }
.sb-kpi-change.is-flat { color: var(--sb-muted); }
.sb-kpi-vs {
    font-weight: 400;
    color: var(--sb-muted);
    margin-left: 2px;
}
.sb-kpi-icon {
    flex: none;
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}
@media (max-width: 480px) {
    .sb-kpi-vs { display: none; }
}
</style>
