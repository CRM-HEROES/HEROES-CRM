<template>
    <div class="hc-flex-column" style="height: 100%">
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
                        <a
                            @click.prevent="addCategory"
                            v-text="
                                $t('prospect.table.bulk.label.new_category')
                            "
                            class="hc-button-grey"
                        ></a>
                    </buttons>
                </div>
            </template>

            <template #2>
                <div
                    class="hc-flex-column"
                    style="height: 100%"
                    v-if="prospectCategory"
                >
                    <item @click="setCategory(null)" class="bordered">
                        <icon class="fa fa-caret-left" />
                        <div
                            class="hc-item-main-content"
                            v-text="prospectCategory.name"
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
                            <item tag="label">
                                <icon class="fa fa-check-square" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="$t('all')"
                                ></div>
                                <checkbox v-model="all" />
                            </item>
                            <label-row
                                v-for="label in filteredLabels"
                                :key="label.id"
                                :label="label"
                                v-model="selectedLabels"
                            />
                        </item-list>
                        <loading :loading="fetchingLabels" />
                    </div>
                    <buttons>
                        <a
                            v-if="can('all.project.category.label.add')"
                            @click.prevent="addLabel"
                            class="hc-button-grey"
                            v-text="
                                $t('prospect.table.bulk.label.new_label', {
                                    category: prospectCategory.name,
                                })
                            "
                        ></a>
                    </buttons>
                </div>
            </template>
        </tab-layout>
        <item tag="label" v-if="!disabled" @click="deselect">
            <icon class="fa fa-check-square" />
            <div
                class="hc-item-main-content"
                v-text="
                    $t('prospect.table.bulk.label.selected', {
                        selected: prospectBulkLabels.length,
                    })
                "
            ></div>
            <icon class="fa fa-times" />
        </item>
        <buttons>
            <button
                @click.prevent="attach"
                :disabled="disabled"
                v-text="$t('prospect.table.bulk.add')"
            ></button>
            <button
                @click.prevent="detach"
                :disabled="disabled"
                class="hc-button-danger"
                v-text="$t('prospect.table.bulk.remove')"
            ></button>
        </buttons>
        <loading :loading="bulking" />
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    BULK_ADD_PROSPECT_LABEL,
    BULK_REMOVE_PROSPECT_LABEL,
} from "@/actions/project/prospect/label";
import {
    FETCH_PROSPECTS,
    SET_PROSPECT_CATEGORY,
    UPDATE_PROSPECT_BULK_LABELS,
} from "@/actions/project/prospect";
import { SET_CATEGORY } from "@/actions/project/category";
import { FETCH_LABELS } from "@/actions/project/label";
import { OPEN_MODAL } from "@/actions/modal";
import { CLOSE_SLIDE } from "@/actions/slide";

import CategoryRow from "./CategoryRow.vue";
import LabelRow from "./LabelRow.vue";

export default {
    components: {
        CategoryRow,
        LabelRow,
    },

    data() {
        return {
            bulking: false,
            tab: 0,
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
            store.commit(SET_PROSPECT_CATEGORY, category);
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
            store.commit(SET_CATEGORY, this.prospectCategory);
            store.commit(OPEN_MODAL, "label-add");
        },

        /**
         *
         */
        async attach() {
            this.bulking = true;
            const prospectsSelected = this.prospectsSelected;
            // store.commit(UPDATE_SELECTED_PROSPECTS, []);

            try {
                await store.dispatch(BULK_ADD_PROSPECT_LABEL, {
                    prospects: prospectsSelected,
                    labels: this.prospectBulkLabels,
                });
                store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.bulking = false;
                store.commit(UPDATE_PROSPECT_BULK_LABELS, []);
                store.commit(CLOSE_SLIDE, "prospect-bulk-manage-labels");
            }
        },

        /**
         *
         */
        async detach() {
            this.bulking = true;
            const prospectsSelected = this.prospectsSelected;
            // store.commit(UPDATE_SELECTED_PROSPECTS, []);

            try {
                await store.dispatch(BULK_REMOVE_PROSPECT_LABEL, {
                    prospects: prospectsSelected,
                    labels: this.prospectBulkLabels,
                });
                store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.bulking = false;
                store.commit(UPDATE_PROSPECT_BULK_LABELS, []);
                store.commit(CLOSE_SLIDE, "prospect-bulk-manage-labels");
            }
        },

        deselect() {
            store.commit(UPDATE_PROSPECT_BULK_LABELS, []);
        },
    },

    watch: {
        async prospectCategory(newValue) {
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
            // store.commit(UPDATE_PROSPECT_BULK_LABELS, []);
        },

        prospect(newValue) {
            store.dispatch(FETCH_PROSPECT_LABELS, newValue);
        },
    },

    computed: {
        ...mapGetters([
            "categories",
            "prospectCategory",
            "prospectsSelected",
            "prospectBulkLabels",
            "can",
        ]),

        /**
         *
         */
        disabled() {
            return this.prospectBulkLabels.length == 0;
        },

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
            if (!this.prospectCategory) {
                return [];
            }

            const category = this.categories.find(
                (c) => c.id == this.prospectCategory.id
            );
            if (!category || category.labels === undefined) {
                return [];
            }

            const keyword = removeStringAccent(this.labelKeyword);

            return category.labels.filter(
                (label) => removeStringAccent(label.name).indexOf(keyword) >= 0
            );
        },

        selectedLabels: {
            get() {
                return this.prospectBulkLabels;
            },
            async set(value) {
                store.commit(UPDATE_PROSPECT_BULK_LABELS, value);
            },
        },

        /**
         *
         */
        all: {
            get: function () {
                for (let i in this.filteredLabels) {
                    if (
                        !this.prospectBulkLabels.find(
                            (label) => label == this.filteredLabels[i].id
                        )
                    ) {
                        return false;
                    }
                }

                return true;
            },
            set: async function (value) {
                if (value) {
                    store.commit(
                        UPDATE_PROSPECT_BULK_LABELS,
                        this.filteredLabels.map((label) => label.id)
                    );
                } else {
                    store.commit(UPDATE_PROSPECT_BULK_LABELS, []);
                }
            },
        },
    },
};
</script>
