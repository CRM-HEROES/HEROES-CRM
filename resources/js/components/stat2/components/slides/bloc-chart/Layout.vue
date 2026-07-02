<template>
    <tab-layout :count="3" :tab="tab" class="hc-flex-1">
        <template #1>
            <div class="hc-flex-column" style="height: 100%">
                <search v-model="blocChartKeyword" />
                <item-list class="hc-flex-1" padding="5px">
                    <bloc-chart-row
                        v-for="blocChart in filteredBlocCharts"
                        :key="blocChart.key"
                        :bloc-value="blocChart"
                        @click="
                            (blocChartCategory = null),
                                (chart.key = blocChart.key),
                                (chart.name = blocChart.name),
                                (chart.labels = []),
                                (tab = 2)
                        " />
                    <category-row
                        v-for="category in filteredCategories"
                        :key="category.id"
                        :category="category"
                        @click="
                            (blocChartCategory = category),
                                (tab = 1),
                                (chart.key = 'label.prospects.' + category.id),
                                (chart.name = category.name),
                                (chart.labels = [])
                        "
                /></item-list>
            </div>
        </template>

        <template #2>
            <div class="hc-flex-column" style="height: 100%">
                <!-- Back -->
                <item
                    @click="(blocChartCategory = null), (tab = 0)"
                    class="bordered"
                >
                    <icon class="fa fa-caret-left" />
                    <!-- Current category name -->
                    <div
                        class="hc-item-main-content"
                        v-text="blocChartCategory.name"
                    ></div>
                </item>

                <div
                    class="hc-flex-column"
                    style="height: auto; overflow: hidden; position: relative"
                >
                    <!-- Search -->
                    <search v-model="labelKeyword" />

                    <item-list class="hc-flex-1" padding="5px">
                        <label-row
                            v-for="label in filteredLabels"
                            :key="label.id"
                            :label="label"
                            v-model="chart.labels"
                        />
                    </item-list>
                    <buttons>
                        <a @click.prevent="tab = 2" v-text="$t('next')"></a>
                    </buttons>
                </div>
            </div>
        </template>

        <template #3>
            <div class="hc-flex-column" style="height: 100%">
                <!-- Back -->
                <item @click="tab = blocChartCategory ? 1 : 0" class="bordered">
                    <icon class="fa fa-caret-left" />
                    <!-- Current category name -->
                    <div class="hc-item-main-content" v-text="chart.name"></div>
                </item>

                <div
                    style="
                        display: flex;
                        flex-direction: row;
                        flex-wrap: wrap;
                        padding: 8px;
                    "
                >
                    <div
                        v-for="chartType in chartTypes"
                        :class="[
                            'hc-stat-chart-type',
                            chart.type == chartType.key ? 'selected' : '',
                        ]"
                        @click="chart.type = chartType.key"
                    >
                        <div class="hc-stat-chart-type-content">
                            <div class="hc-stat-chart-type-icon">
                                <i :class="chartType.icon"></i>
                            </div>
                            <div class="hc-stat-chart-type-name">
                                {{ chartType.name }}
                            </div>
                        </div>
                    </div>
                </div>
                <buttons>
                    <button
                        @click.prevent="addBlocChart"
                        v-text="$t('add')"
                    ></button>
                </buttons>
            </div>
        </template>
    </tab-layout>
</template>

<style scoped>
.hc-stat-chart-type {
    display: flex;
    flex-direction: column;
    width: 33.33%;
    height: auto;
    padding: 2px;
    cursor: pointer;
}

.hc-stat-chart-type-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: auto;
    padding: 16px 8px;
    border-radius: 8px;
    background-color: #00000005;
}

.hc-stat-chart-type:hover .hc-stat-chart-type-content,
.hc-stat-chart-type.selected .hc-stat-chart-type-content {
    background-color: #0000000a;
}

.hc-stat-chart-type-icon {
    font-size: 24px;
    color: #0007;
}

.hc-stat-chart-type:hover .hc-stat-chart-type-icon,
.hc-stat-chart-type.selected .hc-stat-chart-type-icon {
    color: #8979ff;
}

.hc-stat-chart-type-name {
    font-size: 12px;
    line-height: 28px;
}
</style>

<script>
import ApiService from "@/apis/api.service";
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { CLOSE_SLIDE } from "@/actions/slide";

// Components
import BlocChartRow from "./BlocChartRow.vue";
import CategoryRow from "./CategoryRow.vue";
import LabelRow from "./LabelRow.vue";

export default {
    components: {
        BlocChartRow,
        CategoryRow,
        LabelRow,
    },

    data() {
        return {
            tab: 0,
            blocChartKeyword: "",
            labelKeyword: "",
            blocChartCategory: null,
            chart: this.newChart(),
        };
    },

    created() {
        this.blocCharts = [
            {
                key: "prospects",
                name: "Prospects",
                bgcolor: "#83c7d4",
            },
            {
                key: "orders-total",
                name: "Total des commandes",
                bgcolor: "#ff8979",
            },
            {
                key: "orders",
                name: "Commandes",
                bgcolor: "#ff8979",
            },
            {
                key: "interactions-duration",
                name: "Durées des appels",
                bgcolor: "#8979ff",
            },
            {
                key: "interactions",
                name: "Appels",
                bgcolor: "#8979ff",
            },
            {
                key: "sms",
                name: "SMS",
                bgcolor: "#83d496",
            },
            {
                key: "files",
                name: "Fichiers",
                bgcolor: "#ffae4c",
            },
            {
                key: "messages",
                name: "Messages",
                bgcolor: "#ffae4c",
            },
            {
                key: "events",
                name: "RDV",
                bgcolor: "#ffae4c",
            },
        ];

        this.chartTypes = [
            {
                key: "vbar",
                name: "Barres",
                icon: "fa fa-chart-column",
            },
            {
                key: "hbar",
                name: "Barres",
                icon: "fa fa-chart-bar",
            },
            {
                key: "line",
                name: "Lignes",
                icon: "fa fa-chart-area",
            },
            {
                key: "calendar",
                name: "Calendrier",
                icon: "fa fa-calendar",
            },
            {
                key: "funnel",
                name: "Funnel",
                icon: "fa fa-filter",
            },
            {
                key: "pie",
                name: "Camembert",
                icon: "fa fa-chart-pie",
            },
            /*{
                key: "radar",
                name: "Radar",
                icon: "fa fa-draw-polygon",
            },*/
        ];
    },

    methods: {
        newChart() {
            return {
                key: "",
                type: "pie",
                name: "",
                labels: [],
            };
        },

        async addBlocChart() {
            try {
                const { data } = await ApiService.post(
                    `project/${this.project.slug}/stat`,
                    {
                        chart: this.chart,
                    }
                );
                this.$emit("chart-added", data);
            } finally {
                store.commit(CLOSE_SLIDE);
            }
        },
    },

    watch: {
        blocChartCategory() {},
    },

    computed: {
        ...mapGetters(["project", "categories"]),

        /**
         *
         */
        filteredBlocCharts() {
            const keyword = removeStringAccent(this.blocChartKeyword);

            return this.blocCharts.filter(
                (blocChart) =>
                    removeStringAccent(blocChart.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredCategories() {
            const keyword = removeStringAccent(this.blocChartKeyword);

            return this.categories.filter(
                (category) =>
                    category.for == "prospect" &&
                    (removeStringAccent(category.name).indexOf(keyword) >= 0 ||
                        category.labels.find(
                            (label) =>
                                removeStringAccent(label.name).indexOf(
                                    keyword
                                ) >= 0
                        ))
            );
        },

        /**
         *
         */
        filteredLabels() {
            const keyword = removeStringAccent(this.labelKeyword);

            const category = this.categories.find(
                (c) => c.id == this.blocChartCategory.id
            );
            if (!category || category.labels === undefined) {
                return [];
            }

            return category.labels.filter(
                (label) => removeStringAccent(label.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
