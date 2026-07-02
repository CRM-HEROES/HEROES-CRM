<template>
    <canvas ref="chart"></canvas>
</template>

<script>
import { mapGetters } from "vuex";

import ChartJsInit from "@/utils/chart";
import ChartDateFnsInit from "@/utils/chart-date-fns";

export default {
    props: {
        data: {},
        borderColor: {
            type: String,
            default: "#000000",
        },
        fillColor: {
            default: "#00000022",
        },
    },

    data() {
        return {
            chart: null,
        };
    },

    mounted() {
        Promise.all([ChartJsInit(), ChartDateFnsInit()])
            .then((results) => {
                this.showChart();
            })
            .catch((error) => {
                console.error("An error occurred:", error);
            });
    },

    methods: {
        /**
         *
         * @param {*} ctx
         */
        generateChartObjet(ctx) {
            return new Chart(ctx, {
                type: "bar",
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
                                maxTicksLimit: 5,
                            },
                            grid: {
                                color: function (context) {
                                    if (context.index % 10 != 0) {
                                        return "#00000000";
                                    }

                                    return "#FAFAFA";
                                },
                            },
                        },
                        y: {
                            ticks: {
                                display: true,
                                color: "#999999",
                                beginAtZero: true,
                                maxTicksLimit: 5,
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

                                    return "#FAFAFA";
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
        showChart() {
            if (this.chart !== null) {
                this.chart.destroy();
                this.chart = null;
            }

            let ctx = this.$refs.chart.getContext("2d");
            this.chart = this.generateChartObjet(ctx);
        },
    },

    computed: {
        ...mapGetters(["project"]),

        /**
         *
         */
        labels() {
            return this.data.labels;
        },

        /**
         *
         */
        datasets() {
            if (!this.data) {
                return null;
            }

            return this.data.datasets;
            /*
            return [
                {
                    data: this.data.datasets,
                    fill: true,
                    borderWidth: 1,
                    tension: 0.2,
                    borderColor: this.borderColor,
                    backgroundColor: this.fillColor,
                },
            ];
*/
        },
    },

    watch: {
        datasets(value) {
            if (value) {
                this.showChart();
            }
        },
    },
};
</script>
