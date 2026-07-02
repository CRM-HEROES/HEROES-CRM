<template>
    <form
        class="hc-flex-column"
        style="overflow: hidden"
        @submit.prevent="storeMenu"
    >
        <tab-layout :count="4" :tab="tab" class="hc-flex-1">
            <!-- Form -->

            <template #1>
                <item-list gap="5px">
                    <!-- Name -->

                    <v-field :label="$t('name')" required v-slot="{ label }"
                        ><input
                            :placeholder="label + ' ...'"
                            v-model="menu.name"
                            :style="{
                                color: menu.color,
                                backgroundColor: menu.bgcolor,
                            }"
                            required
                    /></v-field>

                    <!-- Color -->

                    <v-field :label="$t('color')"
                        ><input type="color" v-model="menu.color"
                    /></v-field>

                    <!-- Background color -->

                    <color-palette v-model="menu.color" />
                    <v-field :label="$t('bgcolor')"
                        ><input type="color" v-model="menu.bgcolor"
                    /></v-field>
                    <color-palette v-model="menu.bgcolor" />

                    <!-- Use current filters from prospects table -->

                    <item
                        tag="label"
                        v-tuto="{
                            key: 'project.prospect.table.menu.add.user-current-filters',
                            name: 'Tableau de prospects - Menu - Nouveau - Utiliser les filtres actuels',
                            body: 'Ne vous embêtez plus à sélectionner d\'autres filtres,<br>vous pouvez utiliser directement les filtres déjà pre-séletionnés dans le tableau de prospects.',
                            timeout: 300,
                        }"
                    >
                        <icon class="fa fa-filter" :style="style" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('menu.use_current_filters')"
                        ></div>
                        <checkbox v-model="useCurrentFilters" />
                    </item>

                    <!-- Select labels -->

                    <item
                        v-if="!useCurrentFilters"
                        tag="a"
                        @click.prevent="tab = 1"
                    >
                        <icon class="fa fa-filter" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('menu.select_filters')"
                        ></div>
                        <icon class="fa fa-caret-right" />
                    </item>

                    <!-- Affected users -->

                    <item @click="tab = 3">
                        <icon class="fa fa-users" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('label.affected_users')"
                        ></div>
                        <div
                            v-if="menuUsers.length"
                            class="hc-item-count"
                            v-text="menuUsers.length"
                        ></div>
                        <icon class="fa fa-caret-right" />
                    </item>
                </item-list>
            </template>

            <!-- Categories -->

            <template #2>
                <div class="hc-flex-column" style="height: 100%">
                    <item tag="a" @click.prevent="tab = 0">
                        <icon class="fa fa-caret-left" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('menu.select_filters')"
                        ></div>
                    </item>
                    <search v-model="categoryKeyword" />
                    <item-list class="hc-flex-1" padding="5px">
                        <category-row
                            v-for="c in filteredCategories"
                            :key="c.id"
                            :category="c"
                            :labels-count="
                                categoryKeyword
                                    ? filteredCategoryLabels(c, categoryKeyword)
                                          .length
                                    : 0
                            "
                            @click="setCategory(c)"
                        />
                    </item-list>
                </div>
            </template>

            <!-- Labels -->

            <template #3>
                <div
                    class="hc-flex-column"
                    style="height: 100%"
                    v-if="menuCategory"
                >
                    <item @click="setCategory(null)" class="bordered">
                        <icon class="fa fa-caret-left" />
                        <div
                            class="hc-item-main-content"
                            v-text="menuCategory.name"
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
                            <!-- Filter: all labels -->

                            <item
                                tag="label"
                                class="hc-prospects-table-filter-label"
                            >
                                <icon class="fa fa-tag" :style="style" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="'Avec ' + menuCategory.name"
                                ></div>

                                <!-- Exclude all labels from current category -->

                                <icon
                                    tag="a"
                                    class="fa fa-thumbs-down"
                                    :style="{
                                        color: isCategoryExcluded
                                            ? '#CC0000'
                                            : '#CCCCCC',
                                    }"
                                    :title="'Sans filtre ' + menuCategory.name"
                                    @click.prevent="categoryExcluded"
                                />

                                <!-- Include all labels from current category -->

                                <checkbox
                                    :model-value="isCategoryChecked"
                                    @change="
                                        categoryChecked($event.target.value)
                                    "
                                />
                            </item>

                            <label-row
                                v-for="label in filteredLabels"
                                :key="label.id"
                                :label="label"
                                :value="isLabelChecked(label)"
                                :excluded="isLabelExcluded(label)"
                                @checked="labelChecked"
                                @exclude="labelExcluded"
                            />
                        </item-list>
                    </div>
                </div>
            </template>

            <template #4>
                <div class="hc-flex-column" style="height: 100%">
                    <item @click="tab = 0">
                        <icon class="fa fa-caret-left" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('label.affected_users')"
                        ></div>
                    </item>
                    <search v-model="userKeyword" />
                    <item-list
                        class="hc-flex-1"
                        padding="5px"
                        style="max-height: 400px"
                    >
                        <user-row
                            v-for="user in filteredUsers"
                            :key="user.id"
                            :user="user"
                            v-model="menuUsers"
                        />
                    </item-list>
                </div>
            </template>
        </tab-layout>
        <buttons>
            <button v-text="$t('menu.new')"></button>
            <a
                v-if="tab == 1"
                class="hc-button-grey"
                @click.prevent="addCategory"
                v-text="$t('menu.new_category')"
            ></a>
            <a
                v-if="tab == 2"
                class="hc-button-grey"
                @click.prevent="addLabel"
                v-text="$t('menu.new_label')"
            ></a>
        </buttons>
        <loading :loading="addingMenu" />
    </form>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { ADD_MENU } from "@/actions/project/menu";
import { OPEN_MODAL, CLOSE_MODAL } from "@/actions/modal";
import { SET_CATEGORY } from "@/actions/project/category";
import { FETCH_LABELS } from "@/actions/project/label";
import { BULK_ADD_USER_MENU } from "@/actions/project/user/menu";

import CategoryRow from "./CategoryRow.vue";
import LabelRow from "./LabelRow.vue";
import UserRow from "./UserRow.vue";

export default {
    components: {
        CategoryRow,
        LabelRow,
        UserRow,
    },

    data() {
        return {
            tab: 0,
            menu: this.newMenu(),
            addingMenu: false,
            menuCategory: null,
            categoryKeyword: "",
            labelKeyword: "",
            useCurrentFilters: false,
            fetchingLabels: false,
            userKeyword: "",
            menuUsers: [],
        };
    },

    methods: {
        /**
         *
         * @param {*} category
         */
        setCategory(category) {
            this.menuCategory = category;
        },

        /**
         *
         */
        newMenu() {
            return {
                name: "",
                color: "#000000",
                bgcolor: "#FFFFFF",
                filters: {},
            };
        },

        /**
         *
         */
        async storeMenu() {
            this.addingMenu = true;

            if (this.useCurrentFilters) {
                this.menu.filters = this.prospectsParams;
            }

            try {
                const menu = await store.dispatch(ADD_MENU, this.menu);

                if (this.menuUsers.length > 0) {
                    await store.dispatch(BULK_ADD_USER_MENU, {
                        users: this.menuUsers,
                        menus: [menu.id],
                    });
                }
            } finally {
                this.addingMenu = false;
                this.menu = this.newMenu();
                this.menuUsers = [];
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         * @param {*} label
         */
        isLabelChecked(label) {
            return (
                this.menu.filters &&
                Array.isArray(
                    this.menu.filters["withCategory_" + label.category_id]
                ) &&
                this.menu.filters["withCategory_" + label.category_id].indexOf(
                    label.id
                ) >= 0
            );
        },

        /**
         *
         * @param {*} label
         */
        isLabelExcluded(label) {
            return (
                this.menu.filters &&
                Array.isArray(
                    this.menu.filters["withoutCategory_" + label.category_id]
                ) &&
                this.menu.filters[
                    "withoutCategory_" + label.category_id
                ].indexOf(label.id) >= 0
            );
        },

        /**
         * Include label into menu filters
         * @param {*} value
         */
        labelChecked(label, value) {
            if (!this.menu.filters) {
                this.menu.filters = {};
                this.menu.name = label.name;
                this.menu.color = label.color;
                this.menu.bgcolor = label.bgcolor;
            }

            // Label is checked
            if (value) {
                // Add the label into the menu filters
                if (!this.menu.filters["withCategory_" + label.category_id]) {
                    this.menu.filters["withCategory_" + label.category_id] = [];
                }

                this.menu.filters["withCategory_" + label.category_id].push(
                    label.id
                );
                // Label is not checked
            } else {
                // Remove the label from the menu filters
                if (this.menu.filters["withCategory_" + label.category_id]) {
                    this.menu.filters["withCategory_" + label.category_id] =
                        this.menu.filters[
                            "withCategory_" + label.category_id
                        ].filter((labelId) => labelId != label.id);
                }

                // If no label is selected
                // in the category
                // remove the category from
                // the menu filters
                if (
                    this.menu.filters["withCategory_" + label.category_id]
                        .length == 0
                ) {
                    delete this.menu.filters[
                        "withCategory_" + label.category_id
                    ];
                }
            }

            if (!this.menu.name) {
                this.menu.name = (value ? "" : "- ") + label.name;
                this.menu.color = label.color;
                this.menu.bgcolor = label.bgcolor;
            }
        },

        /**
         * Exclude label from menu filters
         * @param {*} value
         */
        labelExcluded(label, value) {
            if (!this.menu.filters) {
                this.menu.filters = {};
            }

            // Label is checked
            if (value) {
                // Add the label into the menu filters
                if (
                    !this.menu.filters["withoutCategory_" + label.category_id]
                ) {
                    this.menu.filters["withoutCategory_" + label.category_id] =
                        [];
                }

                this.menu.filters["withoutCategory_" + label.category_id].push(
                    label.id
                );
                // Label is not checked
            } else {
                // Remove the label from the menu filters
                if (this.menu.filters["withoutCategory_" + label.category_id]) {
                    this.menu.filters["withoutCategory_" + label.category_id] =
                        this.menu.filters[
                            "withoutCategory_" + label.category_id
                        ].filter((labelId) => labelId != label.id);
                }

                // If no label is selected
                // in the category
                // remove the category from
                // the menu filters
                if (
                    this.menu.filters["withoutCategory_" + label.category_id]
                        .length == 0
                ) {
                    delete this.menu.filters[
                        "withoutCategory_" + label.category_id
                    ];
                }
            }

            if (!this.menu.name) {
                this.menu.name = "Sans " + label.name;
                this.menu.color = label.color;
                this.menu.bgcolor = label.bgcolor;
            }
        },

        /**
         * Include all labels from current category into menu filters
         * @param {*} value
         */
        categoryChecked(value) {
            if (!this.menu.filters) {
                this.menu.filters = {};
            }

            // Label is checked
            if (value) {
                this.menu.filters["withCategory_" + this.menuCategory.id] = [];
                // Label is not checked
            } else {
                // Remove the label from the menu filters
                if (this.menu.filters["withCategory_" + this.menuCategory.id]) {
                    delete this.menu.filters[
                        "withCategory_" + this.menuCategory.id
                    ];
                }
            }

            if (!this.menu.name) {
                this.menu.name = this.menuCategory.name;
                this.menu.color = this.menuCategory.color;
                this.menu.bgcolor = this.menuCategory.bgcolor;
            }
        },

        /**
         * Exclude all labels from current category from menu filters
         * @param {*} value
         */
        categoryExcluded(value) {
            if (!this.menu.filters) {
                this.menu.filters = {};
            }

            // Label is checked
            if (value) {
                this.menu.filters["withoutCategory_" + this.menuCategory.id] =
                    [];
                // Label is not checked
            } else {
                // Remove the label from the menu filters
                if (
                    this.menu.filters["withoutCategory_" + this.menuCategory.id]
                ) {
                    delete this.menu.filters[
                        "withoutCategory_" + this.menuCategory.id
                    ];
                }
            }

            if (!this.menu.name) {
                this.menu.name = "Sans " + this.menuCategory.name;
                this.menu.color = this.menuCategory.color;
                this.menu.bgcolor = this.menuCategory.bgcolor;
            }
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
            store.commit(SET_CATEGORY, this.menuCategory);
            store.commit(OPEN_MODAL, "label-add");
        },

        /**
         *
         * @param {*} category
         * @param {*} keyword
         */
        filteredCategoryLabels(category, keyword) {
            keyword = removeStringAccent(keyword);

            return category.labels.filter(
                (label) => removeStringAccent(label.name).indexOf(keyword) >= 0
            );
        },
    },

    watch: {
        async menuCategory(newValue) {
            if (newValue) {
                this.tab = 2;

                if (
                    this.categoryKeyword &&
                    this.filteredCategoryLabels(newValue, this.categoryKeyword)
                        .length > 0
                ) {
                    this.labelKeyword = this.categoryKeyword;
                } else {
                    this.labelKeyword = "";
                }

                /*const category = this.categories.find(
                    (c) => c.id == newValue.id
                );

                if (category && category.labels === undefined) {
                    this.fetchingLabels = true;
                }

                try {
                    await store.dispatch(FETCH_LABELS, newValue.id);
                } finally {
                    this.fetchingLabels = false;
                }*/
            } else {
                this.tab = 1;
            }
        },
    },

    computed: {
        ...mapGetters([
            "prospectsParams",
            "categories",
            "prospectsParamExists",
            "users",
        ]),

        /**
         *
         * @param {*} label
         */
        isCategoryChecked() {
            return (
                this.menuCategory &&
                this.menu.filters &&
                Array.isArray(
                    this.menu.filters["withCategory_" + this.menuCategory.id]
                ) &&
                this.menu.filters["withCategory_" + this.menuCategory.id]
                    .length == 0
            );
        },

        /**
         *
         * @param {*} label
         */
        isCategoryExcluded(label) {
            return (
                this.menuCategory &&
                this.menu.filters &&
                Array.isArray(
                    this.menu.filters["withoutCategory_" + this.menuCategory.id]
                ) &&
                this.menu.filters["withoutCategory_" + this.menuCategory.id]
                    .length == 0
            );
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
                        this.filteredCategoryLabels(
                            category,
                            this.categoryKeyword
                        ).length > 0)
            );
        },

        /**
         *
         */
        filteredLabels() {
            if (!this.menuCategory) {
                return [];
            }

            const category = this.categories.find(
                (c) => c.id == this.menuCategory.id
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
        filteredUsers() {
            const keyword = removeStringAccent(this.userKeyword);

            return this.users.filter(
                (user) => removeStringAccent(user.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
