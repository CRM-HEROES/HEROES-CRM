<template>
    <slide
        name="map-color-by-label"
        :title="$t('map.color.title')"
        icon="fa fa-tags"
        style="width: 260px"
    >
        <tab-layout :count="2" :tab="tab" class="hc-flex-1">
            <template #1>
                <div class="hc-flex-column" style="height: 100%">
                    <search v-model="categoryKeyword" />
                    <draggable
                        tag="item-list"
                        :list="filteredCategories"
                        class="hc-flex-1"
                        style="overflow: auto"
                        item-key="id"
                        :component-data="{ padding: '5px' }"
                        :disabled="!isDesktop"
                        @end=""
                    >
                        <template #header>
                            <item @click="mapColorByCategory(null)">
                                <icon class="fa fa-times" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="$t('none')"
                                ></div>
                            </item>
                        </template>
                        <template #item="{ element }">
                            <category-row
                                :category="element"
                                @click="mapColorByCategory(element.id)"
                                @labels="setCategory(element)"
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
                        <draggable
                            tag="item-list"
                            :list="filteredLabels"
                            class="hc-flex-1"
                            item-key="id"
                            style="overflow: auto"
                            :component-data="{ padding: '10px' }"
                            :disabled="!isDesktop"
                            @end=""
                        >
                            <template #item="{ element }">
                                <label-row :label="element" />
                            </template>
                        </draggable>
                        <buttons>
                            <a @click.prevent="addLabel" v-text="$t('add')"></a>
                        </buttons>
                        <loading :loading="fetchingLabels" />
                    </div>
                </div>
            </template>
        </tab-layout>
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { CLOSE_SLIDE } from "@/actions/slide";
import { SET_MAP_COLOR_BY_LABEL } from "@/actions/project/map";
import { FETCH_LABELS } from "@/actions/project/label";

// Components
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
            categoryKeyword: "",
            labelKeyword: "",
            category: null,
            fetchingLabels: false,
        };
    },

    methods: {
        /**
         *
         */
        mapColorByCategory(category) {
            store.commit(SET_MAP_COLOR_BY_LABEL, category);
            store.commit(CLOSE_SLIDE);
        },

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
                    await store.dispatch(FETCH_LABELS, category.id);
                } finally {
                    this.fetchingLabels = false;
                }
            } else {
                this.tab = 0;
            }
        },
    },

    computed: {
        ...mapGetters(["categories", "can"]),

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

        isDesktop() {
            return deviceType() == "desktop";
        },
    },
};
</script>
