<template>
    <bloc-chart :title="title" :index="index">
        <div class="hc-stat-chart-dates">
            <input type="date" v-model="dateValue" />
        </div>
        <div class="hc-stat-chart-resume">
            <div
                class="hc-flex-1 hc-stat-chart-order-metric hc-stat-chart-order-last"
            >
                <div
                    class="hc-stat-chart-order-metric-value"
                    v-text="lastValue"
                ></div>
                <div
                    class="hc-stat-chart-order-metric-date"
                    v-text="date"
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
        </div>
        <div
            class="hc-stat-chart-chart"
            style="display: flex; flex-direction: row"
        >
            <chart-calendar
                :data="_chart.datasets[0].data"
                :month="1"
                :yaer="2025"
                :border-color="chart.datasets[0].backgroundColor"
                :fill-color="chart.datasets[0].backgroundColor"
            />
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
    align-items: flex-end;
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
    align-items: flex-start;
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
import { mapGetters } from "vuex";
import ApiService from "@/apis/api.service";

import ChartCalendar from "./chart/Calendar.vue";
import BlocChart from "./BlocChart.vue";

export default {
    components: {
        BlocChart,
        ChartCalendar,
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
        let date = new Date();

        if (
            this.chart &&
            this.chart.datasets &&
            this.chart.datasets.length > 0 &&
            this.chart.datasets[0].data
        ) {
            const keys = Object.keys(this.chart.datasets[0].data);

            if (keys.length > 0) {
                date = new Date(keys[0]);
            }
        }

        return {
            date,
            _chart: this.chart,
        };
    },

    methods: {
        async fetchChart() {
            const inputDate = new Date(this.date);
            const firstDate = new Date(
                inputDate.getFullYear(),
                inputDate.getMonth(),
                1
            );
            const lastDate = new Date(
                inputDate.getFullYear(),
                inputDate.getMonth() + 1,
                0
            );

            try {
                const { data } = await ApiService.get(
                    `project/${this.project.slug}/stat/chart/${this.index}`,
                    {
                        params: {
                            from: firstDate,
                            to: lastDate,
                        },
                    }
                );
                this._chart = data;
            } finally {
            }
        },
    },

    watch: {
        date() {
            this.fetchChart();
        },
    },

    computed: {
        ...mapGetters(["project"]),

        resume() {
            if (
                !this._chart ||
                !Array.isArray(this._chart.datasets) ||
                this._chart.datasets.length === 0
            ) {
                return [];
            }

            if (this._chart.datasets.length === 1) {
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

            return this._chart.datasets.map((dataset, i) => {
                const data = Object.values(dataset.data);

                return {
                    name: dataset.label,
                    value: data.length > 0 ? data[data.length - 1] : 0,
                };
            });
        },

        firstDataset() {
            if (
                !this._chart ||
                !Array.isArray(this._chart.datasets) ||
                this._chart.datasets.length == 0
            ) {
                return null;
            }

            return this._chart.datasets[0].data;
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

        dateValue: {
            get() {
                return dateToString(this.date).substring(0, 10);
            },
            set(value) {
                this.date = new Date(value);
            },
        },
    },
};
</script>
