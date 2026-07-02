<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <!-- Categories -->
        <template #1>
            <div class="hc-flex-column" style="height: 100%">
                <!-- Header -->
                <item @click="setCategory(null)" class="bordered">
                    <div class="hc-item-main-content"></div>
                    <!-- Enable reordering categories -->
                    <icon
                        tag="a"
                        @click.prevent.stop="
                            categoriesDraggable = !categoriesDraggable
                        "
                        class="fa fa-arrows"
                        v-tooltip="'Changer l\'ordre des catégories filtres'"
                        :color="categoriesDraggable ? '#7939b8' : '#CCCCCC'"
                    />
                </item>

                <!-- Search category -->
                <search v-model="labelKeyword" />

                <!-- List of categories -->
                <draggable
                    tag="item-list"
                    :list="labelKeyword ? filteredCategories : this.categories"
                    class="hc-flex-1"
                    style="overflow-y: auto; overflow-x: hidden"
                    item-key="id"
                    :component-data="{ padding: '5px' }"
                    :disabled="labelKeyword || !categoriesDraggable"
                    @end="updateCategoriesOrder"
                >
                    <template #item="{ element }">
                        <category-row
                            :category="element"
                            @click="setCategory(element)"
                        />
                    </template>

                    <!-- List of labels corresponding to the category keyword -->
                    <template #footer>
                        <template v-if="labelKeyword && !prospectCategory">
                            <label-row
                                v-for="label in filteredLabels"
                                :key="label.id"
                                :label="label"
                                :value="isLabelChecked(label)"
                                :multi-selection="multiSelection"
                                v-model="selectedLabels"
                            />
                        </template>
                    </template>
                </draggable>

                <!-- Add category -->
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
                v-if="prospectCategory"
            >
                <!-- Back -->
                <item @click="setCategory(null)" class="bordered">
                    <icon class="fa fa-caret-left" />
                    <!-- Current category name -->
                    <div
                        class="hc-item-main-content"
                        v-text="prospectCategory.name"
                    ></div>
                    <!-- Allow multiple labels selection remove -->
                    <icon
                        @click.prevent.stop="multiSelection = !multiSelection"
                        class="fa fa-check-square"
                        v-tooltip="'Suppression de filtres multiple'"
                        :color="multiSelection ? '#7939b8' : '#CCCCCC'"
                    />
                    <!-- Allow reordering labels -->
                    <icon
                        tag="a"
                        @click.prevent.stop="labelsDraggable = !labelsDraggable"
                        class="fa fa-arrows"
                        title="Changer l'ordre des filtres"
                        :color="labelsDraggable ? '#7939b8' : '#CCCCCC'"
                    />
                </item>

                <div
                    class="hc-flex-column"
                    style="height: auto; overflow: hidden; position: relative"
                >
                    <!-- Search -->
                    <search v-model="labelKeyword" />

                    <!-- Labels list -->
                    <draggable
                        tag="item-list"
                        :list="
                            labelKeyword
                                ? filteredLabels
                                : this.prospectCategory
                                ? this.prospectCategory.labels
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
                                :multi-selection="multiSelection"
                                v-model="selectedLabels"
                            />
                        </template>
                    </draggable>

                    <buttons v-if="can('all.project.category.label.add')">
                        <!-- Bulk remove labels -->
                        <button
                            v-if="
                                can('all.project.category.label.delete') &&
                                selectedLabels.length > 0
                            "
                            @click.prevent="bulkRemove"
                            class="hc-button-danger"
                            v-text="$t('delete')"
                        ></button>

                        <template v-else>
                            <!-- Add label -->
                            <a
                                @click.prevent="addLabel"
                                v-text="$t('label.add.title')"
                            ></a>

                            <!-- Add event -->
                            <a
                                @click.prevent="addEvent"
                                class="hc-button-grey"
                                v-text="'Créer un RDV'"
                            ></a>
                        </template>
                    </buttons>
                    <loading :loading="bulkRemoving" />
                </div>
            </div>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import {
    FETCH_PROSPECT_LABELS,
    BULK_ADD_PROSPECT_LABEL,
    BULK_REMOVE_PROSPECT_LABEL,
    ADD_PROSPECT_LABEL,
    REMOVE_PROSPECT_LABEL,
} from "@/actions/project/prospect/label";
import {
    SET_PROSPECT_CATEGORY,
    SET_PROSPECT,
} from "@/actions/project/prospect";
import { SET_EVENT } from "@/actions/project/event";
import { SET_CATEGORY } from "@/actions/project/category";
import { FETCH_LABELS, REMOVE_LABEL } from "@/actions/project/label";
import { OPEN_MODAL } from "@/actions/modal";
import { OPEN_SUB_SLIDE } from "@/actions/slide";

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
            name: "prospect-manage-labels",
            bulkRemoving: false,
            tab: 0,
            // categoryKeyword: "",
            labelKeyword: "",
            selectedLabels: [],
            fetchingLabels: false,
            multiSelection: false,
            categoriesDraggable: deviceType() == "desktop",
            labelsDraggable: deviceType() == "desktop",
        };
    },

    created() {
        this.showCategory();
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
         * @param {*} label
         */
        isLabelChecked(label) {
            return this.prospectLabels.findIndex((l) => l.id == label.id) >= 0;
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
        addEvent() {
            const startDate = new Date();
            startDate.setMinutes(60);

            const endDate = new Date();
            endDate.setMinutes(120);

            const event = {
                prospect: this.prospect,
                started_at: dateToString(startDate),
                ended_at: dateToString(endDate),
            };

            store.commit(SET_PROSPECT, this.prospect);
            store.commit(SET_EVENT, event);
            store.commit(OPEN_SUB_SLIDE, "prospect-manage-events");
        },

        /**
         *
         */
        updateLabelsOrder() {
            const orders = this.prospectCategory.labels.map((label, i) => ({
                label: label.id,
                order: i,
            }));

            LabelService.orders(this.project.slug, this.prospectCategory.id, {
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

        /**
         *
         */
        async bulkRemove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.bulkRemoving = true;

                try {
                    await LabelService.bulkDestroy(
                        this.project.slug,
                        this.prospectCategory.id,
                        this.selectedLabels
                    );

                    this.selectedLabels.forEach((id) => {
                        store.commit(
                            REMOVE_LABEL,
                            this.prospectCategory.labels.find(
                                (label) => label.id == id
                            )
                        );
                    });
                } finally {
                    this.bulkRemoving = false;
                }
            });
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
    },

    watch: {
        prospectCategory() {
            if (this.prospectCategory) {
                this.showCategory();
            } else {
                this.tab = 0;
            }
        },

        prospect(newValue) {
            if (this.slideOpen(this.name)) {
                store.dispatch(FETCH_PROSPECT_LABELS, newValue);
            }
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "categories",
            "prospectCategory",
            "prospect",
            "prospectFullName",
            "prospectLabels",
            "slideOpen",
            "can",
        ]),

        /**
         *
         */
        filteredCategories() {
            const keyword = removeStringAccent(this.labelKeyword);

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

            if (!this.prospectCategory) {
                return this.categories.reduce((carry, category) => {
                    return [
                        ...carry,
                        ...category.labels.filter(
                            (label) =>
                                removeStringAccent(label.name).indexOf(
                                    keyword
                                ) >= 0
                        ),
                    ];
                }, []);
            }

            const category = this.categories.find(
                (c) => c.id == this.prospectCategory.id
            );
            if (!category || category.labels === undefined) {
                return [];
            }

            return category.labels.filter(
                (label) => removeStringAccent(label.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        all: {
            get: function () {
                if (this.multiSelection) {
                    return (
                        this.selectedLabels.length == this.filteredLabels.length
                    );
                } else {
                    if (!this.prospectCategory) {
                        return false;
                    }

                    const labels = this.prospectLabels.filter(
                        (label) => label.category_id == this.prospectCategory.id
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
                }
            },
            set: async function (value) {
                if (this.multiSelection) {
                    if (value) {
                        this.selectedLabels = this.filteredLabels.map(
                            (f) => f.id
                        );
                    } else {
                        this.selectedLabels = [];
                    }
                } else {
                    if (value) {
                        this.filteredLabels.forEach((f) => {
                            store.commit(ADD_PROSPECT_LABEL, f);
                        });
                        store.dispatch(BULK_ADD_PROSPECT_LABEL, {
                            prospects: [this.prospect.id],
                            labels: this.filteredLabels.map((f) => f.id),
                        });
                    } else {
                        this.filteredLabels.forEach((f) => {
                            store.commit(REMOVE_PROSPECT_LABEL, f);
                        });
                        store.dispatch(BULK_REMOVE_PROSPECT_LABEL, {
                            prospects: [this.prospect.id],
                            labels: this.filteredLabels.map((f) => f.id),
                        });
                    }
                }
            },
        },
    },
};
</script>
