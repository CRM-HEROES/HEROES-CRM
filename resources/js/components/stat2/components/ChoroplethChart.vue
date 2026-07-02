<template>
    <bloc-chart title="Prospects par pays" :index="index">
        <div
            class="hc-stat-chart-chart"
            style="display: flex; flex-direction: row"
        >
            <chart-choropleth :data="data.data" /></div
    ></bloc-chart>
</template>

<style scoped>
.hc-stat-chart-resume {
    display: flex;
    flex-direction: row;
    width: 100%;
    padding: 8px 0;
}

.hc-stat-chart-chart {
    width: 100%;
    flex: 1;
    border-radius: 4px;
    overflow: hidden;
    display: flex;
    justify-content: center;
}

.hc-stat-chart-chart > canvas {
    width: 100%;
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
</style>

<script>
import ChartChoropleth from "./chart/Choropleth.vue";
import BlocChart from "./BlocChart.vue";

export default {
    components: {
        BlocChart,
        ChartChoropleth,
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
            data: {
                "2025-01-01": 10,
                "2025-01-02": 20,
                "2025-01-03": 30,
                "2025-01-04": 40,
                "2025-01-05": 23,
                "2025-01-06": 45,
                "2025-01-07": 67,
                "2025-01-08": 78,
                "2025-01-09": 89,
                "2025-01-10": 90,
                "2025-01-11": 100,
                "2025-01-12": 110,
                "2025-01-13": 120,
                "2025-01-14": 130,
                "2025-01-15": 40,
                "2025-01-16": 50,
                "2025-01-17": 33,
                "2025-01-18": 45,
                "2025-01-19": 67,
                "2025-01-20": 78,
                "2025-01-21": 34,
                "2025-01-22": 45,
                "2025-01-23": 67,
            },
        };
    },

    methods: {},
};
</script>
