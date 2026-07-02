<template>
    <canvas ref="chart"></canvas>
</template>

<script>
import ChartJsInit from "@/utils/chart";
import ChartGeoJsInit from "@/utils/chart-geo";

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

        /**
         * Chart features
         */
        features: {
            type: Object,
        },

        /**
         * Chart chropleth projection
         */
        projection: {
            type: String,
            default: "equalEarth",
        },

        /**
         * Chart chropleth scale
         */
        scale: {
            type: Number,
            default: 1,
        },

        /**
         * Chart chropleth offsetX
         */
        offsetX: {
            type: Number,
            default: 0,
        },

        /**
         * Chart chropleth offsetY
         */
        offsetY: {
            type: Number,
            default: 0,
        },
    },

    data() {
        return {
            chart: null,
        };
    },

    async mounted() {
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
                type: "choropleth",
                data: {
                    labels: this.features.map((d) => d.properties.name),
                    datasets: [
                        {
                            label: "",
                            data: this.features.map((d) => {
                                const data = this.data[d.properties.name];

                                return {
                                    feature: d,
                                    value: data !== undefined ? data : 0,
                                };
                            }),
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    showOutline: false,
                    showGraticule: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                    scales: {
                        projection: {
                            axis: "x",
                            projection: this.projection,
                            projectionScale: this.scale,
                            projectionOffset: [
                                (this.offsetX *
                                    this.scale *
                                    this.$refs.chart.offsetWidth) /
                                    490,
                                (this.offsetY *
                                    this.scale *
                                    this.$refs.chart.offsetWidth) /
                                    358,
                            ],

                            // France
                            // projectionScale: 19,
                            // projectionOffset: [-1.63157895 * 19, 50.2631579 * 19],
                        },
                        color: {
                            display: false,
                            axis: "x",
                            type: "color",
                            quantize: 5,
                            legend: {
                                position: "bottom-right",
                                align: "right",
                            },
                        },
                    },
                },
            });
        },

        /**
         *
         */
        async initChartJs() {
            if (!this.features) {
                return;
            }

            if (this.chart !== null) {
                this.chart.destroy();
                this.chart = null;
            }

            await ChartJsInit();
            Promise.all([
                // Chart js
                // Chart Geo js
                ChartGeoJsInit(),
                // ChartD3JsInit(),
            ]).then((values) => {
                this.chart = this.createChart(this.features);
            });
        },
    },

    watch: {
        features: {
            handler() {
                this.initChartJs();
            },
            immediate: true,
        },
    },
};
</script>
