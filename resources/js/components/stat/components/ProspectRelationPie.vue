<template>
    <div class="hc-stat-bloc" style="width: 22.222%">
        <div>
            <div class="hc-stat-bloc-header" style="padding-bottom: 20px">
                <div class="hc-stat-bloc-selector">
                    <select v-model="stat" style="max-width: 200px">
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
            <div class="hc-stat-bloc-body hc-stat-layout hc-stat-h-layout">
                <div style="flex: 1; overflow: hidden">
                    <canvas ref="chart"></canvas>
                </div>
                <div
                    id="hc-stat-choropleth-countries"
                    class="hc-stat-layout hc-stat-v-layout"
                    style="max-width: 150px"
                >
                    <div
                        class="hc-stat-choropleth-country"
                        v-for="relation in data"
                        @click.prevent="showProspects(relation)"
                    >
                        <div
                            :class="[
                                'hc-stat-choropleth-country-icon',
                                'fa',
                                'fa-circle',
                            ]"
                            :style="{ color: relation.color }"
                        ></div>
                        <div
                            class="hc-stat-choropleth-country-name"
                            v-text="relation.name"
                        ></div>
                        <div
                            class="hc-stat-choropleth-country-count"
                            v-text="relation.value"
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
import ChartJsInit from "@/utils/chart";

export default {
    props: {},

    data() {
        return {
            chart: null,
            data: [],

            /**
             * Stat date
             */
            date: dateToString(new Date()).substring(0, 10),

            /**
             * Default shown stat
             */
            stat: "prospect.event.total",

            /**
             * List of stats
             */
            stats: {
                "prospect.sms.total": this.$t("stat.blocs.total.sms"),
                "prospect.interaction.total": this.$t(
                    "stat.blocs.total.interaction"
                ),
                "prospect.event.total": this.$t("stat.blocs.total.event"),
                "prospect.message.total": this.$t("stat.blocs.total.message"),
                "prospect.file.total": this.$t("stat.blocs.total.file"),
                "prospect.order.total": this.$t("stat.blocs.total.order"),

                "prospect.sms.new": this.$t("stat.blocs.new.sms"),
                "prospect.interaction.new": this.$t(
                    "stat.blocs.new.interaction"
                ),
                "prospect.event.new": this.$t("stat.blocs.new.event"),
                "prospect.message.new": this.$t("stat.blocs.new.message"),
                "prospect.file.new": this.$t("stat.blocs.new.file"),
                "prospect.order.new": this.$t("stat.blocs.new.order"),
            },

            fetchingData: false,
        };
    },

    created() {
        Promise.all([ChartJsInit()]).then((results) => {
            this.fetchStat();
        });
    },

    methods: {
        showChart() {
            if (this.datasets.length == 0) {
                return;
            }

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

        /**
         * Fetch stat
         */
        async fetchStat() {
            this.fetchingData = true;
            let { data } = await ApiService.get(
                `project/${this.project.slug}/stat/prospect-relation/${this.stat}`,
                {
                    params: {
                        date: this.date,
                    },
                }
            );

            this.data = data;
            this.fetchingData = false;
        },

        showProspects(relation) {
            this.$router.push({
                name: "prospect",
                params: {
                    project: this.project.slug,
                },
                query: {
                    filters: JSON.stringify(relation.filter),
                },
            });
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
    },

    computed: {
        ...mapGetters(["project"]),

        labels() {
            return this.data.map((d) => d.name);
        },

        datasets() {
            return [
                {
                    data: this.data.map((d) => d.value),
                    backgroundColor: this.data.map((d) => d.color),
                },
            ];
        },
    },
};
</script>
