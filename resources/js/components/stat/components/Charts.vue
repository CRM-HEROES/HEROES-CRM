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
        stats: {
            type: Array,
        },
        from: {
            type: String,
        },
        to: {
            type: String,
        },
    },

    data() {
        return {
            chart: null,
            data: [],
        };
    },

    mounted() {
        Promise.all([ChartJsInit(), ChartDateFnsInit()])
            .then((results) => {
                this.fetchAllData();
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
        async fetchAllData() {
            this.data = [];

            this.stats.forEach((stat, i) => {
                this.fetchData(stat);
            });
        },

        /**
         *
         */
        async fetchData(stat) {
            let { data } = await ApiService.get(
                `project/${this.project.slug}/stat/${stat.key}`,
                {
                    params: {
                        from: this.from,
                        to: this.to,
                    },
                }
            );

            this.data = [
                ...this.data,
                {
                    data: data,
                    borderColor: stat.borderColor,
                    fillColor: stat.fillColor,
                },
            ];
        },
    },

    computed: {
        ...mapGetters(["project"]),

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
                borderColor: data.borderColor,
                backgroundColor: data.fillColor,
            }));
        },
    },

    watch: {
        datasets(value) {
            if (value.length == this.stats.length) {
                this.showChart();
            }
        },
        from() {
            this.fetchAllData();
        },
        to() {
            this.fetchAllData();
        },
    },
};
</script>
