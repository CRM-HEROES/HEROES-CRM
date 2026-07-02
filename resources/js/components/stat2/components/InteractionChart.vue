<template>
    <bloc-chart title="Nouveaux appels">
        <div class="hc-stat-chart-resume">
            <div class="hc-stat-chart-order-metric hc-stat-chart-order-last">
                <div class="hc-stat-chart-order-metric-value">123</div>
                <div class="hc-stat-chart-order-metric-date">
                    17 Janvier 2025
                </div>
            </div>
            <div class="hc-flex-row" style="gap: 8px">
                <div class="hc-stat-chart-order-metric">
                    <div class="hc-stat-chart-order-metric-value">2</div>
                    <div class="hc-stat-chart-order-metric-date">min</div>
                </div>
                <div class="hc-stat-chart-order-metric">
                    <div class="hc-stat-chart-order-metric-value">10</div>
                    <div class="hc-stat-chart-order-metric-date">max</div>
                </div>
                <div class="hc-stat-chart-order-metric">
                    <div class="hc-stat-chart-order-metric-value">6</div>
                    <div class="hc-stat-chart-order-metric-date">moy</div>
                </div>
                <div class="hc-stat-chart-order-metric">
                    <div class="hc-stat-chart-order-metric-value">23 mn</div>
                    <div class="hc-stat-chart-order-metric-date">Durée</div>
                </div>
            </div>
        </div>
        <div class="hc-stat-chart-chart">
            <chart-bar :data="data.chart" />
        </div>
    </bloc-chart>
</template>

<style scoped>
.hc-stat-chart-resume {
    display: flex;
    flex-direction: row;
    /* width: 100%; */
    padding: 8px 0;
    /* justify-content: center; */
    /* align-items: center; */
    padding: 0 8px;
    gap: 16px;
}

.hc-stat-chart-chart {
    width: 100%;
    flex: 1;
    border-radius: 4px;
}

.hc-stat-chart-order-metric {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.hc-stat-chart-order-metric-value {
    font-size: 18px;
    font-weight: bold;
    color: #333;
    white-space: nowrap;
}

.hc-stat-chart-order-metric-date {
    font-size: 12px;
    color: #999;
    margin-left: 4px;
}

.hc-stat-chart-order-last {
    flex: 1;
    align-items: flex-start;
}
</style>

<script>
import ChartBar from "./chart/Bar.vue";
import BlocChart from "./BlocChart.vue";

export default {
    components: {
        BlocChart,
        ChartBar,
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
                    "Jan",
                    "Fev",
                    "Mar",
                    "Avr",
                    "Mai",
                    "Jui",
                    "Jui",
                    "Aou",
                    "Sep",
                    "Oct",
                    "Nov",
                    "Dec",
                ],
                datasets: [
                    {
                        label: "Appel",
                        data: [45, 56, 37, 95, 2, 13, 37, 59, 23, 32, 29, 36],
                        fill: true,
                        borderWidth: 0,
                        backgroundColor: "#FFAE4C",
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                ],
            },
        };
    },

    methods: {},
};
</script>
