<template>
    <div class="hc-stat-bloc" style="width: 20%">
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
                <div class="hc-stat-bloc-selector">
                    <select v-model="month">
                        <option
                            v-for="month in months"
                            :value="month"
                            v-text="month.month + ' / ' + month.year"
                        ></option>
                    </select>
                </div>
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
import ChartDateFnsInit from "@/utils/chart-date-fns";
import ChartMatrixInit from "@/utils/chart-matrix";

export default {
    data() {
        return {
            chart: null,
            data: [],

            /**
             * List of stats
             */
            stats: {
                "prospect.new": this.$t("stat.blocs.new.prospects"),
                "prospect.sms.new": this.$t("stat.blocs.new.sms"),
                "prospect.interaction.new": this.$t(
                    "stat.blocs.new.interaction"
                ),
                "prospect.event.new": this.$t("stat.blocs.new.event"),
                "prospect.message.new": this.$t("stat.blocs.new.message"),
                "prospect.file.new": this.$t("stat.blocs.new.file"),
                "prospect.order.new": this.$t("stat.blocs.new.order"),
            },

            /**
             * Default shown stat
             */
            stat: "prospect.new",

            months: [],

            month: null,

            fetchingData: false,
        };
    },

    created() {
        this.months = this.getLastMonths(24);
        this.month = this.months[0];
    },

    async mounted() {
        Promise.all([ChartJsInit(), ChartDateFnsInit(), ChartMatrixInit()])

            .then((results) => {
                this.fetchStat();
            })
            .catch((error) => {
                // Handle errors from any of the promises
                console.error("An error occurred:", error);
            });
    },

    methods: {
        getLastMonths(count) {
            const today = new Date();
            const lastMonths = [];

            for (let i = 0; i < count; i++) {
                const year = today.getFullYear();
                const month = today.getMonth();

                // Push the current year and month to the array
                lastMonths.push({
                    year: year,
                    month: month,
                });

                // Calculate the previous month
                if (month === 0) {
                    // If it's January, go back to December of the previous year
                    today.setFullYear(year - 1);
                    today.setMonth(11);
                } else {
                    // Otherwise, go back one month
                    today.setMonth(month - 1);
                }
            }

            return lastMonths;
        },

        showChart() {
            if (this.chart !== null) {
                this.chart.destroy();
                this.chart = null;
            }

            this.chart = new Chart(this.$refs.chart.getContext("2d"), {
                type: "matrix",
                data: {
                    datasets: this.datasets,
                },
                options: this.options,
            });
        },

        generateData() {
            const startDate = new Date();
            startDate.setDate(1);
            const endDate = new Date(
                startDate.getFullYear(),
                startDate.getMonth() + 1,
                0
            );

            const currentDate = new Date(startDate);
            const data = [];

            while (currentDate <= endDate) {
                const iso = dateToString(currentDate).substring(0, 10);
                const value = this.data[iso] ? this.data[iso] : 0;
                data.push({
                    x:
                        (currentDate.getDay() == 0 ? 7 : currentDate.getDay()) +
                        "",
                    y: iso,
                    d: iso,
                    v: value,
                });
                currentDate.setDate(currentDate.getDate() + 1);
            }

            return data;
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

        maxValue(arr) {
            let maxValue = arr[0]; // Initialize maxValue with the first element

            for (let i = 1; i < arr.length; i++) {
                if (arr[i] > maxValue) {
                    maxValue = arr[i]; // Update maxValue if a larger element is found
                }
            }

            return maxValue;
        },
    },

    watch: {
        stat(newValue) {
            if (newValue) {
                this.fetchStat();
            }
        },

        month(newValue, oldValue) {
            if (oldValue) {
                this.fetchStat();
            }
        },

        datasets() {
            this.showChart();
        },
    },

    computed: {
        ...mapGetters(["project"]),

        options() {
            return {
                plugins: {
                    legend: false,
                    tooltip: {
                        displayColors: false,
                        callbacks: {
                            title() {
                                return "";
                            },
                            label(context) {
                                const v =
                                    context.dataset.data[context.dataIndex];
                                return ["d: " + v.d, "v: " + v.v.toFixed(2)];
                            },
                        },
                    },
                },
                scales: this.scales,
                layout: {
                    padding: {
                        top: 10,
                    },
                },
            };
        },

        scales() {
            return {
                y: {
                    type: "time",
                    left: "left",
                    offset: true,
                    time: {
                        unit: "week",
                        round: "week",
                        isoWeekday: 1,
                        displayFormats: {
                            week: "I",
                        },
                    },
                    ticks: {
                        maxRotation: 0,
                        autoSkip: true,
                        padding: 1,
                    },
                    grid: {
                        display: false,
                        drawBorder: false,
                        tickLength: 0,
                    },
                    title: {
                        display: true,
                        font: { size: 15, weigth: "bold" },
                        text: ({ chart }) => "",
                        padding: 0,
                    },
                },
                x: {
                    type: "time",
                    position: "top",
                    offset: true,
                    time: {
                        unit: "day",
                        parser: "i",
                        isoWeekday: 1,
                        displayFormats: {
                            day: "iiiiii",
                        },
                    },
                    reverse: false,
                    ticks: {
                        source: "data",
                        padding: 0,
                        maxRotation: 0,
                    },
                    grid: {
                        display: false,
                        drawBorder: false,
                    },
                },
            };
        },

        datasets() {
            const maxValue = this.maxValue(Object.values(this.data));

            return [
                {
                    data: this.generateData(),
                    backgroundColor({ raw }) {
                        const alpha = 0.1 + raw.v; // (maxValue == 0 ? 0 : (raw.v * 0.9) / maxValue);

                        return (
                            "#36a2eb" +
                            (alpha * 255)
                                .toString(16)
                                .toUpperCase()
                                .substring(0, 2)
                        );
                    },
                    borderColor({ raw }) {
                        const alpha = 0.1 + raw.v; // (maxValue == 0 ? 0 : (raw.v * 0.9) / maxValue);
                        return (
                            "#36a2eb" +
                            (alpha * 255)
                                .toString(16)
                                .toUpperCase()
                                .substring(0, 2)
                        );
                    },
                    borderWidth: 1,
                    hoverBackgroundColor: "yellow",
                    hoverBorderColor: "yellowgreen",
                    width: ({ chart }) =>
                        (chart.chartArea || {}).width /
                            chart.scales.x.ticks.length -
                        3,
                    height: ({ chart }) =>
                        (chart.chartArea || {}).height /
                            chart.scales.y.ticks.length -
                        3,
                },
            ];
        },

        from() {
            return this.month.year + "-" + this.month.month + "-01";
        },

        to() {
            const nextMonthFirstDay = new Date(
                this.month.year,
                this.month.month + 1,
                1
            );
            const lastDayOfMonth = new Date(nextMonthFirstDay - 1);
            return dateToString(lastDayOfMonth).substring(0, 10);
        },
    },
};
</script>
