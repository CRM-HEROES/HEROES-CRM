<template>
    <bloc-chart :title="title" :index="index">
        <div class="hc-stat-chart-dates">
            <input type="date" v-model="from" />
            <input type="date" v-model="to" />
        </div>
        <!--div class="hc-stat-chart-resume">
            <div
                class="hc-flex-1 hc-stat-chart-order-metric hc-stat-chart-order-last"
            >
                <div
                    class="hc-stat-chart-order-metric-value"
                    v-text="lastValue"
                ></div>
                <div
                    class="hc-stat-chart-order-metric-date"
                    v-text="from"
                ></div>
            </div>
            <div class="hc-flex-row" style="gap: 8px">
                <div
                    class="hc-stat-chart-order-metric"
                    v-for="item in resume"
                    key="item.name"
                >
                    <div
                        class="hc-stat-chart-order-metric-value"
                        v-text="item.value"
                    ></div>
                    <div
                        class="hc-stat-chart-order-metric-date"
                        v-text="item.name"
                    ></div>
                </div>
            </div>
        </div-->
        <div class="hc-stat-chart-chart">
            <chart-radar :data="chart" />
        </div>
    </bloc-chart>
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
}

.hc-stat-chart-order-metric {
    display: flex;
    flex-direction: column;
    height: 100%;
    align-items: flex-end;
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
    align-items: start;
    justify-content: center;
}

.hc-stat-chart-dates {
    display: flex;
    flex-direction: row;
    gap: 4px;
}

.hc-stat-chart-dates > input {
    border: 1px solid #ccc;
    height: 32px;
    padding: 0 8px;
    width: 110px;
    border-radius: 4px;
    font-size: 12px;
}
</style>

<script>
import ChartRadar from "./chart/Radar.vue";
import BlocChart from "./BlocChart.vue";

export default {
    components: {
        BlocChart,
        ChartRadar,
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

    data() {
        let from = new Date();
        let to = new Date();

        if (
            this.chart &&
            this.chart.datasets &&
            this.chart.datasets.length > 0 &&
            this.chart.datasets[0].data
        ) {
            const keys = Object.keys(this.chart.datasets[0].data);

            if (keys.length > 0) {
                from = keys[0];
                to = keys[keys.length - 1];
            }
        }

        return {
            from,
            to,
        };
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
                        label: "Négatif",
                        data: [12, 19, 3, 5, 2, 3, 45, 3, 19, 25, 26, 13],
                        fill: "+2",
                        borderColor: "#8979FF",
                        backgroundColor: "#8979FF",
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                    {
                        label: "A rappeler",
                        data: [123, 56, 43, 56, 92, 33, 34, 13, 78, 65, 45, 98],
                        fill: "+2",
                        borderColor: "#FF9991",
                        backgroundColor: "#FF9991",
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                    /*{
                        label: "A rappeler",
                        data: [36, 67, 89, 111, 66, 90, 98, 23, 98, 76, 66, 90],
                        fill: "+2",
                        borderColor: "#63CBDB",
                        backgroundColor: "#63CBDB",
                        borderWidth: 1,
                        borderRadius: 4,
                    },*/
                ],
            },
        };
    },

    computed: {
        resume() {
            if (
                !this.chart ||
                !Array.isArray(this.chart.datasets) ||
                this.chart.datasets.length === 0
            ) {
                return [];
            }

            if (this.chart.datasets.length === 1) {
                return [
                    {
                        name: "min",
                        value: this.min,
                    },
                    {
                        name: "max",
                        value: this.max,
                    },
                    {
                        name: "moy",
                        value: this.average,
                    },
                ];
            }

            console.log(this.chart.datasets);
            return this.chart.datasets.map((dataset, i) => {
                const data = Object.values(dataset.data);

                return {
                    name: dataset.label,
                    value: data.length > 0 ? data[data.length - 1] : 0,
                };
            });
        },

        firstDataset() {
            if (
                !this.chart ||
                !Array.isArray(this.chart.datasets) ||
                this.chart.datasets.length == 0
            ) {
                return null;
            }

            return this.chart.datasets[0].data;
        },

        firstDatasetData() {
            if (!this.firstDataset) {
                return [0];
            }

            return Object.values(this.firstDataset);
        },

        max() {
            return this.firstDatasetData.reduce(
                (carry, value) => Math.max(carry, value),
                -10000000
            );
        },

        min() {
            return this.firstDatasetData.reduce(
                (carry, value) => Math.min(carry, value),
                10000000
            );
        },

        average() {
            return (
                this.firstDatasetData.reduce(
                    (carry, value) => carry + value,
                    0
                ) / this.firstDatasetData.length
            ).toFixed(2);
        },

        lastValue() {
            return this.firstDatasetData[this.firstDatasetData.length - 1];
        },
    },
};
</script>
