<template>
    <div class="hc-stat-bloc hc-stat-prospect-bloc">
        <div>
            <div class="hc-stat-bloc-header" style="padding-bottom: 20px">
                <i :class="['hc-stat-prospect-bloc-icon', icon]"></i>
                <div class="hc-stat-bloc-selector">
                    <select v-model="stat" style="max-width: 120px">
                        <option
                            v-for="(name, key) in stats"
                            :value="key"
                            v-text="name"
                        ></option>
                    </select>
                </div>
                <span
                    class="hc-stat-prospect-bloc-type"
                    v-text="$t('new')"
                ></span>
                <span
                    class="hc-stat-prospect-bloc-count"
                    v-text="lastValue"
                ></span>
            </div>
            <div class="hc-stat-bloc-body">
                <canvas ref="chart"></canvas>

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
    props: {
        icon: {
            type: String,
        },
        stats: {
            type: Object,
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
        const fromDate = new Date();
        const toDate = new Date();

        fromDate.setDate(fromDate.getDate() - 31);
        toDate.setDate(toDate.getDate() - 1);

        const statKeys = Object.keys(this.stats);

        return {
            chart: null,
            data: [],

            /**
             * Stat from date
             */
            from: dateToString(fromDate).substring(0, 10),

            /**
             * Stat to date
             */
            to: dateToString(toDate).substring(0, 10),

            /**
             * Default shown stat
             */
            stat: statKeys.length > 0 ? statKeys[0] : null,

            fetchingData: false,
        };
    },

    created() {
        Promise.all([ChartJsInit()]).then((results) => {
            if (this.stat) {
                this.fetchStat();
            }
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
         * Fetch stat
         */
        async fetchStat() {
            this.fetchingData = true;
            let { data } = await ApiService.get(
                `project/${this.project.slug}/stat/${this.stat}`,
                {
                    params: {
                        from: this.from,
                        to: this.to,
                    },
                }
            );

            this.data = data;
            this.fetchingData = false;
        },
    },

    watch: {
        stat(newValue) {
            if (newValue) {
                this.fetchStat();
            }
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
            return Object.keys(this.data);
        },

        datasets() {
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

        lastValue() {
            const data = Object.values(this.data);
            return data.length > 0 ? data[data.length - 1] : 0;
        },
    },
};
</script>
