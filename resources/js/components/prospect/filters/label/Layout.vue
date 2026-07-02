<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <!-- Categories -->

        <template #1>
            <div class="hc-flex-column" style="height: 100%">
                <search v-model="categoryKeyword" />
                <item-list
                    class="hc-flex-1"
                    style="overflow: auto"
                    padding="5px"
                >
                    <category-row
                        v-for="category in filteredCategories"
                        :category="category"
                        @click="setCategory(category)"
                    />
                </item-list>
                <buttons v-if="can('all.project.category.add')">
                    <a @click.prevent="addCategory" v-text="$t('add')"></a>
                </buttons>
            </div>
        </template>

        <!-- Labels -->

        <template #2>
            <div
                class="hc-flex-column"
                style="height: 100%"
                v-if="prospectFilterCategory"
            >
                <!-- Header -->

                <item @click="setCategory(null)" class="bordered">
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content"
                        v-text="prospectFilterCategory.name"
                    ></div>

                    <!-- Show or hide cog icon on mobile device -->

                    <icon
                        :class="[
                            'fa',
                            'fa-cog',
                            showLabelSetting ? 'icon-blue' : 'icon-grey',
                        ]"
                        @click.prevent.stop="
                            showLabelSetting = !showLabelSetting
                        "
                    />
                </item>

                <div
                    class="hc-flex-column"
                    style="height: auto; overflow: hidden; position: relative"
                >
                    <search v-model="labelKeyword" />

                    <item-list
                        class="hc-flex-1"
                        style="overflow: auto"
                        padding="2px"
                    >
                        <!-- Filter: all labels -->

                        <item
                            v-if="!labelKeyword"
                            tag="label"
                            class="hc-prospects-table-filter-label"
                        >
                            <icon class="fa fa-tag" :style="style" />
                            <div
                                class="hc-item-main-content"
                                v-text="'Avec ' + prospectFilterCategory.name"
                            ></div>

                            <!-- Exclude all labels from current category -->

                            <icon
                                tag="a"
                                class="fa fa-thumbs-down"
                                :style="excludeCategoryStyle"
                                :title="
                                    'Sans filtre ' + prospectFilterCategory.name
                                "
                                @click.prevent="toggleExcludeCategory"
                            />

                            <!-- Include all labels from current category -->

                            <checkbox
                                :model-value="isCheckedCategory"
                                @change="changeCategory"
                            />
                        </item>

                        <item tag="label">
                            <icon class="fa fa-tags" />
                            <div
                                class="hc-item-main-content"
                                v-text="$t('all')"
                            ></div>
                            <checkbox v-model="all" />
                        </item>

                        <!-- Labels list -->

                        <label-row
                            v-for="label in filteredLabels"
                            :label="label"
                            :show-label-setting="showLabelSetting"
                        />
                    </item-list>
                    <buttons v-if="can('all.project.category.label.add')">
                        <a
                            @click.prevent="addLabel"
                            v-text="$t('label.add.title')"
                        ></a>
                    </buttons>
                    <loading :loading="fetchingLabels" />
                </div>
            </div>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    SET_PROSPECT_FILTER_CATEGORY,
    REMOVE_PROSPECT_PARAMS,
    ADD_PROSPECT_PARAMS,
    FETCH_PROSPECTS,
} from "@/actions/project/prospect";
import { SET_CATEGORY } from "@/actions/project/category";
import { FETCH_LABELS } from "@/actions/project/label";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import CategoryRow from "./CategoryRow.vue";
import LabelRow from "./LabelRow.vue";

export default {
    components: {
        CategoryRow,
        LabelRow,
    },

    created() {
        this.showCategory();
    },

    data() {
        return {
            tab: 0,
            categoryKeyword: "",
            labelKeyword: "",
            showLabelSetting: deviceType() == "desktop",
            fetchingLabels: false,
        };
    },

    methods: {
        /**
         *
         * @param {*} category
         */
        setCategory(category) {
            store.commit(SET_PROSPECT_FILTER_CATEGORY, category);
        },

        /**
         * Create new category
         */
        addCategory() {
            store.commit(OPEN_MODAL, "category-add");
        },

        /**
         * Create new label for current category
         */
        addLabel() {
            store.commit(SET_CATEGORY, this.prospectFilterCategory);
            store.commit(OPEN_MODAL, "label-add");
        },

        /**
         * Include all labels from current category
         * @param {*} event
         */
        changeCategory(event) {
            // Remove without param
            store.commit(REMOVE_PROSPECT_PARAMS, {
                key: this.withoutKeyCategory,
            });

            // Add or remove with param
            store.commit(
                event.target.checked
                    ? ADD_PROSPECT_PARAMS
                    : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKeyCategory,
                    value: [],
                }
            );
            store.dispatch(FETCH_PROSPECTS);
        },

        /**
         * Exclude all labels from current category
         */
        toggleExcludeCategory() {
            // Add or remove with param
            store.commit(
                this.isExcludedCategory
                    ? ADD_PROSPECT_PARAMS
                    : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKeyCategory,
                    value: [],
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcludedCategory
                    ? REMOVE_PROSPECT_PARAMS
                    : ADD_PROSPECT_PARAMS,
                {
                    key: this.withoutKeyCategory,
                    value: [],
                }
            );

            store.dispatch(FETCH_PROSPECTS);
        },

        async showCategory() {
            if (this.prospectCategory) {
                this.tab = 1;

                const category = this.categories.find(
                    (c) => c.id == this.prospectCategory.id
                );

                if (category && category.labels === undefined) {
                    this.fetchingLabels = true;
                }

                try {
                    await store.dispatch(
                        FETCH_LABELS,
                        this.prospectCategory.id
                    );
                } finally {
                    this.fetchingLabels = false;
                }

                // this.labelKeyword = "";
            }
        },

        async showCategory() {
            if (this.prospectFilterCategory) {
                this.tab = 1;

                const category = this.categories.find(
                    (c) => c.id == this.prospectFilterCategory.id
                );

                if (category && category.labels === undefined) {
                    this.fetchingLabels = true;
                }

                try {
                    await store.dispatch(FETCH_LABELS, category.id);
                } finally {
                    this.fetchingLabels = false;
                }

                this.labelKeyword = "";
            }
        },
    },

    watch: {
        async prospectFilterCategory(newValue) {
            if (newValue) {
                this.showCategory();
            } else {
                this.tab = 0;
            }
        },
    },

    computed: {
        ...mapGetters([
            "categories",
            "prospectFilterCategory",
            "prospectsParamExists",
            "prospectsParamValue",
            "can",
        ]),

        all: {
            get() {
                if (!this.prospectFilterCategory) {
                    return false;
                }

                const category = this.categories.find(
                    (c) => c.id == this.prospectFilterCategory.id
                );

                if (!category || category.labels === undefined) {
                    return false;
                }

                const t = this.prospectsParamValue(this.withKeyCategory);

                if (!t) {
                    return false;
                }

                return t.length == category.labels.length;
            },
            set(value) {
                if (value) {
                    const category = this.categories.find(
                        (c) => c.id == this.prospectFilterCategory.id
                    );

                    if (!category || category.labels === undefined) {
                        return;
                    }

                    store.commit(ADD_PROSPECT_PARAMS, {
                        key: this.withKeyCategory,
                        value: category.labels.map((l) => l.id),
                    });
                } else {
                    store.commit(REMOVE_PROSPECT_PARAMS, {
                        key: this.withKeyCategory,
                    });
                }
            },
        },

        /**
         *
         */
        withKeyCategory() {
            return "withCategory_" + this.prospectFilterCategory.id;
        },

        /**
         *
         */
        withoutKeyCategory() {
            return "withoutCategory_" + this.prospectFilterCategory.id;
        },

        /**
         *
         */
        isCheckedCategory() {
            return (
                (this.prospectsParamExists(this.withKeyCategory) &&
                    this.prospectsParamValue(this.withKeyCategory).length ==
                        0) ||
                (this.prospectsParamExists(this.withoutKeyCategory) &&
                    this.prospectsParamValue(this.withoutKeyCategory).length ==
                        0)
            );
        },

        /**
         *
         */
        isExcludedCategory() {
            return (
                this.prospectsParamExists(this.withoutKeyCategory) &&
                this.prospectsParamValue(this.withoutKeyCategory).length == 0
            );
        },

        /**
         *
         */
        excludeCategoryStyle() {
            return {
                color: this.isExcludedCategory ? "#CC0000" : "#CCCCCC",
            };
        },

        /**
         *
         */
        filteredCategories() {
            const keyword = removeStringAccent(this.categoryKeyword);

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
            if (!this.prospectFilterCategory) {
                return [];
            }

            const category = this.categories.find(
                (c) => c.id == this.prospectFilterCategory.id
            );
            if (!category || category.labels === undefined) {
                return [];
            }

            const keyword = removeStringAccent(this.labelKeyword);

            return category.labels.filter(
                (label) => removeStringAccent(label.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
