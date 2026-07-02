<template>
    <canvas ref="chart"></canvas>
</template>

<script>
import ChartJsInit from "@/utils/chart";

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
        this.initChartJs();
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
                type: "line",
                data: {
                    labels: this.labels,
                    datasets: this.datasets,
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        display: false,
                    },
                    title: {
                        display: false,
                    },
                    elements: {
                        point: {
                            radius: 1,
                        },
                    },
                    scales: {
                        x: {
                            ticks: {
                                display: false,
                                color: "#FFFFFF",
                                maxTicksLimit: 20,
                            },
                            grid: {
                                color: function (context) {
                                    if (context.index % 10 != 0) {
                                        return "#00000000";
                                    }

                                    return "#DDDDDD";
                                },
                            },
                        },
                        y: {
                            ticks: {
                                display: true,
                                color: "#999999",
                                beginAtZero: true,
                                maxTicksLimit: 20,
                                callback: (value) => {
                                    if (value % 1 === 0) {
                                        return value;
                                    }
                                },
                            },
                            grid: {
                                color: function (context) {
                                    /*if (context.index % 10 != 0) {
                                        return "#00000000";
                                    }*/

                                    return "#DDDDDD";
                                },
                            },
                        },
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                    layout: {
                        padding: {
                            left: 0,
                        },
                    },
                },
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
         *
         */
        labels() {
            return this.data.length > 0 ? Object.keys(this.data[0].data) : [];
        },

        /**
         *
         */
        datasets() {
            return this.data.map((data) => ({
                data: Object.values(data.data),
                fill: true,
                borderWidth: 1,
                tension: 0.2,
                borderColor: data.fillColor,
                backgroundColor: data.fillColor + "11",
            }));
        },
    },
};
</script>
