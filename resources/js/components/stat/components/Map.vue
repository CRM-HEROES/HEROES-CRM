<template>
    <div class="hc-stat-bloc">
        <div>
            <div class="hc-stat-bloc-header" style="padding-bottom: 20px">
                <div class="hc-stat-bloc-selector">
                    <select v-model="stat">
                        <option
                            v-for="(name, key) in stats"
                            :value="key"
                            v-text="name"
                        ></option>
                    </select>
                </div>
                <span class="hc-stat-prospect-bloc-type"> </span>
                <input
                    type="date"
                    class="hc-stat-bloc-calendar"
                    v-model="date"
                />
            </div>
            <div
                class="hc-stat-bloc-body hc-stat-layout hc-stat-h-layout"
                style="position: relative"
            >
                <div style="flex: 1; overflow: hidden">
                    <canvas ref="chart"></canvas>
                </div>
                <div
                    id="hc-stat-choropleth-countries"
                    class="hc-stat-layout hc-stat-v-layout"
                >
                    <div
                        class="hc-stat-choropleth-country"
                        v-for="(value, country) in data"
                        @click="mapCountry(country)"
                    >
                        <div
                            :class="[
                                'hc-stat-choropleth-country-icon',
                                'fa',
                                'fa-globe',
                                settingsStatMap[country] ? 'mapped' : '',
                            ]"
                        ></div>
                        <div
                            class="hc-stat-choropleth-country-name"
                            v-text="country"
                        ></div>
                        <div
                            class="hc-stat-choropleth-country-count"
                            v-text="value"
                        ></div>
                    </div>
                </div>

                <loading :loading="fetchingData" style="display: block" />
            </div>
        </div>
    </div>
</template>

<script>
import ApiService from "@/apis/api.service";
import { mapGetters } from "vuex";
import store from "@/store";

import { GET_SETTING } from "@/actions/project/setting";
import { OPEN_MODAL } from "@/actions/modal";
import { SET_COUNTRY, SET_COUNTRIES } from "@/actions/project/stat/map";

import ChartJsInit from "@/utils/chart";
import ChartGeoJsInit from "@/utils/chart-geo";
import ChartDateFnsInit from "@/utils/chart-date-fns";

export default {
    data() {
        return {
            /**
             * Chart
             */
            chart: null,

            /**
             * Stat per country
             */
            data: {},

            /**
             * Stat date
             */
            date: dateToString(new Date()).substring(0, 10),

            /**
             * Setting key
             * for mapping betwenn the map countries
             * and project countries
             */
            statMapSettingKey: "stat-map",

            /**
             * Default shown stat
             */
            stat: "prospect.total",

            /**
             * Countries from
             * https://unpkg.com/world-atlas/countries-50m.json
             */
            countries: null,

            /**
             * List of stats
             */
            stats: {
                "prospect.total": "Prospect",
                "prospect.sms.total": "SMS",
                "prospect.interaction.total": "Appel",
                "prospect.event.total": "RDV",
                "prospect.message.total": "Message",
                "prospect.file.total": "Fichier",
                "prospect.order.total": "Devis",
            },

            fetchingData: false,
        };
    },

    created() {
        this.fetchStatMapSetting();
        this.fetchStat();
    },

    async mounted() {
        this.initChartJs();
    },

    methods: {
        /**
         *
         */
        initChartJs() {
            Promise.all([ChartGeoJsInit()]).then((results) => {
                fetch("https://unpkg.com/world-atlas/countries-50m.json")
                    .then((r) => r.json())
                    .then((data) => {
                        store.commit(
                            SET_COUNTRIES,
                            data.objects.countries.geometries.map(
                                (country) => country.properties.name
                            )
                        );

                        this.countries = ChartGeo.topojson.feature(
                            data,
                            data.objects.countries
                        ).features;
                    });
            });
        },

        /**
         *
         */
        showChart() {
            if (
                !this.countries ||
                this.datasets.length == 0 ||
                this.data.length == 0
            ) {
                return;
            }

            Promise.all([ChartJsInit(), ChartDateFnsInit()]).then((results) => {
                if (this.chart !== null) {
                    this.chart.destroy();
                    this.chart = null;
                }

                this.chart = new Chart(this.$refs.chart.getContext("2d"), {
                    type: "choropleth",
                    data: {
                        labels: this.countries.map((d) => d.properties.name),
                        datasets: this.datasets,
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

        /**
         *
         */
        async fetchStatMapSetting() {
            try {
                const { data } = await store.dispatch(
                    GET_SETTING,
                    this.statMapSettingKey
                );

                if (!data) {
                }
            } finally {
            }
        },

        /**
         * Fetch stat per country
         */
        async fetchStat() {
            this.fetchingData = true;

            try {
                let { data } = await ApiService.get(
                    `project/${this.project.slug}/stat/country/${this.stat}`,
                    {
                        params: {
                            date: this.date,
                        },
                    }
                );

                this.data = data;
            } finally {
                this.fetchingData = false;
            }
        },

        /**
         *
         * @param {*} country
         */
        getMappedCountries(country) {
            let results = [];

            for (let i in this.settingsStatMap) {
                if (this.settingsStatMap[i] == country) {
                    results.push(i);
                }
            }

            return results;
        },

        /**
         *
         * @param {*} country
         */
        mapCountry(country) {
            store.commit(SET_COUNTRY, country);
            store.commit(OPEN_MODAL, "stat-map-country");
        },
    },

    watch: {
        stat() {
            this.fetchStat();
        },

        date() {
            this.fetchStat();
        },

        datasets() {
            this.showChart();
        },

        settingsStatMap() {
            this.showChart();
        },
    },

    computed: {
        ...mapGetters(["project", "settingsStatMap"]),

        datasets() {
            if (!this.countries) {
                return [];
            }

            return [
                {
                    label: "Countries",
                    data: this.countries.map((d) => {
                        const mappedCountries = this.getMappedCountries(
                            d.properties.name
                        );

                        const value = mappedCountries.reduce(
                            (carry, c) => carry + (this.data[c] ?? 0),
                            0
                        );

                        return {
                            feature: d,
                            value: value,
                        };
                    }),
                },
            ];
        },
    },
};
</script>
