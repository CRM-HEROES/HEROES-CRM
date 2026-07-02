<template>
    <stat-chart :title="chart.name" :stat-chart="chart">
        <div class="stat-chart-bloc-sub-header">
            <div
                v-if="chart.project"
                class="stat-chart-bloc-project"
                v-text="chart.project.name"
                @click="showProject"
            ></div>
            <div class="stat-chart-bloc-sub-title">
                <span v-text="date"></span>
                <input type="date" v-model="date" />
            </div>
            <div class="stat-chart-bloc-total" v-text="total"></div>
        </div>
        <div class="stat-chart-bloc-chart">
            <pie
                v-if="chart.chart_type == 'pie'"
                :data="data"
                :key="'pie-' + date"
            />
            <hbar
                v-else-if="chart.chart_type == 'hbar'"
                :data="data"
                :key="'hbar-' + date"
            />
            <vbar
                v-else-if="chart.chart_type == 'vbar'"
                :data="data"
                :key="'vbar-' + date"
            />
        </div>
        <div class="stat-chart-bloc-labels">
            <div class="stat-chart-bloc-labels-content">
                <div
                    class="stat-chart-bloc-label"
                    v-for="label in chart.data"
                    :key="label.id"
                    v-tooltip="label.name"
                    @click="$emit('label-clicked', chart, label)"
                >
                    <i
                        class="stat-chart-bloc-label-color fa fa-circle"
                        :style="{ color: label.fillColor }"
                    ></i>
                    <span
                        class="stat-chart-bloc-label-name"
                        v-text="label.name"
                    ></span>
                    <span
                        class="stat-chart-bloc-label-count"
                        v-text="label.data"
                    ></span>
                </div>
            </div>
        </div>
        <loading :loading="showingChart" />
    </stat-chart>
</template>

<script>
import store from "@/store";

import StatChart from "./StatChart.vue";
import Pie from "../charts/Pie.vue";
import Hbar from "../charts/Hbar.vue";
import Vbar from "../charts/Vbar.vue";

import { SHOW_CHART } from "@/actions/project/stat/chart";

export default {
    components: {
        StatChart,
        Pie,
        Hbar,
        Vbar,
    },

    props: {
        /**
         * Chart option
         */
        chart: {
            type: Object,
        },
    },

    data() {
        return {
            showingChart: false,
            date: dateToString(new Date()).substring(0, 10),
        };
    },

    methods: {
        async showChart() {
            this.showingChart = true;

            const date = new Date(this.date);

            // Get the first day of the month
            const firstDay = new Date(date.getFullYear(), date.getMonth(), 1);

            // Get the last day of the month
            const lastDay = new Date(
                date.getFullYear(),
                date.getMonth() + 1,
                0
            );

            try {
                await store.dispatch(SHOW_CHART, {
                    slug: this.chart.id,
                    project: this.chart.project.slug,
                    params: {
                        params: {
                            from: dateToString(firstDay).substring(0, 10),
                            to: dateToString(lastDay).substring(0, 10),
                        },
                    },
                });
            } finally {
                this.showingChart = false;
            }
        },

        showProject() {
            this.$router.push({
                name: "prospect",
                params: {
                    project: this.chart.project.slug,
                },
            });
        },
    },

    watch: {
        date() {
            this.showChart();
        },
    },

    computed: {
        total() {
            return this.chart.data.reduce((carry, d) => carry + d.data, 0);
        },
        data() {
            return this.chart.data;
        },
    },
};
</script>
