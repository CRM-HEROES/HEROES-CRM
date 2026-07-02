<template>
    <modal
        :name="name"
        :title="
            newChart && newChart.id
                ? 'Modifier ' + newChart.name
                : $t('stat-chart.add.title')
        "
        :width="tab == 0 ? 1000 : 360"
        @open="fetchProjects"
    >
        <tab-layout :count="4" :tab="tab" class="hc-flex-1">
            <!-- Chart type list -->

            <template #1>
                <div class="hc-flex-column" style="height: 100%">
                    <search width="300" v-model="chartKeyword" />
                    <div
                        style="
                            display: flex;
                            flex-direction: row;
                            flex-wrap: wrap;
                            overflow: auto;
                        "
                    >
                        <chart
                            v-for="(chart, i) in filteredCharts"
                            :chart="chart"
                            :key="i"
                            :selected="chartType == chart.type"
                            @click="createChart(chart)"
                        />
                    </div>
                </div>
            </template>

            <!-- Choose projects -->

            <template #2>
                <div
                    v-if="newChart"
                    class="hc-flex-column"
                    style="height: 100%; position: relative"
                >
                    <item tag="a" @click="tab = 0" class="bordered">
                        <icon class="fa fa-caret-left" />
                        <div
                            class="hc-item-main-content"
                            v-text="'Choisir un projet'"
                        ></div>
                    </item>
                    <item-list
                        class="hc-flex-1"
                        style="overflow: auto; padding: 8px"
                    >
                        <item
                            v-if="globalAndProjectChart"
                            tag="a"
                            class="hc-stat-chart-add-project"
                            @click.prevent="setProject(null)"
                        >
                            <icon class="fa fa-columns" />
                            <div
                                class="hc-item-main-content"
                                v-text="$t('all')"
                            ></div>
                            <icon class="fa fa-caret-right" />
                        </item>
                        <project-row
                            v-for="project in projects"
                            :key="project.id"
                            :project="project"
                            @click.prevent="setProject(project)"
                        />
                    </item-list>
                    <loading :loading="fetchingProject" />
                </div>
            </template>

            <!-- New chart form -->

            <template #3>
                <tab-layout
                    :count="2"
                    :tab="chartProspectPerLabelTab"
                    class="hc-flex-1"
                >
                    <!-- New chart form -->

                    <template #1>
                        <form
                            class="hc-flex-column"
                            style="height: 100%"
                            @submit.prevent="
                                newChart.id ? updateChart() : addChart()
                            "
                            v-if="newChart"
                        >
                            <!-- Back -->

                            <item tag="a" @click="tab = 1" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="$t('back')"
                                ></div>
                            </item>

                            <item-list style="padding: 4px 8px">
                                <!-- Name -->

                                <v-field
                                    :label="'Nom'"
                                    required
                                    v-slot="{ label }"
                                    ><input
                                        :placeholder="label + ' ...'"
                                        v-model="newChart.name"
                                        required
                                /></v-field>

                                <!-- Description -->

                                <v-field
                                    :label="'Description'"
                                    v-slot="{ label }"
                                >
                                    <textarea
                                        :placeholder="label + ' ...'"
                                        v-model="newChart.description"
                                    ></textarea>
                                </v-field>

                                <!-- Columns -->

                                <v-field
                                    :label="'Taille en colonne'"
                                    v-slot="{ label }"
                                >
                                    <select
                                        :placeholder="label + ' ...'"
                                        v-model="columns"
                                    >
                                        <option
                                            v-for="i in 4"
                                            :value="i"
                                            v-text="i"
                                        ></option>
                                    </select>
                                </v-field>

                                <!-- Columns -->

                                <v-field
                                    :label="'Taille en ligne'"
                                    v-slot="{ label }"
                                >
                                    <select
                                        :placeholder="label + ' ...'"
                                        v-model="rows"
                                    >
                                        <option
                                            v-for="i in 2"
                                            :value="i"
                                            v-text="i"
                                        ></option>
                                    </select>
                                </v-field>

                                <!-- Chart chropleth map type -->

                                <template
                                    v-if="
                                        newChart.type ==
                                        'total-prospects-per-country'
                                    "
                                >
                                    <v-field :label="'Type de MAP'">
                                        <select v-model="chroplethMap">
                                            <option value="world">
                                                Map Monde
                                            </option>
                                            <option value="france-state">
                                                Régions France
                                            </option>
                                            <option value="usa-state">
                                                Etat USA
                                            </option>
                                        </select>
                                    </v-field>
                                </template>

                                <!-- Chart Per label -->

                                <template v-if="multipleDataChart">
                                    <label
                                        style="
                                            width: 100%;
                                            line-height: 20px;
                                            display: inline-block;
                                            padding: 0 5px;
                                            font-weight: 600;
                                            margin-top: 12px;
                                        "
                                        >Filtres affichés dans le chart:</label
                                    >
                                    <div
                                        style="
                                            display: flex;
                                            flex-direction: row;
                                            flex-wrap: wrap;
                                            gap: 4px;
                                        "
                                    >
                                        <div
                                            v-for="label in newChart.labels"
                                            :key="label.id"
                                            class="stat-chart-add-chart-label"
                                            :style="{
                                                color: 'white',
                                                backgroundColor:
                                                    label.fillColor,
                                            }"
                                        >
                                            <span
                                                class="stat-chart-add-chart-label-name"
                                                v-text="label.name"
                                            ></span>
                                            <span
                                                class="stat-chart-add-chart-label-remove"
                                                @click="removeLabel(label)"
                                                >&times</span
                                            >
                                        </div>
                                        <div
                                            class="stat-chart-add-chart-label"
                                            :style="{
                                                color: 'black',
                                                backgroundColor: '#DDD',
                                            }"
                                            @click.prevent="
                                                chartProspectPerLabelTab = 1
                                            "
                                        >
                                            <span
                                                class="stat-chart-add-chart-label-name"
                                                v-text="'Ajouter'"
                                            ></span>
                                            <span
                                                class="stat-chart-add-chart-label-remove"
                                                >+</span
                                            >
                                        </div>
                                    </div>
                                </template>

                                <!-- Chart type: Pie, HBar, VBar -->

                                <template v-if="hasChartType">
                                    <label
                                        style="
                                            width: 100%;
                                            line-height: 20px;
                                            display: inline-block;
                                            padding: 0 5px;
                                            font-weight: 600;
                                            margin-top: 12px;
                                        "
                                        >Type de chart:</label
                                    >
                                    <div
                                        style="
                                            display: flex;
                                            flex-direction: row;
                                        "
                                    >
                                        <!-- Chart type: Pie, HBar, VBar -->

                                        <template v-if="repartitionChart">
                                            <label
                                                class="stat-chart-add-chart-type"
                                                v-for="t in repartitionChartTypes"
                                                :key="t.type"
                                            >
                                                <input
                                                    type="radio"
                                                    name="stat-chart-add-chart-type"
                                                    :value="t.type"
                                                    v-model="
                                                        newChart.chart_type
                                                    "
                                                />
                                                <div
                                                    class="stat-chart-add-chart-type-content"
                                                >
                                                    <i :class="t.icon"></i>
                                                    <span
                                                        v-text="t.name"
                                                    ></span>
                                                </div>
                                            </label>
                                        </template>

                                        <!-- Chart type: Line, Calendar -->

                                        <template v-if="progressionChart">
                                            <label
                                                class="stat-chart-add-chart-type"
                                                v-for="t in progressionChartTypes"
                                                :key="t.type"
                                            >
                                                <input
                                                    type="radio"
                                                    name="stat-chart-add-chart-type"
                                                    :value="t.type"
                                                    v-model="
                                                        newChart.chart_type
                                                    "
                                                />
                                                <div
                                                    class="stat-chart-add-chart-type-content"
                                                >
                                                    <i :class="t.icon"></i>
                                                    <span
                                                        v-text="t.name"
                                                    ></span>
                                                </div>
                                            </label>
                                        </template>
                                    </div>
                                </template>
                            </item-list>
                            <buttons>
                                <button
                                    v-text="
                                        newChart.id ? $t('update') : $t('add')
                                    "
                                ></button>
                            </buttons>
                            <loading :loading="addingChart || updatingChart" />
                        </form>
                    </template>

                    <!-- Labels, users -->

                    <template #2>
                        <div
                            v-if="newChart && project"
                            class="hc-flex-column"
                            style="height: 100%"
                        >
                            <!-- Labels -->

                            <tab-layout
                                v-if="
                                    newChart.type ==
                                        'total-prospects-per-label' ||
                                    newChart.type ==
                                        'new-prospects-per-label-per-day' ||
                                    newChart.type ==
                                        'total-prospects-per-label-per-day'
                                "
                                :count="2"
                                :tab="categoryTab"
                                class="hc-flex-1"
                            >
                                <template #1>
                                    <div
                                        class="hc-flex-column"
                                        style="height: 100%"
                                    >
                                        <search v-model="categoryKeyword" />
                                        <item-list
                                            class="hc-flex-1"
                                            style="
                                                overflow-y: auto;
                                                overflow-x: hidden;
                                            "
                                            :padding="5"
                                        >
                                            <category-row
                                                v-for="c in filteredCategories"
                                                :key="c.id"
                                                :category="c"
                                                @click="
                                                    (category = c),
                                                        (categoryTab = 1)
                                                "
                                            />
                                        </item-list>
                                    </div>
                                </template>

                                <template #2>
                                    <div
                                        class="hc-flex-column"
                                        style="height: 100%"
                                    >
                                        <item
                                            @click="categoryTab = 0"
                                            class="bordered"
                                        >
                                            <icon class="fa fa-caret-left" />
                                            <div
                                                class="hc-item-main-content"
                                                v-text="category.name"
                                            ></div>
                                        </item>
                                        <search v-model="labelKeyword" />
                                        <item v-if="!labelKeyword" tag="label">
                                            <icon class="fa fa-check-square" />
                                            <div
                                                class="hc-item-main-content"
                                                v-text="$t('all')"
                                            ></div>
                                            <checkbox v-model="allLabels" />
                                        </item>
                                        <item-list
                                            class="hc-flex-1"
                                            style="
                                                overflow-y: auto;
                                                overflow-x: hidden;
                                            "
                                            :padding="5"
                                        >
                                            <label-row
                                                v-for="label in filteredLabels"
                                                :key="label.id"
                                                :label="label"
                                                v-model="newChart.labels"
                                            />
                                        </item-list>
                                    </div>
                                </template>
                            </tab-layout>

                            <div
                                v-if="
                                    newChart.type ==
                                        'total-commissions-per-user-per-day' ||
                                    newChart.type ==
                                        'new-commissions-per-user-per-day'
                                "
                                class="hc-flex-column"
                                style="height: 100%"
                            >
                                <search v-model="userKeyword" />
                                <item-list
                                    class="hc-flex-1"
                                    style="overflow-y: auto; overflow-x: hidden"
                                    :padding="5"
                                >
                                    <user-row
                                        v-for="c in filteredUsers"
                                        :key="c.id"
                                        :user="c"
                                        v-model="newChart.labels"
                                    />
                                </item-list>
                            </div>

                            <buttons>
                                <a
                                    v-text="'Valider'"
                                    @click="chartProspectPerLabelTab = 0"
                                ></a>
                            </buttons>
                        </div>
                    </template>
                </tab-layout>
            </template>
        </tab-layout>
    </modal>
</template>

<style>
.stat-chart-add-chart-type {
    width: 33.333%;
    padding: 4px;
}

.stat-chart-add-chart-type > input {
    display: none;
}

.stat-chart-add-chart-type-content {
    background-color: white;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding: 12px;
    border: 1px solid #eee;
    height: auto;
    cursor: pointer;
    text-align: center;
}

.stat-chart-add-chart-type:hover > .stat-chart-add-chart-type-content {
    background-color: #fafafa;
}

.stat-chart-add-chart-type-content > i {
    font-size: 50px;
    color: #aaa;
}

.stat-chart-add-chart-type-content > span {
    font-size: 12px;
}

.stat-chart-add-chart-type
    > input:checked
    + .stat-chart-add-chart-type-content {
    border: 1px solid #4b007c;
}

.stat-chart-add-chart-type
    > input:checked
    + .stat-chart-add-chart-type-content
    > i {
    color: #4b007c;
}

.stat-chart-add-chart-label {
    height: 36px;
    line-height: 36px;
    display: flex;
    flex-direction: row;
    gap: 8px;
    font-size: 12px;
    border-radius: 18px;
    padding: 0 12px;
    opacity: 0.9;
    cursor: pointer;
}

.stat-chart-add-chart-label:hover {
    opacity: 1;
}

.stat-chart-add-chart-label-remove {
    font-size: 14px;
    line-height: 34px;
}

.fa-bar-chart.vbar {
    transform: rotate(-90deg) scaleY(-1);
}
</style>

<script>
import ProjectService from "@/apis/project";
import { mapGetters } from "vuex";
import store from "@/store";

import Chart from "./Chart.vue";
import ProjectRow from "./ProjectRow.vue";
import CategoryRow from "./prospects-per-label/CategoryRow.vue";
import LabelRow from "./prospects-per-label/LabelRow.vue";
import UserRow from "./UserRow.vue";

import {
    ADD_CHART,
    UPDATE_CHART,
    FETCH_CHARTS,
} from "@/actions/project/stat/chart";
import { CLOSE_MODAL } from "@/actions/modal";

export default {
    components: {
        Chart,
        ProjectRow,
        CategoryRow,
        LabelRow,
        UserRow,
    },

    data() {
        return {
            name: "stat-chart-add",
            tab: 0,
            chartKeyword: "",
            newChart: null,
            project: null,
            fetchingProjects: false,
            fetchingProject: false,
            addingChart: false,
            updatingChart: false,

            chartProspectPerLabelTab: 0,
            categoryTab: 0,
            categoryKeyword: "",
            labelKeyword: "",
            category: null,

            userKeyword: "",

            projects: [],

            /**
             * List of charts
             */
            charts: [
                {
                    icon: "fa fa-pie-chart icon-purple",
                    type: "total-prospects-per-label",
                },
                {
                    icon: "fa fa-globe-europe icon-blue",
                    type: "total-prospects-per-country",
                },
                {
                    icon: "fa fa-chart-line icon-green",
                    type: "new-prospects-per-label-per-day",
                },
                {
                    icon: "fa fa-chart-line icon-cyan",
                    type: "total-prospects-per-label-per-day",
                },
                {
                    icon: "fa fa-chart-line icon-green",
                    type: "new-orders-price-per-day",
                },
                {
                    icon: "fa fa-chart-line icon-blue",
                    type: "total-orders-price-per-day",
                },
                {
                    icon: "fa fa-dollar-sign icon-green",
                    type: "new-commissions-per-user-per-day",
                },
                {
                    icon: "fa fa-dollar-sign icon-blue",
                    type: "total-commissions-per-user-per-day",
                },
                {
                    icon: "fa fa-calendar icon-purple",
                    type: "events-list",
                },
                {
                    icon: "fa fa-map-marker icon-red",
                    type: "events-direction",
                },
                {
                    icon: "fa fa-comments icon-green",
                    type: "new-sms-per-day",
                },
                {
                    icon: "fa fa-comments icon-cyan",
                    type: "total-sms-per-day",
                },
                {
                    icon: "fa fa-calendar icon-green",
                    type: "new-event-per-day",
                },
                {
                    icon: "fa fa-calendar icon-cyan",
                    type: "total-event-per-day",
                },
                {
                    icon: "fa fa-envelope icon-green",
                    type: "new-message-per-day",
                },
                {
                    icon: "fa fa-envelope icon-cyan",
                    type: "total-message-per-day",
                },
                {
                    icon: "fa fa-shopping-cart icon-green",
                    type: "new-order-per-day",
                },
                {
                    icon: "fa fa-shopping-cart icon-cyan",
                    type: "total-order-per-day",
                },
            ].map((bloc) => ({
                ...bloc,
                name: this.$t("stat-chart.add.blocs." + bloc.type + ".title"),
                description: this.$t(
                    "stat-chart.add.blocs." + bloc.type + ".description"
                ),
            })),

            /**
             * Chart types for repartition chart
             */
            repartitionChartTypes: [
                {
                    type: "pie",
                    icon: "fa fa-pie-chart",
                    name: this.$t("stat-chart.add.chart-types.pie"),
                },
                {
                    type: "hbar",
                    icon: "fa fa-bar-chart",
                    name: this.$t("stat-chart.add.chart-types.hbar"),
                },
                {
                    type: "vbar",
                    icon: "fa fa-bar-chart vbar",
                    name: this.$t("stat-chart.add.chart-types.vbar"),
                },
            ],

            /**
             * Chart types for progression chart
             */
            progressionChartTypes: [
                {
                    type: "line",
                    icon: "fa fa-line-chart",
                    name: this.$t("stat-chart.add.chart-types.line"),
                },
                {
                    type: "calendar",
                    icon: "fa fa-calendar",
                    name: this.$t("stat-chart.add.chart-types.calendar"),
                },
            ],
        };
    },

    methods: {
        /**
         * Create new chart
         *
         * @param {*} chart
         */
        createChart(chart) {
            if (this.newChart && this.newChart.id) {
                this.tab = 1;
                this.setProject(this.newChart.project);
            } else {
                this.newChart = {
                    // Project
                    project_id: null,
                    name: chart.name,
                    type: chart.type,
                    description: chart.description,
                    // Labels that will be displayed into the chart
                    labels: [],
                    // Extra options
                    options: {},
                };

                // Default type of chart
                this.newChart.chart_type = this.repartitionChart
                    ? "pie"
                    : "line";

                // If the chart is not associated to a project
                // Go to the form tab
                this.tab = this.globalChart ? 2 : 1;
            }
        },

        /**
         * Select a project
         * for the new chart
         *
         * @param {*} project
         */
        async setProject(project) {
            if (!project) {
                this.newChart.project_id = null;
                this.project = null;
                this.tab = 2;
                return;
            }

            // Default chart name
            this.newChart.name = this.newChart.name;
            this.newChart.project_id = project.id;

            // Load the selected project
            this.fetchingProject = true;
            try {
                const { data } = await ProjectService.show(project.slug);
                this.project = data;
                // And go to the form tab
                this.tab = 2;
            } finally {
                this.fetchingProject = false;
            }
        },

        /**
         * Store the new chart
         */
        async addChart() {
            this.addingChart = true;

            try {
                await store.dispatch(ADD_CHART, {
                    ...this.newChart,
                    project: this.project ? this.project.slug : null,
                });
                // Update charts list
                store.dispatch(FETCH_CHARTS);
            } finally {
                this.addingChart = false;
                this.tab = 0;
                this.project = null;
                // Close modal
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         * Update chart
         */
        async updateChart() {
            this.updatingChart = true;

            try {
                await store.dispatch(UPDATE_CHART, {
                    ...this.newChart,
                    project: this.newChart.project
                        ? this.newChart.project.slug
                        : null,
                });
                // Update charts list
                store.dispatch(FETCH_CHARTS);
            } finally {
                this.updatingChart = false;
                this.tab = 0;
                this.project = null;
                // Close modal
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         * Remove label from the chart
         * @param {*} label
         */
        removeLabel(label) {
            this.newChart.labels = this.newChart.labels.filter(
                (l) => l.id != label.id
            );
        },

        /**
         * Generate random color
         */
        randColor() {
            let sum = parseInt(Math.random() * 40 + 230);
            let r = parseInt(Math.random() * Math.min(sum, 255));
            let g = parseInt(Math.random() * Math.min(sum - r, 255));
            let b = sum - r - g;

            return (
                "#" +
                ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1)
            );
        },

        async fetchProjects() {
            this.fetchingProjects = true;
            try {
                const { data } = await ProjectService.get({
                    params: {
                        filters: JSON.stringify({ admin: 1 }),
                    },
                });
                this.projects = data.data;
            } finally {
                this.fetchingProject = false;
            }
        },
    },

    watch: {
        statChart(newValue) {
            if (!this.modalOpen(this.name)) {
                return;
            }

            this.newChart = newValue;

            if (newValue.id) {
                this.tab = 2;
            } else {
                this.tab = 0;
            }

            if (newValue.project) {
                this.setProject(newValue.project);
            }
        },
    },

    computed: {
        ...mapGetters(["project", "statChart", "modalOpen"]),

        chartType() {
            return this.newChart ? this.newChart.type : null;
        },

        /**
         *
         */
        filteredCategories() {
            if (!this.project || !this.project.categories) {
                return [];
            }

            const keyword = removeStringAccent(this.categoryKeyword);

            return this.project.categories.filter(
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
            if (!this.category || this.category.labels === undefined) {
                return [];
            }

            const keyword = removeStringAccent(this.labelKeyword);

            return this.category.labels.filter(
                (label) => removeStringAccent(label.name).indexOf(keyword) >= 0
            );
        },

        /**
         * Selected project users
         */
        users() {
            if (!this.project || !this.project.users) {
                return [];
            }

            return this.project.users.map((user) => ({
                ...user,
                bgcolor: this.randColor(),
            }));
        },

        /**
         *
         */
        filteredUsers() {
            const keyword = removeStringAccent(this.userKeyword);

            return this.users.filter(
                (label) => removeStringAccent(label.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredCharts() {
            const keyword = removeStringAccent(this.chartKeyword);

            return this.charts.filter(
                (chart) => removeStringAccent(chart.name).indexOf(keyword) >= 0
            );
        },

        /**
         * Charts that have multiple labels
         */
        multipleDataChart() {
            return (
                this.newChart.type == "total-prospects-per-label" ||
                this.newChart.type == "new-prospects-per-label-per-day" ||
                this.newChart.type == "total-prospects-per-label-per-day" ||
                this.newChart.type == "new-commissions-per-user-per-day" ||
                this.newChart.type == "total-commissions-per-user-per-day"
            );
        },

        /**
         * Charts that can be shown into pie, hbar or vbar chart
         */
        repartitionChart() {
            if (!this.newChart) {
                return false;
            }

            return this.newChart.type == "total-prospects-per-label";
        },

        /**
         * Charts that can be shown into line or calendar chart
         */
        progressionChart() {
            if (!this.newChart) {
                return false;
            }

            return (
                this.newChart.type == "new-prospects-per-label-per-day" ||
                this.newChart.type == "total-prospects-per-label-per-day" ||
                this.newChart.type == "new-orders-price-per-day" ||
                this.newChart.type == "total-orders-price-per-day" ||
                this.newChart.type == "new-commissions-per-user-per-day" ||
                this.newChart.type == "total-commissions-per-user-per-day"
            );
        },

        /**
         *
         */
        hasChartType() {
            return this.repartitionChart || this.progressionChart;
        },

        /**
         * Chart that is not related to project
         */
        globalChart() {
            return this.newChart.type == "events-direction";
        },

        /**
         * Chart that can be or not related to project
         */
        globalAndProjectChart() {
            return this.newChart.type == "events-list";
        },

        /**
         * Select all labels
         */
        allLabels: {
            get: function () {
                if (!this.category) {
                    return false;
                }

                for (let i in this.filteredLabels) {
                    if (
                        !this.newChart.labels.find(
                            (label) => label.id == this.filteredLabels[i].id
                        )
                    ) {
                        return false;
                    }
                }

                return true;
            },
            set: function (value) {
                this.newChart.labels = value
                    ? this.filteredLabels.map((label) => ({
                          id: label.id,
                          name: label.name,
                          fillColor: label.bgcolor,
                      }))
                    : [];
            },
        },

        /**
         * Chart chropleth type
         */
        chroplethMap: {
            get() {
                return this.newChart &&
                    this.newChart.options &&
                    this.newChart.options.map
                    ? this.newChart.options.map
                    : "world";
            },
            set(value) {
                if (!this.newChart.options) {
                    this.newChart.options = {};
                }

                this.newChart.options.map = value;

                // World
                if (value == "world") {
                    this.newChart.options.geojson = "json/world.geojson";
                    this.newChart.options.zone = "country";
                    // USA State
                } else if (value == "usa-state") {
                    this.newChart.options.projection = "albertUsa";
                    this.newChart.options.geojson = "json/usa-states.geojson";
                    this.newChart.options.zone = "state";
                    // France State
                } else if (value == "france-state") {
                    this.newChart.options.geojson =
                        "json/france-regions.geojson";
                    this.newChart.options.zone = "state";
                    this.newChart.options.scale = 20;
                    this.newChart.options.offsetX = -1.63157895;
                    this.newChart.options.offsetY = 44;
                    // France country
                } else if (value == "france-county") {
                    this.newChart.options.geojson =
                        "json/france-departements.geojson";
                    this.newChart.options.zone = "county";
                    this.newChart.options.scale = 19;
                    this.newChart.options.offsetX = -1.63157895;
                    this.newChart.options.offsetY = 50.2631579;
                }
            },
        },

        /**
         * Chart columns
         */
        columns: {
            get() {
                return this.newChart &&
                    this.newChart.options &&
                    this.newChart.options.columns
                    ? this.newChart.options.columns
                    : 1;
            },
            set(value) {
                if (!this.newChart.options) {
                    this.newChart.options = { columns: value };
                }

                this.newChart.options.columns = value;
            },
        },

        /**
         * Chart rows
         */
        rows: {
            get() {
                return this.newChart &&
                    this.newChart.options &&
                    this.newChart.options.rows
                    ? this.newChart.options.rows
                    : 1;
            },
            set(value) {
                if (!this.newChart.options) {
                    this.newChart.options = {};
                }

                this.newChart.options.rows = value;
            },
        },
    },
};
</script>
