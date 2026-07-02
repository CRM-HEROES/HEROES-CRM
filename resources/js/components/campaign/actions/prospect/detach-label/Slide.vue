<template>
    <slide
        :name="name"
        :title="$t('campaign.action.prospect.detach_label.title')"
        icon="fa fa-tags"
        style="width: 260px"
    >
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
                    <buttons>
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
                        <item-list class="hc-flex-1" padding="12px">
                            <label-row
                                v-for="label in filteredLabels"
                                :key="label.id"
                                :label="label"
                                :category="category"
                                @dragging="dragging"
                                @dragged="dragged"
                            />
                        </item-list>
                        <buttons>
                            <a @click.prevent="addLabel" v-text="$t('add')"></a>
                        </buttons>
                        <!--loading :loading="fetchingLabels" /-->
                    </div>
                </div>
            </template>
        </tab-layout>
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { CLOSE_SLIDE } from "@/actions/slide";
import { SET_CATEGORY } from "@/actions/project/category";
import { FETCH_LABELS } from "@/actions/project/label";
import { OPEN_MODAL } from "@/actions/modal";

import CategoryRow from "./CategoryRow.vue";
import LabelRow from "./LabelRow.vue";

export default {
    components: {
        CategoryRow,
        LabelRow,
    },

    data() {
        return {
            name: "campaign-action-prospect-detach-label",
            tab: 0,
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

        dragging(field) {
            this.$emit("dragging", field);
        },

        dragged(x, y) {
            this.$emit("dragged", x, y);
            store.commit(CLOSE_SLIDE);
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
            } else {
                this.tab = 0;
            }
        },
    },

    computed: {
        ...mapGetters(["categories", "category", "slideOpen"]),

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
