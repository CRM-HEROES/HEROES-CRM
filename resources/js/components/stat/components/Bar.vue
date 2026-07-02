<template>
    <canvas ref="chart"></canvas>
</template>

<script>
import ApiService from "@/apis/api.service";
import { mapGetters } from "vuex";

import ChartJsInit from "@/utils/chart";
import ChartDateFnsInit from "@/utils/chart-date-fns";

export default {
    props: {
        statKey: {
            type: String,
        },
        from: {
            type: String,
        },
        to: {
            type: String,
        },
        borderColor: {
            type: String,
            default: "#000000",
        },
        fillColor: {
            type: String,
            default: "#00000022",
        },
    },

    data() {
        return {
            chart: null,
            data: null,
        };
    },

    created() {},

    mounted() {
        Promise.all([ChartJsInit(), ChartDateFnsInit()])
            .then((results) => {
                this.fetchData();
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
        showChart() {
            if (this.chart !== null) {
                this.chart.destroy();
                this.chart = null;
            }

            let ctx = this.$refs.chart.getContext("2d");
            this.chart = this.generateChartObjet(ctx);
        },

        /**
         *
         */
        async fetchData() {
            let { data } = await ApiService.get(
                `project/${this.project.slug}/stat/${this.statKey}`,
                {
                    params: {
                        from: this.from,
                        to: this.to,
                    },
                }
            );

            this.data = data;
        },
    },

    computed: {
        ...mapGetters(["project"]),

        /**
         *
         */
        labels() {
            return Object.keys(this.data);
        },

        /**
         *
         */
        datasets() {
            if (!this.data) {
                return null;
            }

            return [
                {
                    data: Object.values(this.data),
                    fill: true,
                    borderWidth: 1,
                    tension: 0.2,
                    borderColor: this.borderColor,
                    backgroundColor: this.fillColor,
                },
            ];
        },
    },

    watch: {
        datasets(value) {
            if (value) {
                this.showChart();
            }
        },
        from() {
            this.fetchData();
        },
        to() {
            this.fetchData();
        },
    },
};
</script>
