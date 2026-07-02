<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <div class="hc-flex-column" style="height: 100%">
                <item @click="setCategory(null)" class="bordered">
                    <div class="hc-item-main-content"></div>
                    <icon
                        tag="a"
                        @click.prevent.stop="
                            categoriesDraggable = !categoriesDraggable
                        "
                        class="fa fa-arrows"
                        v-tooltip="
                            'Changer l\'ordre des catégories filtres'
                        "
                        :color="categoriesDraggable ? '#7939b8' : '#CCCCCC'"
                    />
                </item>
                <search v-model="categoryKeyword" />
                <draggable
                    tag="item-list"
                    :list="
                        categoryKeyword
                            ? filteredCategories
                            : this.categories
                    "
                    class="hc-flex-1"
                    style="overflow-y: auto; overflow-x: hidden"
                    item-key="id"
                    :component-data="{ padding: '5px' }"
                    :disabled="categoryKeyword || !categoriesDraggable"
                    @end="updateCategoriesOrder"
                >
                    <template #item="{ element }">
                        <category-row
                            :category="element"
                            @click="setCategory(element)"
                        />
                    </template>
                </draggable>
                <buttons v-if="can('all.project.category.add')">
                    <a @click.prevent="addCategory" v-text="$t('add')"></a>
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
                    <icon
                        tag="a"
                        @click.prevent.stop="
                            labelsDraggable = !labelsDraggable
                        "
                        class="fa fa-arrows"
                        title="Changer l'ordre des filtres"
                        :color="labelsDraggable ? '#7939b8' : '#CCCCCC'"
                    />
                </item>
                <div
                    class="hc-flex-column"
                    style="
                        height: auto;
                        overflow: hidden;
                        position: relative;
                    "
                >
                    <search v-model="labelKeyword" />
                    <draggable
                        tag="item-list"
                        :list="
                            labelKeyword
                                ? filteredLabels
                                : this.category
                                ? this.category.labels
                                : []
                        "
                        class="hc-flex-1"
                        style="overflow-y: auto; overflow-x: hidden"
                        item-key="id"
                        :component-data="{ padding: '12px' }"
                        :disabled="labelKeyword || !labelsDraggable"
                        @end="updateLabelsOrder"
                    >
                        <template #header>
                            <item v-if="!labelKeyword" tag="label">
                                <icon class="fa fa-check-square" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="$t('all')"
                                ></div>
                                <checkbox v-model="all" />
                            </item>
                        </template>
                        <template #item="{ element }">
                            <label-row
                                :label="element"
                                :value="isLabelChecked(element)"
                            />
                        </template>
                    </draggable>
                    <buttons v-if="can('all.project.category.label.add')">
                        <a
                            @click.prevent="addLabel"
                            v-text="$t('label.add.title')"
                        ></a>
                    </buttons>
                    <!--loading :loading="fetchingLabels" /-->
                </div>
            </div>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import {
    FETCH_PIPELINE_LABELS,
    BULK_ADD_PIPELINE_LABEL,
    BULK_REMOVE_PIPELINE_LABEL,
    ADD_PIPELINE_LABEL,
    REMOVE_PIPELINE_LABEL,
} from "@/actions/project/pipeline/label";
import { SET_CATEGORY } from "@/actions/project/category";
import { FETCH_LABELS } from "@/actions/project/label";
import { OPEN_MODAL } from "@/actions/modal";

import LabelService from "@/apis/project/label";
import CategoryService from "@/apis/project/category";
import CategoryRow from "./CategoryRow.vue";
import LabelRow from "./LabelRow.vue";

export default {
    components: {
        CategoryRow,
        LabelRow,
    },

    data() {
        return {
            tab: 0,
            category: null,
            categoryKeyword: "",
            labelKeyword: "",
            fetchingLabels: false,
            categoriesDraggable: deviceType() == "desktop",
            labelsDraggable: deviceType() == "desktop",
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
         * @param {*} label
         */
        isLabelChecked(label) {
            return this.pipelineLabels.findIndex((l) => l.id == label.id) >= 0;
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
        updateLabelsOrder() {
            const orders = this.category.labels.map((label, i) => ({
                label: label.id,
                order: i,
            }));

            LabelService.orders(this.project.slug, this.category.id, {
                orders,
            });
        },

        /**
         *
         */
        updateCategoriesOrder() {
            const orders = this.categories.map((category, i) => ({
                category: category.id,
                order: i,
            }));

            CategoryService.orders(this.project.slug, {
                orders,
            });
        },
    },

    watch: {
        async category(newValue) {
            if (newValue) {
                this.tab = 1;

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

                this.labelKeyword = "";
            } else {
                this.tab = 0;
            }
        },

        pipeline(newValue) {
            if (this.slideOpen(this.name)) {
                store.dispatch(FETCH_PIPELINE_LABELS, newValue);
            }
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "categories",
            "pipeline",
            "pipelineFullName",
            "pipelineLabels",
            "slideOpen",
            "can",
        ]),

        /**
         *
         */
        filteredCategories() {
            const keyword = removeStringAccent(this.categoryKeyword);

            return this.categories.filter(
                (category) =>
                    category.for == "pipeline" &&
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
        all: {
            get: function () {
                if (!this.category) {
                    return false;
                }

                const labels = this.pipelineLabels.filter(
                    (label) => label.category_id == this.category.id
                );

                for (let i in this.filteredLabels) {
                    if (
                        !labels.find(
                            (label) => label.id == this.filteredLabels[i].id
                        )
                    ) {
                        return false;
                    }
                }

                return true;
            },
            set: async function (value) {
                if (value) {
                    this.filteredLabels.forEach((f) => {
                        store.commit(ADD_PIPELINE_LABEL, f);
                    });
                    store.dispatch(BULK_ADD_PIPELINE_LABEL, {
                        pipelines: [this.pipeline.id],
                        labels: this.filteredLabels.map((f) => f.id),
                    });
                } else {
                    this.filteredLabels.forEach((f) => {
                        store.commit(REMOVE_PIPELINE_LABEL, f);
                    });
                    store.dispatch(BULK_REMOVE_PIPELINE_LABEL, {
                        pipelines: [this.pipeline.id],
                        labels: this.filteredLabels.map((f) => f.id),
                    });
                }
            },
        },
    },
};
</script>
