<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
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
                    <a @click.prevent="addCategory" v-text="$t('add')"></a>
                </buttons>
            </div>
        </template>

        <template #2>
            <div class="hc-flex-column" style="height: 100%" v-if="category">
                <item @click="setCategory(null)" class="bordered">
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content"
                        v-text="category.name"
                    ></div>
                    <icon
                        v-if="can('all.project.category.update')"
                        tag="a"
                        class="fa fa-cog"
                        @click.prevent.stop="editCategory"
                    />
                    <icon
                        tag="a"
                        @click.prevent.stop="labelsDraggable = !labelsDraggable"
                        class="fa fa-arrows"
                        title="Changer l'ordre des filtres"
                        :color="labelsDraggable ? '#7939b8' : '#CCCCCC'"
                    />
                </item>
                <div
                    class="hc-flex-1 hc-flex-column"
                    style="height: 100%; overflow: hidden; position: relative"
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
                        <template #item="{ element }">
                            <label-row :label="element" />
                        </template>
                    </draggable>
                    <buttons v-if="can('all.project.category.label.add')">
                        <a @click.prevent="addLabel" v-text="$t('add')"></a>
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
import { SET_CATEGORY } from "@/actions/project/category";
import { FETCH_LABELS } from "@/actions/project/label";
import { OPEN_MODAL } from "@/actions/modal";

import LabelService from "@/apis/project/label";
import CategoryRow from "./CategoryRow.vue";
import LabelRow from "./LabelRow.vue";

export default {
    components: {
        CategoryRow,
        LabelRow,
    },

    data() {
        return {
            name: "categories",
            tab: 0,
            categoryKeyword: "",
            labelKeyword: "",
            fetchingLabels: false,
            labelsDraggable: false,
        };
    },

    methods: {
        /**
         *
         * @param {*} category
         */
        setCategory(category) {
            store.commit(SET_CATEGORY, category);
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
        editCategory() {
            store.commit(OPEN_MODAL, "category-update");
            store.commit(SET_CATEGORY, this.category);
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
    },

    watch: {
        async category(newValue) {
            if (newValue) {
                if (this.name) {
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
                }
            } else {
                this.tab = 0;
            }
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "categories",
            "slideOpen",
            "category",
            "can",
        ]),

        /**
         *
         */
        filteredCategories() {
            const keyword = removeStringAccent(this.categoryKeyword);

            return this.categories.filter(
                (category) =>
                    category.for == "prospect" &&
                    removeStringAccent(category.name).indexOf(keyword) >= 0
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
    },
};
</script>
