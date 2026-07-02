<template>
    <div v-if="superAdmin" class="hc-dashboard-row">
        <div style="width: 100%; max-width: 1600px">
            <div class="stat-chart-bloc">
                <a
                    class="stat-chart-bloc-content"
                    style="justify-content: center; align-items: center"
                >
                    <span
                        class="stat-chart-bloc-default-icon"
                        style="color: #1d11b5; background: #1d11b511"
                        ><i class="fa fa-table"></i
                    ></span>
                    <span
                        class="stat-chart-bloc-default-title"
                        v-text="'Prospects'"
                    ></span>
                    <span
                        class="stat-chart-bloc-default-count"
                        v-text="dashboard ? dashboard.prospects : ''"
                    ></span>
                </a>
            </div>
            <div class="stat-chart-bloc">
                <router-link
                    :to="{ name: 'project' }"
                    class="stat-chart-bloc-content"
                    style="justify-content: center; align-items: center"
                >
                    <span
                        class="stat-chart-bloc-default-icon"
                        style="color: #349f51; background-color: #349f5111"
                        ><i class="fa fa-user-tie"></i
                    ></span>
                    <span
                        class="stat-chart-bloc-default-title"
                        v-text="'Projets'"
                    ></span>
                    <span
                        class="stat-chart-bloc-default-count"
                        v-text="dashboard ? dashboard.projects : ''"
                    ></span>
                </router-link>
            </div>
            <div class="stat-chart-bloc">
                <router-link
                    :to="{
                        name: 'global.user',
                        query: { sortBy: 'last_activity', sortOrder: 'desc' },
                    }"
                    class="stat-chart-bloc-content"
                    style="justify-content: center; align-items: center"
                >
                    <span
                        class="stat-chart-bloc-default-icon"
                        style="color: #e99529; background-color: #e9952911"
                        ><i class="fa fa-users"></i
                    ></span>
                    <span
                        class="stat-chart-bloc-default-title"
                        v-text="'Utilisateurs'"
                    ></span>
                    <span
                        class="stat-chart-bloc-default-count"
                        v-text="dashboard ? dashboard.users : ''"
                    ></span>
                </router-link>
            </div>
        </div>

        <h2 v-text="$t('dashboard.stats')"></h2>

        <draggable
            tag="div"
            :disabled="!movable"
            :list="statCharts"
            item-key="id"
            style="width: 100%; max-width: 1600px"
            @end="statChartMoved"
        >
            <template #item="{ element }">
                <stat-chart :stat-chart="element" />
            </template>
            <template #footer>
                <div class="stat-chart-bloc">
                    <div
                        class="stat-chart-bloc-content"
                        style="
                            justify-content: center;
                            align-items: center;
                            cursor: pointer;
                        "
                        @click="addStatChart"
                    >
                        <span style="font-size: 200px; color: #cccccc">+</span>
                    </div>
                </div>
            </template>
        </draggable>
    </div>
</template>

<style>
.stat-chart-bloc-default-icon {
    margin-top: 40px;
    width: 100px;
    height: 100px;
    text-align: center;
    line-height: 100px;
    border-radius: 50%;
    background-color: #eee;
    font-size: 40px;
}

.stat-chart-bloc-default-title {
    margin-top: 10px;
    font-size: 14px;
}

.stat-chart-bloc-default-count {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    font-size: 30px;
    font-weight: bold;
    margin-bottom: 25px;
}
</style>

<script>
import ApiService from "@/apis/api.service";
import ChartService from "@/apis/project/stat/chart";
import { mapGetters } from "vuex";
import store from "@/store";

import { OPEN_MODAL } from "@/actions/modal";
import { SET_CHART, FETCH_CHARTS } from "@/actions/project/stat/chart";

import StatChart from "./StatChart.vue";

export default {
    components: {
        StatChart,
    },

    data() {
        return {
            movable: deviceType() == "desktop",
            dashboard: null,
        };
    },

    created() {
        this.fetchStats();
        this.fetchDashboard();
    },

    methods: {
        fetchStats() {
            store.dispatch(FETCH_CHARTS);
        },

        /**
         * Get dashboard data
         */
        async fetchDashboard() {
            let { data } = await ApiService.get(`dashboard`);

            this.dashboard = data;
        },

        /**
         * Add new stat chart
         */
        addStatChart() {
            store.commit(OPEN_MODAL, "stat-chart-add");
            store.commit(SET_CHART, {});
        },

        /**
         *
         */
        statChartMoved() {
            const orders = this.statCharts.map((statChart, i) => ({
                "stat-chart": statChart.id,
                order: i,
            }));

            ChartService.orders({
                orders,
            });
        },
    },

    computed: {
        ...mapGetters(["statCharts"]),
        ...mapGetters(["superAdmin"]),
    },
};
</script>
