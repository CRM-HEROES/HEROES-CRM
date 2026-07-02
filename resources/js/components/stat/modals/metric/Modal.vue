<template>
    <modal :title="$t('stat.metric.title')" name="stat-add-metric" :width="425">
        <tab :count="2" @tab:change="(t) => (tab = t)">
            <template #1>{{ $t("stat.metric.label") }}</template>
            <template #2>{{ $t("stat.metric.menu") }}</template>
        </tab>
        <tab-layout :count="2" :tab="tab" class="hc-flex-1">
            <!-- Label -->
            <template #1>
                <tab-layout :count="2" :tab="categoryTab" class="hc-flex-1">
                    <template #1>
                        <div class="hc-flex-column" style="height: 100%">
                            <search v-model="categoryKeyword" />
                            <item-list class="hc-flex-1" padding="5px">
                                <category-row
                                    v-for="c in filteredCategories"
                                    :key="c.id"
                                    :category="c"
                                    @click="setCategory(c)"
                                />
                            </item-list>
                            <buttons v-if="can('all.project.category.add')">
                                <a
                                    @click.prevent="addCategory"
                                    v-text="$t('add')"
                                ></a>
                            </buttons>
                        </div>
                    </template>

                    <template #2>
                        <div
                            class="hc-flex-column"
                            style="height: 100%"
                            v-if="category"
                        >
                            <item @click="setCategory(null)" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="category.name"
                                ></div>
                            </item>
                            <div
                                class="hc-flex-1 hc-flex-column"
                                style="
                                    height: 100%;
                                    overflow: hidden;
                                    position: relative;
                                "
                            >
                                <search v-model="labelKeyword" />
                                <item-list class="hc-flex-1" padding="12px">
                                    <label-row
                                        v-for="label in filteredLabels"
                                        :key="label.id"
                                        :label="label"
                                        @click="addLabelMetric(label)"
                                    />
                                </item-list>
                                <buttons
                                    v-if="can('all.project.category.label.add')"
                                >
                                    <a
                                        @click.prevent="addLabel"
                                        v-text="$t('add')"
                                    ></a>
                                </buttons>
                                <!--loading :loading="fetchingLabels" /-->
                            </div>
                        </div>
                    </template>
                </tab-layout>
            </template>

            <!-- Menu -->
            <template #2>
                <div class="hc-flex-column" style="height: 100%">
                    <search v-model="menuKeyword" />
                    <item-list class="hc-flex-1" padding="12px">
                        <menu-row
                            v-for="menu in filteredMenus"
                            :key="menu.id"
                            :menu="menu"
                            @click="addMenuMetric(menu)"
                        />
                    </item-list>
                    <buttons>
                        <a @click.prevent="addMenu" v-text="$t('add')"></a>
                    </buttons>
                </div>
            </template>
        </tab-layout>
    </modal>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { CLOSE_SLIDE } from "@/actions/slide";
import { SET_CATEGORY } from "@/actions/project/category";
import { FETCH_LABELS } from "@/actions/project/label";
import { ADD_METRIC } from "@/actions/project/stat/metric";
import { OPEN_MODAL, CLOSE_MODAL } from "@/actions/modal";

import CategoryRow from "./CategoryRow.vue";
import LabelRow from "./LabelRow.vue";
import MenuRow from "./MenuRow.vue";

export default {
    components: {
        CategoryRow,
        LabelRow,
        MenuRow,
    },

    data() {
        return {
            tab: 0,
            categoryTab: 0,
            category: null,
            categoryKeyword: "",
            labelKeyword: "",
            fetchingLabels: false,
        };
    },

    methods: {
        /**
         *
         * @param {*} category
         */
        setCategory(category) {
            this.category = category;
        },

        /**
         *
         */
        addCategory() {
            store.commit(OPEN_MODAL, "category-add");
        },

        /**
         *
         */
        addLabel() {
            store.commit(SET_CATEGORY, this.category);
            store.commit(OPEN_MODAL, "label-add");
        },

        /**
         *
         */
        addMenu() {
            store.commit(OPEN_MODAL, "menu-add");
        },

        /**
         *
         */
        addLabelMetric(label) {
            store.dispatch(ADD_METRIC, {
                name: label.name,
                color: label.bgcolor,
                for: "prospect",
                filters: { withLabels: [label.id] },
            });
            store.commit(CLOSE_MODAL);
        },

        /**
         *
         */
        addMenuMetric(menu) {
            store.dispatch(ADD_METRIC, {
                name: menu.name,
                color: menu.bgcolor,
                for: "prospect",
                filters: menu.filters,
            });
            store.commit(CLOSE_MODAL);
        },
    },

    watch: {
        async category(newValue) {
            if (newValue) {
                this.categoryTab = 1;

                const category = this.categories.find(
                    (c) => c.id == newValue.id
                );

                if (category && category.labels === undefined) {
                    this.fetchingLabels = true;
                }

                try {
                    await store.dispatch(FETCH_LABELS, newValue.id);
                } finally {
                    this.fetchingLabels = false;
                }
            } else {
                this.categoryTab = 0;
            }
        },
    },

    computed: {
        ...mapGetters(["categories", "menus", "slideOpen", "can"]),

        /**
         *
         */
        filteredCategories() {
            const keyword = removeStringAccent(this.categoryKeyword);

            return this.categories.filter(
                (category) =>
                    removeStringAccent(category.name).indexOf(keyword) >= 0 ||
                    (category.labels &&
                        category.labels.filter(
                            (label) =>
                                removeStringAccent(label.name).indexOf(
                                    keyword
                                ) >= 0
                        ).length > 0)
            );
        },

        /**
         *
         */
        filteredLabels() {
            if (!this.category) {
                return [];
            }

            const category = this.categories.find(
                (c) => c.id == this.category.id
            );
            if (!category || category.labels === undefined) {
                return [];
            }

            const keyword = removeStringAccent(this.labelKeyword);

            return category.labels.filter(
                (label) => removeStringAccent(label.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredMenus() {
            const keyword = removeStringAccent(this.labelKeyword);

            return this.menus.filter(
                (menu) => removeStringAccent(menu.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
