<template>
    <div class="sb-funnel">
        <div v-for="(s, i) in data.steps" :key="i" class="sb-funnel-row">
            <div class="sb-funnel-info">
                <div class="sb-funnel-label">{{ s.label }}</div>
                <div class="sb-funnel-value">{{ fmt(s.value) }}</div>
            </div>
            <div class="sb-funnel-track">
                <div
                    class="sb-funnel-bar"
                    :style="{ width: Math.max(s.rate, 3) + '%', background: colors[i % colors.length] }"
                    :data-tip="`${s.rate}% du départ`"
                ></div>
            </div>
            <div v-if="config.showPercent !== false" class="sb-funnel-rate" :data-tip="i ? 'Conversion depuis l\'étape précédente' : 'Point de départ'">
                {{ i === 0 ? "100%" : s.rate_prev + "%" }}
            </div>
        </div>
    </div>
</template>

<script>
import { PALETTE } from "../registry";
import { formatNumber } from "../format";

export default {
    props: {
        data: { type: Object, required: true },
        config: { type: Object, default: () => ({}) },
    },
    data() {
        return { colors: PALETTE };
    },
    methods: {
        fmt: formatNumber,
    },
};
</script>

<style scoped>
.sb-funnel {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 10px;
    height: 100%;
    overflow: auto;
}
.sb-funnel-row {
    display: grid;
    grid-template-columns: minmax(80px, 28%) 1fr 52px;
    align-items: center;
    gap: 10px;
}
.sb-funnel-label {
    font-size: 12px;
    color: var(--sb-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.sb-funnel-value {
    font-size: 16px;
    font-weight: 700;
    color: var(--sb-text);
}
.sb-funnel-track {
    height: 30px;
    display: flex;
    justify-content: center;
}
.sb-funnel-bar {
    height: 100%;
    border-radius: 6px;
    transition: width 0.5s ease;
    opacity: 0.92;
}
.sb-funnel-rate {
    font-size: 12px;
    font-weight: 600;
    color: var(--sb-text);
    text-align: right;
}
</style>
