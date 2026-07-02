<template>
    <bloc-chart :title="title" :index="index">
        <div
            style="
                display: flex;
                flex-direction: column;
                height: 100%;
                align-items: center;
            "
        >
            <div
                class="hc-stat-chart-resume"
                style="height: 90px; flex-1"
                v-if="chart.datasets.length > 0"
            >
                <div class="hc-stat-label" v-for="(label, i) in chart.labels">
                    <div
                        :class="['hc-stat-label-icon', 'fa', 'fa-circle']"
                        :style="{
                            color: chart.datasets[0].backgroundColor[i],
                        }"
                    ></div>
                    <div class="hc-stat-label-name" v-text="label"></div>
                    <div
                        class="hc-stat-label-count"
                        v-text="chart.datasets[0].data[i]"
                    ></div>
                </div>
            </div>
            <div class="hc-stat-chart-chart" style="padding: 0 32px">
                <chart-half-pie :data="chart" />
            </div>
        </div>
    </bloc-chart>
</template>

<style scoped>
.hc-stat-chart-resume {
    display: flex;
    flex-direction: row;
    padding: 16px 0;
    width: 100%;
    flex-wrap: wrap;
}

.hc-stat-chart-chart {
    width: 100%;
    flex: 1;
    border-radius: 4px;
    overflow: hidden;
}

.hc-stat-chart-chart > canvas {
    height: 100%;
}

.hc-stat-chart-order-metric {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.hc-stat-chart-order-metric-value {
    font-size: 14px;
    font-weight: bold;
    color: #333;
}

.hc-stat-chart-order-metric-date {
    font-size: 12px;
    color: #999;
    margin-left: 4px;
}

.hc-stat-chart-order-last {
    flex: 1;
}

.hc-stat-label {
    display: flex;
    flex-direction: row;
    gap: 7px;
    align-items: center;
    width: 50%;
    border-radius: 3px;
    cursor: pointer;
    padding: 2px 8px;
}

.hc-stat-label:hover {
    background-color: #eeeeee;
}

.hc-stat-label-icon {
    font-size: 12px;
    color: #cccccc;
}

.hc-stat-label-icon.mapped {
    color: #2f7ebc;
}

.hc-stat-label-name {
    font-size: 11px;
    flex: 1;
    padding-right: 8px;
    white-space: nowrap;
    overflow: hidden;
}

.hc-stat-label-count {
    font-size: 12px;
    color: #777777;
}
</style>

<script>
import ChartPie from "./chart/Pie.vue";
import ChartHalfPie from "./chart/HalfPie.vue";
import BlocChart from "./BlocChart.vue";

export default {
    components: {
        ChartPie,
        ChartHalfPie,
        BlocChart,
    },

    props: {
        title: {
            type: String,
        },
        index: {
            type: String,
        },
        chart: {
            type: Object,
        },
    },
};
</script>
