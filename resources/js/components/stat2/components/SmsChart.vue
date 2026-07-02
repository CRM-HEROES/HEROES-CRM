<template>
    <bloc-chart title="Nouveaux SMS">
        <div
            style="
                display: flex;
                flex-direction: column;
                height: 100%;
                align-items: center;
            "
        >
            <div class="hc-stat-chart-resume">
                <div
                    class="hc-stat-chart-order-metric hc-stat-chart-order-last"
                >
                    <div class="hc-stat-chart-order-metric-value">75</div>
                    <div class="hc-stat-chart-order-metric-date">
                        17 Janvier 2025
                    </div>
                </div>
                <div class="hc-flex-row" style="gap: 8px">
                    <div class="hc-stat-chart-order-metric">
                        <div class="hc-stat-chart-order-metric-value">10</div>
                        <div class="hc-stat-chart-order-metric-date">min</div>
                    </div>
                    <div class="hc-stat-chart-order-metric">
                        <div class="hc-stat-chart-order-metric-value">143</div>
                        <div class="hc-stat-chart-order-metric-date">max</div>
                    </div>
                    <div class="hc-stat-chart-order-metric">
                        <div class="hc-stat-chart-order-metric-value">67</div>
                        <div class="hc-stat-chart-order-metric-date">moy</div>
                    </div>
                </div>
            </div>
            <div class="hc-stat-chart-chart">
                <chart-bar :data="data.chart" />
            </div>
        </div>
    </bloc-chart>
</template>

<style scoped>
.hc-stat-chart-resume {
    display: flex;
    flex-direction: column;
    /* width: 100%; */
    padding: 8px 0;
    justify-content: center;
    align-items: center;
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
    align-items: center;
    justify-content: center;
}
</style>

<script>
import BlocChart from "./BlocChart.vue";
import ChartBar from "./chart/Bar.vue";

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
                        label: "SMS",
                        data: [12, 19, 3, 5, 2, 3, 17, 19, 23, 15, 9, 6],
                        fill: true,
                        borderWidth: 0,
                        backgroundColor: "#CDEDF5",
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
