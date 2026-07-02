<template>
    <canvas ref="chart"></canvas>
</template>

<script>
import ChartJsInit from "@/utils/chart";

export default {
    props: {},

    data() {
        return {
            chart: null,
            data: null,
        };
    },

    created() {},

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
    },

    computed: {
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
