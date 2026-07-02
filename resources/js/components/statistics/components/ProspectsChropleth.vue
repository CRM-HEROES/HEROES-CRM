<template>
    <stat-chart
        :title="chart.name"
        :stat-chart="chart"
        class="stat-chart-bloc-chropleth"
    >
        <div class="stat-chart-bloc-sub-header">
            <div
                v-if="chart.project"
                class="stat-chart-bloc-project"
                v-text="chart.project.name"
            ></div>
            <div class="stat-chart-bloc-sub-title" v-text="''"></div>
            <div class="stat-chart-bloc-total" v-text="total"></div>
        </div>
        <div class="stat-chart-bloc-chart">
            <choropleth
                :data="chart.data"
                :features="features"
                :projection="projection"
                :scale="scale"
                :offset-x="offsetX"
                :offset-y="offsetY"
            />
        </div>
        <div class="stat-chart-bloc-labels">
            <div
                class="stat-chart-bloc-label"
                v-for="(data, key) in chart.data"
                :key="key"
                @click="selectCountry = key"
            >
                <i
                    class="stat-chart-bloc-label-color fa fa-circle"
                    :style="{ color: '#000000' }"
                ></i>
                <span class="stat-chart-bloc-label-name" v-text="key"></span>
                <span class="stat-chart-bloc-label-count" v-text="data"></span>
            </div>
        </div>
        <div
            class="stat-chart-bloc-chropleth-countries"
            :style="{ display: selectCountry ? 'flex' : 'none' }"
            @click="selectCountry = false"
        >
            <div>
                <div
                    class="stat-chart-bloc-chropleth-countries-search"
                    @click.stop
                >
                    <input
                        :placeholder="$t('search')"
                        v-model="countryKeyword"
                    />
                </div>
                <div class="stat-chart-bloc-chropleth-countries-content">
                    <div
                        v-for="country in filteredCountries"
                        :key="country"
                        v-text="country"
                        @click="mapCountry(country)"
                    ></div>
                </div>
                <loading :loading="fetchingChropleth" />
            </div>
        </div>
    </stat-chart>
</template>

<style>
.stat-chart-bloc-chropleth .stat-chart-bloc-label {
    border-radius: 2px;
    cursor: pointer;
}
.stat-chart-bloc-chropleth .stat-chart-bloc-label:hover {
    background-color: #eee;
}
.stat-chart-bloc-chropleth-countries {
    position: fixed;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    background-color: #0001;
    top: 0;
    left: 0;
    z-index: 1000;
}
.stat-chart-bloc-chropleth-countries > div {
    max-height: 400px;
    display: flex;
    flex-direction: column;
    border-radius: 4px;
    background-color: white;
    box-shadow: 0 5px 50px #0001;
}

.stat-chart-bloc-chropleth-countries-search {
    padding: 5px;
}

.stat-chart-bloc-chropleth-countries-search > input {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 4px 10px;
    font-size: 12px;
}

.stat-chart-bloc-chropleth-countries-content {
    flex: 1;
    overflow: auto;
    display: flex;
    flex-direction: column;
}

.stat-chart-bloc-chropleth-countries-content > div {
    padding: 5px 10px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    font-size: 12px;
}

.stat-chart-bloc-chropleth-countries-content > div:hover {
    background-color: #f5f5f5;
}
</style>

<script>
import store from "@/store";

import { UPDATE_CHART, FETCH_CHARTS } from "@/actions/project/stat/chart";

import StatChart from "./StatChart.vue";
import Choropleth from "../charts/Choropleth.vue";

export default {
    components: {
        StatChart,
        Choropleth,
    },

    props: {
        /**
         * Chart option
         */
        chart: {
            type: Object,
        },

        /**
         * Chart chropleth URL
         */
        url: {
            type: String,
            default: "json/world.geojson",
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
            features: null,
            selectCountry: false,
            fetchingChropleth: false,
            updatingChart: false,
            countryKeyword: "",
        };
    },

    created() {
        this.fetchChropleth();
    },

    methods: {
        /**
         * Fetch Chropleth map
         */
        async fetchChropleth() {
            this.fetchingChropleth = true;

            // Url
            fetch(this.url)
                .then((r) => r.json())
                .then((data) => {
                    this.fetchingChropleth = false;
                    this.features = data.features;
                });
        },

        async mapCountry(country) {
            this.updatingChart = true;
            let options = this.chart.options ? this.chart.options : {};
            if (!options.countries) {
                options.countries = {};
            }

            options.countries[this.selectCountry] = country;

            try {
                await store.dispatch(UPDATE_CHART, {
                    id: this.chart.id,
                    project: this.chart.project.slug,
                    options: options,
                });
                store.dispatch(FETCH_CHARTS);
            } finally {
                this.updatingChart = false;
            }
        },
    },

    computed: {
        total() {
            return Object.values(this.chart.data).reduce(
                (carry, d) => carry + d,
                0
            );
        },

        countries() {
            return this.features
                ? this.features
                      .map((d) => d.properties.name)
                      .sort((c1, c2) => (c1 < c2 ? -1 : 1))
                : [];
        },

        filteredCountries() {
            const keyword = removeStringAccent(this.countryKeyword);

            return this.countries.filter(
                (country) => removeStringAccent(country).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
