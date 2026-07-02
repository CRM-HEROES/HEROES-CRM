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
                type: "pie",
                data: {
                    labels: this.labels,
                    datasets: this.datasets,
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    title: {
                        display: false,
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
