<template>
    <canvas ref="chart"></canvas>
</template>

<script>
import ApiService from "@/apis/api.service";
import { mapGetters } from "vuex";

import ChartJsInit from "@/utils/chart";
import ChartDateFnsInit from "@/utils/chart-date-fns";
import ChartMatrixInit from "@/utils/chart-matrix";

export default {
    props: {
        data: {},
        month: {
            type: Number,
            default: 0,
        },
        year: {
            type: Number,
            default: 2025,
        },
        borderColor: {
            type: String,
            default: "#8979FF",
        },
        fillColor: {
            default: "#8979FF",
        },
    },

    data() {
        return {
            chart: null,
        };
    },

    async mounted() {
        this.showChart();
    },

    methods: {
        showChart() {
            Promise.all([ChartJsInit(), ChartDateFnsInit(), ChartMatrixInit()])

                .then((results) => {
                    if (this.chart !== null) {
                        this.chart.destroy();
                        this.chart = null;
                    }

                    this.chart = new Chart(this.$refs.chart.getContext("2d"), {
                        type: "matrix",
                        data: {
                            datasets: this.datasets,
                        },
                        options: this.options,
                    });
                })
                .catch((error) => {
                    // Handle errors from any of the promises
                    console.error("An error occurred:", error);
                });
        },

        generateData() {
            const startDate = new Date();
            startDate.setMonth(this.month);
            startDate.setFullYear(this.year);
            startDate.setDate(1);
            const endDate = new Date(
                startDate.getFullYear(),
                startDate.getMonth() + 1,
                0
            );

            const currentDate = new Date(startDate);
            const data = [];

            while (currentDate <= endDate) {
                const iso = dateToString(currentDate).substring(0, 10);
                const value = this.data[iso] ? this.data[iso] : 0;
                data.push({
                    x:
                        (currentDate.getDay() == 0 ? 7 : currentDate.getDay()) +
                        "",
                    y: iso,
                    d: iso,
                    v: value,
                });
                currentDate.setDate(currentDate.getDate() + 1);
            }

            return data;
        },

        maxValue(arr) {
            let maxValue = arr[0]; // Initialize maxValue with the first element

            for (let i = 1; i < arr.length; i++) {
                if (arr[i] > maxValue) {
                    maxValue = arr[i]; // Update maxValue if a larger element is found
                }
            }

            return maxValue;
        },
    },

    watch: {
        datasets() {
            this.showChart();
        },
    },

    computed: {
        options() {
            return {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: false,
                    tooltip: {
                        displayColors: false,
                        callbacks: {
                            title() {
                                return "";
                            },
                            label(context) {
                                const v =
                                    context.dataset.data[context.dataIndex];
                                return ["d: " + v.d, "v: " + v.v.toFixed(2)];
                            },
                        },
                    },
                },
                scales: this.scales,
                layout: {
                    padding: {
                        top: 10,
                    },
                },
            };
        },

        scales() {
            return {
                y: {
                    type: "time",
                    left: "left",
                    offset: true,
                    time: {
                        unit: "week",
                        round: "week",
                        isoWeekday: 1,
                        displayFormats: {
                            week: "dd ",
                        },
                    },
                    ticks: {
                        maxRotation: 0,
                        color: "#777777",
                        autoSkip: true,
                        padding: 1,
                    },
                    grid: {
                        display: false,
                        drawBorder: false,
                        tickLength: 0,
                    },
                    title: {
                        display: true,
                        font: { size: 15, weigth: "bold" },
                        text: ({ chart }) => "",
                        padding: 0,
                    },
                },
                x: {
                    type: "time",
                    position: "top",
                    offset: true,
                    time: {
                        unit: "day",
                        parser: "i",
                        isoWeekday: 1,
                        displayFormats: {
                            day: "iiiiii",
                        },
                    },
                    reverse: false,
                    ticks: {
                        source: "data",
                        color: "#777777",
                        padding: 0,
                        maxRotation: 0,
                    },
                    grid: {
                        display: false,
                        drawBorder: false,
                    },
                },
            };
        },

        datasets() {
            const maxValue = this.maxValue(Object.values(this.data));

            return [
                {
                    data: this.generateData(),
                    backgroundColor: ({ raw }) => {
                        const alpha =
                            0.1 +
                            (maxValue == 0 ? 0 : (raw.v * 0.9) / maxValue);

                        return (
                            this.fillColor +
                            (alpha * 255)
                                .toString(16)
                                .toUpperCase()
                                .substring(0, 2)
                        );
                    },
                    borderColor: ({ raw }) => {
                        return "#00000000";
                        const alpha =
                            0.1 +
                            (maxValue == 0 ? 0 : (raw.v * 0.9) / maxValue);
                        return (
                            this.borderColor +
                            (alpha * 255)
                                .toString(16)
                                .toUpperCase()
                                .substring(0, 2)
                        );
                    },
                    borderWidth: 1,
                    borderRadius: 4,
                    hoverBackgroundColor: "yellow",
                    hoverBorderColor: "yellowgreen",
                    width: ({ chart }) =>
                        (chart.chartArea || {}).width /
                            chart.scales.x.ticks.length -
                        3,
                    height: ({ chart }) =>
                        (chart.chartArea || {}).height /
                            chart.scales.y.ticks.length -
                        3,
                },
            ];
        },

        from() {
            return this.year + "-" + this.month + "-01";
        },

        to() {
            const nextMonthFirstDay = new Date(this.year, this.month + 1, 1);
            const lastDayOfMonth = new Date(nextMonthFirstDay - 1);
            return dateToString(lastDayOfMonth).substring(0, 10);
        },
    },
};
</script>
