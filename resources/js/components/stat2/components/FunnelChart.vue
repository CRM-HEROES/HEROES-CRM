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
                v-if="chart && chart.datasets && chart.datasets.length > 0"
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
            <div class="hc-stat-chart-chart" style="padding: 32px 32px 0 32px">
                <chart-funnel :data="chart" />
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
import ChartFunnel from "./chart/Funnel.vue";
import BlocChart from "./BlocChart.vue";

export default {
    components: {
        BlocChart,
        ChartFunnel,
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

    created() {
        this.data = {
            last: {
                value: 123,
                date: "17 Janvier 2025",
            },
            min: 10,
            max: 143,
            moy: 67,
            chart: {
                labels: [
                    "A rappeler",
                    "Devis envoyé",
                    "Facture émise",
                    "Signé",
                ],
                datasets: [
                    {
                        data: [150, 89, 45, 7],
                        backgroundColor: [
                            "rgb(255, 99, 132)",
                            "rgb(54, 162, 235)",
                            "rgb(255, 79, 55)",
                            "rgb(0, 201, 76)",
                        ],
                        borderWidth: 0,
                    },
                ],
            },
        };
    },

    methods: {},
};
</script>
