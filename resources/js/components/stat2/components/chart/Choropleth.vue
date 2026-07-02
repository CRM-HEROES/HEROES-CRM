<template>
    <canvas ref="chart"></canvas>
</template>

<script>
import store from "@/store";

import { SET_COUNTRIES } from "@/actions/project/stat/map";

import ChartJsInit from "@/utils/chart";
import ChartGeoJsInit from "@/utils/chart-geo";
import ChartDateFnsInit from "@/utils/chart-date-fns";

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
        Promise.all([ChartJsInit(), ChartGeoJsInit(), ChartDateFnsInit()])

            .then((results) => {
                this.initChartJs();
            })
            .catch((error) => {
                // Handle errors from any of the promises
                console.error("An error occurred:", error);
            });
    },

    methods: {
        initChartJs() {
            fetch("https://unpkg.com/world-atlas/countries-50m.json")
                .then((r) => r.json())
                .then((data) => {
                    const countries = ChartGeo.topojson.feature(
                        data,
                        data.objects.countries
                    ).features;

                    store.commit(
                        SET_COUNTRIES,
                        data.objects.countries.geometries.map(
                            (country) => country.properties.name
                        )
                    );

                    const chart = new Chart(this.$refs.chart.getContext("2d"), {
                        type: "choropleth",
                        data: {
                            labels: countries.map((d) => d.properties.name),
                            datasets: [
                                {
                                    label: "Countries",
                                    data: countries.map((d) => {
                                        return {
                                            feature: d,
                                            value: Math.random(),
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
                                    projection: "equalEarth",
                                },
                                color: {
                                    display: false,
                                    axis: "x",
                                    /*ticks: {
                                        callback: function (
                                            value,
                                            index,
                                            values
                                        ) {
                                            if (index > 0) {
                                                return (
                                                    "Between " +
                                                    values[index - 1].value +
                                                    "K and " +
                                                    value +
                                                    "k"
                                                );
                                            }
                                        },
                                    },*/
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
                });
        },
    },
};
</script>
