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
                type: "bar",
                data: {
                    labels: this.labels,
                    datasets: this.datasets,
                },
                options: {
                    indexAxis: "x",
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
                                color: "#999999",
                                maxTicksLimit: 20,
                            },
                            grid: {
                                color: function (context) {
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
                                    return "#DDDDDD";
                                },
                            },
                        },
                    },
                    layout: {
                        padding: {
                            left: 0,
                        },
                    },
                    plugins: {
                        legend: {
                            display: false,
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
         * List of labels
         */
        labels() {
            return this.data.map((d) => d.name);
        },

        /**
         * Data values and background colors
         */
        datasets() {
            return [
                {
                    data: this.data.map((d) => d.data),
                    backgroundColor: this.data.map((d) => d.fillColor),
                },
            ];
        },
    },
};
</script>
