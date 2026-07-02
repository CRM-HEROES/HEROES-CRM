<template>
    <canvas ref="chart"></canvas>
</template>

<script>
import ApiService from "@/apis/api.service";
import { mapGetters } from "vuex";

import ChartJsInit from "@/utils/chart";

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
        };
    },

    async mounted() {
        await ChartJsInit();
        this.initChartJs();
    },

    methods: {
        initChartJs() {
            if (this.chart !== null) {
                this.chart.destroy();
                this.chart = null;
            }

            this.chart = new Chart(this.$refs.chart.getContext("2d"), {
                type: "bar",
                data: {
                    labels: this.labels,
                    datasets: this.datasets,
                },
                options: {
                    indexAxis: "y",
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
                                display: true,
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
    },

    computed: {
        ...mapGetters(["project"]),

        labels() {
            return [
                "RDV physique installation",
                "RDV physique commercial",
                "RDV Tel installation",
                "RDV Tel commercial",
                "GMeet",
            ];
        },

        datasets() {
            return [
                {
                    data: [12, 20, 15, 23, 7],
                    backgroundColor: [
                        "rgb(255, 99, 132)",
                        "rgb(54, 162, 235)",
                        "rgb(255, 79, 55)",
                        "rgb(255, 205, 86)",
                        "rgb(0, 201, 76)",
                    ],
                },
            ];
        },
    },
};
</script>
