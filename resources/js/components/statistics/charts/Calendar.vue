<template>
    <canvas ref="chart"></canvas>
</template>

<script>
import ChartJsInit from "@/utils/chart";
import ChartDateFnsInit from "@/utils/chart-date-fns";
import ChartMatrixInit from "@/utils/chart-matrix";
import ChartDataLabel from "@/utils/chart-data-label";

export default {
    props: {
        /**
         * Data containing
         * List of labels
         * data value and background color
         * for each label
         */
        data: {
            type: Array,
        },

        /**
         * Chart option
         */
        options: {
            type: Object,
            default: null,
        },
    },

    data() {
        return {
            chart: null,
        };
    },

    async mounted() {
        await ChartJsInit();

        Promise.all([
            ChartDateFnsInit(),
            ChartMatrixInit(),
            // ChartDataLabelInit(),
        ]).then((results) => {
            this.initChartJs();
        });
    },

    methods: {
        /**
         * Get value for an option
         * If the option value is not defined
         * return default value
         */
        getOption(option, defaultValue) {
            return this.options && this.options[option] !== undefined
                ? this.options[option]
                : defaultValue;
        },

        /**
         * Create new chart
         */
        createChart() {
            return new Chart(this.$refs.chart.getContext("2d"), {
                type: "matrix",
                plugins: [ChartDataLabel],
                data: {
                    datasets: this.datasets,
                },
                options: this.chartOptions,
            });
        },

        /**
         *
         */
        initChartJs() {
            if (this.chart !== null) {
                this.chart.destroy();
                this.chart = null;
            }

            this.chart = this.createChart();
        },
    },

    computed: {
        /**
         * List of labels
         */
        labels() {
            return this.data.map((d) => d.label);
        },

        /**
         * Data values and background colors
         */
        datasets() {
            return this.data.map((data) => {
                const maxValue = Math.max(
                    1,
                    Math.max(
                        ...Object.values(data.data).map((data) =>
                            parseInt(data)
                        )
                    )
                );

                return {
                    data: this.dates.map((date) => ({
                        ...date,
                        name: data.name,
                        v: data.data[date.d] ? parseInt(data.data[date.d]) : 0,
                    })),
                    backgroundColor({ raw }) {
                        const alpha = (parseInt(raw.v) / maxValue) * 0.5;

                        let alphaHex = parseInt(alpha * 255)
                            .toString(16)
                            .toUpperCase()
                            .substring(0, 2);

                        if (alphaHex < 10) {
                            alphaHex = "0" + alphaHex;
                        }

                        return data.fillColor + alphaHex;
                    },
                    borderColor({ raw }) {
                        const alpha = Math.max(parseInt(raw.v) / maxValue, 0.1);

                        let alphaHex = parseInt(
                            (alpha * 255)
                                .toString(16)
                                .toUpperCase()
                                .substring(0, 2)
                        );

                        if (alphaHex < 10) {
                            alphaHex = "0" + alphaHex;
                        }

                        return data.fillColor + alphaHex;
                    },
                    borderWidth: 0,
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
                };
            });
        },

        /**
         * List of date for the current month
         */
        dates() {
            const data = [];

            // Start date
            const currentDate = new Date();
            // End date
            const endDate = new Date(
                currentDate.getFullYear(),
                currentDate.getMonth() + 1,
                0
            );

            currentDate.setDate(1);
            for (let i = 0; currentDate <= endDate; ++i) {
                const iso = dateToString(currentDate).substring(0, 10);
                data.push({
                    x:
                        (currentDate.getDay() == 0 ? 7 : currentDate.getDay()) +
                        "",
                    y: iso,
                    d: iso,
                });
                currentDate.setDate(currentDate.getDate() + 1);
            }

            return data;
        },

        chartOptions() {
            return {
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

                                if (context.datasetIndex == 0) {
                                    return [v.d, v.name + ": " + v.v];
                                }

                                return [v.name + ": " + v.v];
                            },
                        },
                    },
                    datalabels: {
                        labels: {
                            value: {
                                color: (context) => {
                                    if (
                                        context.datasetIndex <
                                        this.datasets.length - 1
                                    ) {
                                        return "#00000000";
                                    }

                                    return "#000000";
                                },

                                formatter(value) {
                                    return parseInt(value.d.substring(8));
                                },
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
                responsive: true,
            };
        },

        scales() {
            return {
                y: {
                    display: false,
                    type: "time",
                    left: "left",
                    offset: true,
                    time: {
                        unit: "week",
                        round: "week",
                        isoWeekday: 1,
                        displayFormats: {
                            week: "I",
                        },
                    },
                    ticks: {
                        display: false,
                        maxRotation: 0,
                        autoSkip: true,
                        padding: 1,
                    },
                    grid: {
                        display: false,
                        drawBorder: false,
                        tickLength: 0,
                    },
                    title: {
                        display: false,
                        font: { size: 15, weight: "bold" },
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
                        padding: 0,
                        maxRotation: 0,
                    },
                    grid: {
                        display: false,
                        drawBorder: false,
                        lineWidth: 0,
                    },
                },
            };
        },
    },
};
</script>
