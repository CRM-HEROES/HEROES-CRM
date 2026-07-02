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
        <div
            class="stat-chart-bloc-chart"
            style="display: flex; flex-direction: column; overflow: auto"
        >
            <event-row
                v-for="event in chart.data"
                :key="event.id"
                :event="event"
                @click="showEvent(event)"
            />
        </div>
        <loading :loading="showingChart" />
    </stat-chart>
</template>

<script>
import store from "@/store";

import StatChart from "./StatChart.vue";
import EventRow from "./EventRow.vue";

import { SHOW_CHART } from "@/actions/project/stat/chart";

export default {
    components: {
        StatChart,
        EventRow,
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

        showEvent(event) {
            const date = new Date(event.started_at);
            const dayOfWeek = date.getDay(); // 0 for Sunday, 1 for Monday, etc.
            const diffToSunday = (dayOfWeek + 7 - 0) % 7; // Days to previous Sunday
            const sundayBefore = new Date(date);
            sundayBefore.setDate(date.getDate() - diffToSunday);

            // 0 for Sunday, 1 for Monday, etc.
            const diffToSaturday = (6 - dayOfWeek + 7) % 7; // Days to next Saturday
            const saturdayAfter = new Date(date);
            saturdayAfter.setDate(date.getDate() + diffToSaturday);

            const routeData = this.$router.resolve({
                name: "event",
                params: {
                    project: this.chart.project.slug,
                },
                query: {
                    filters: JSON.stringify({
                        startedAt: dateToString(sundayBefore).substring(0, 10),
                        endedAt: dateToString(saturdayAfter).substring(0, 10),
                    }),
                    event: event.id,
                    "display-mode": "week",
                },
            });
            window.location.href = routeData.href;
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
};
</script>
