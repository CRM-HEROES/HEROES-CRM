<template>
    <div class="" style="overflow: auto; height: 100%; padding: 16px 80px">
        <div
            class=""
            style="display: flex; flex-direction: row; gap: 8px; padding: 8px"
        >
            <template v-if="stats && stats.values">
                <BlocValue
                    v-for="stat in stats.values"
                    :key="stat.key"
                    :id="stat.key"
                    :icon="blocValueIcon(stat.key)"
                    :label="stat.name"
                    :value="stat.value"
                    :unity="stat.unity"
                    :bgcolor="stat.bgcolor"
                    :color="stat.color"
                />
            </template>
            <BlocValue
                icon="fa fa-plus"
                label=""
                unity=""
                bgcolor="#83C7D4"
                color="#FFFFFF"
                @click="addBlocValue"
            />
        </div>
        <div id="hc-stat-blocs">
            <template v-if="stats && stats.charts">
                <template v-for="(chart, index) in stats.charts">
                    <LineChart
                        v-if="chart.type == 'line'"
                        :title="chart.name"
                        :chart="chart.value"
                        :index="index"
                        @chart-removed="removeChart"
                    />
                    <BarChart
                        v-else-if="chart.type == 'vbar'"
                        :title="chart.name"
                        :chart="chart.value"
                        :index="index"
                        @chart-removed="removeChart"
                    />
                    <PieChart
                        v-else-if="chart.type == 'pie'"
                        :title="chart.name"
                        :chart="chart.value"
                        :index="index"
                        @chart-removed="removeChart"
                    />
                    <CalendarChart
                        v-else-if="chart.type == 'calendar'"
                        :title="chart.name"
                        :chart="chart.value"
                        :index="index"
                        @chart-removed="removeChart"
                    />
                    <FunnelChart
                        v-else-if="chart.type == 'funnel'"
                        :title="chart.name"
                        :chart="chart.value"
                        :index="index"
                        @chart-removed="removeChart"
                    />
                    <RadarChart
                        v-else-if="chart.type == 'radar'"
                        :title="chart.name"
                        :chart="chart.value"
                        :index="index"
                        @chart-removed="removeChart"
                    />
                    <ChoroplethChart
                        v-else-if="chart.type == 'choropleth'"
                        :index="index"
                        @chart-removed="removeChart"
                    />
                </template>
            </template>
            <div
                style="
                    display: flex;
                    flex-direction: column;
                    height: 360px;
                    justify-content: center;
                    align-items: center;
                "
            >
                <a
                    style="
                        display: flex;
                        flex-direction: column;
                        width: 100px;
                        height: 100px;
                        justify-content: center;
                        align-items: center;
                        background-color: #0000000a;
                        border-radius: 50%;
                        font-size: 50px;
                        font-weight: bold;
                        color: #ccc;
                        cursor: pointer;
                    "
                    @click="addBlocChart"
                    >+</a
                >
            </div>
        </div>

        <bloc-value-slide />
        <bloc-chart-slide
            @chart-added="
                (chart) => {
                    this.fetchStats();
                }
            "
        />
    </div>
</template>

<style scoped>
#hc-stat-blocs {
    display: flex;
    flex-direction: row;
    padding: 8px;
    flex-wrap: wrap;
}

#hc-stat-blocs > * {
    width: 25%;
}
</style>

<script>
import store from "@/store";
import { mapGetters } from "vuex";
import ApiService from "@/apis/api.service";

import { OPEN_SLIDE } from "@/actions/slide";

import LineChart from "./components/LineChart.vue";
import BarChart from "./components/BarChart.vue";
import PieChart from "./components/PieChart.vue";
import CalendarChart from "./components/CalendarChart.vue";
import FunnelChart from "./components/FunnelChart.vue";
import ChoroplethChart from "./components/ChoroplethChart.vue";
import RadarChart from "./components/RadarChart.vue";
import BlocValue from "./components/BlocValue.vue";

import BlocValueSlide from "./components/slides/bloc-value/Slide.vue";
import BlocChartSlide from "./components/slides/bloc-chart/Slide.vue";

export default {
    components: {
        LineChart,
        BarChart,
        PieChart,
        CalendarChart,
        FunnelChart,
        ChoroplethChart,
        RadarChart,
        BlocValue,

        BlocValueSlide,
        BlocChartSlide,
    },

    props: {},

    data() {
        return {
            stats: [],
        };
    },

    created() {
        this.fetchStats();
        this.blocValueIcons = {
            prospects: "fa fa-users",
            interactions: "fa fa-phone",
            "interactions-duration": "fa fa-phone",
            orders: "fa fa-shopping-bag",
            "orders-total": "fa fa-shopping-bag",
            sms: "fa fa-comment",
            events: "fa fa-calendar",
            files: "fa fa-folder",
            messages: "fa fa-envelope",
        };
    },

    methods: {
        async fetchStats() {
            const from = new Date();
            const to = new Date();

            from.setDate(from.getDate() - 30);
            try {
                const { data } = await ApiService.get(
                    `project/${this.project.slug}/stat`,
                    {
                        params: {
                            from: from,
                            to: to,
                        },
                    }
                );
                this.stats = data;
            } finally {
            }
        },

        addBlocValue() {
            store.commit(OPEN_SLIDE, "stat-bloc-value");
        },

        addBlocChart() {
            store.commit(OPEN_SLIDE, "stat-bloc-chart");
        },

        blocValueIcon(key) {
            if (key.indexOf("label.prospects.") === 0) {
                return "fa fa-tags";
            }

            return this.blocValueIcons[key];
        },

        removeChart(index) {
            this.stats.charts = this.stats.charts.filter(
                (chart, i) => i != index
            );
        },
    },

    watch: {
        projectUserSettingsStatBlocValue() {
            this.fetchStats();
        },
    },

    computed: {
        ...mapGetters(["project", "projectUserSettingsStatBlocValue"]),
    },
};
</script>
