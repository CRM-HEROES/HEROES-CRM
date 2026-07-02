<template>
    <form
        class="hc-flex-column"
        v-if="menuToUpdate"
        style="overflow: hidden"
        @submit.prevent="update"
    >
        <tab-layout
            :count="4"
            :tab="tab"
            class="hc-flex-1"
            style="height: 100%"
        >
            <template #1>
                <item-list
                    gap="5px"
                    style="height: 100%; overflow: auto"
                    padding="10px 0"
                >
                    <v-field :label="$t('name')" required v-slot="{ label }"
                        ><input
                            ref="menuName"
                            :placeholder="label + ' ...'"
                            v-model="menuToUpdate.name"
                            :style="{
                                color: menuToUpdate.color,
                                backgroundColor: menuToUpdate.bgcolor,
                            }"
                            required
                    /></v-field>
                    <v-field :label="$t('color')"
                        ><input type="color" v-model="menuToUpdate.color"
                    /></v-field>
                    <color-palette v-model="menuToUpdate.color" />
                    <v-field :label="$t('bgcolor')"
                        ><input type="color" v-model="menuToUpdate.bgcolor"
                    /></v-field>
                    <color-palette v-model="menuToUpdate.bgcolor" />
                    <item tag="label" v-if="prospectsParamExists()">
                        <icon class="fa fa-filter" :style="style" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('menu.use_current_filters')"
                        ></div>
                        <checkbox v-model="useCurrentFilters" />
                    </item>
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
                    <item @click="tab = 3">
                        <icon class="fa fa-users" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('label.affected_users')"
                        ></div>
                        <icon class="fa fa-caret-right" />
                    </item>
                </item-list>
            </template>

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
                            :count="selectedLabelsCount(c)"
                            @click="setCategory(c)"
                        />
                    </item-list>
                </div>
            </template>

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
                        <item-list
                            class="hc-flex-1"
                            padding="12px"
                            style="max-height: 400px"
                        >
                            <label-row
                                v-for="label in filteredLabels"
                                :key="label.id"
                                :label="label"
                                :value="isLabelChecked(label)"
                                @checked="labelChecked"
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
                    <item-list class="hc-flex-1" padding="5px">
                        <user-row
                            v-for="user in filteredUsers"
                            :key="user.id"
                            :user="user"
                            :menu="menu"
                            v-model="menuUsers"
                        />
                    </item-list>
                    <loading :loading="fetchingMenuUsers" />
                </div>
            </template>
        </tab-layout>
        <buttons>
            <button
                @click.prevent="remove"
                class="hc-button-danger"
                v-text="$t('delete')"
            ></button>
            <button v-text="$t('update')"></button>
        </buttons>
        <loading :loading="updatingMenu || removingMenu" />
    </form>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import UserMenuService from "@/apis/project/user/menu";

// Actions
import { SHOW_MENU, UPDATE_MENU, REMOVE_MENU } from "@/actions/project/menu";
import { CLOSE_MODAL } from "@/actions/modal";
import {
    FETCH_PROSPECTS,
    INIT_PROSPECT_PARAMS,
} from "@/actions/project/prospect";
import { SET_CATEGORY } from "@/actions/project/category";
import { FETCH_LABELS } from "@/actions/project/label";

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
            updatingMenu: false,
            removingMenu: false,
            fetchingMenu: false,
            fetchingMenuUsers: false,
            menuToUpdate: this.menu,
            menuCategory: null,
            categoryKeyword: "",
            labelKeyword: "",
            useCurrentFilters: false,
            userKeyword: "",
            menuUsers: [],
        };
    },

    created() {
        this.menuToUpdate = this.menu;
    },

    methods: {
        /**
         *
         */
        async update() {
            if (this.useCurrentFilters) {
                this.menuToUpdate.filters = this.prospectsParams;
            }

            this.updatingMenu = true;

            try {
                await store.dispatch(UPDATE_MENU, this.menuToUpdate);
                store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.updatingMenu = false;
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingMenu = true;

                try {
                    await store.dispatch(REMOVE_MENU, this.menuToUpdate.id);
                    if (
                        this.prospectsMenu &&
                        this.prospectsMenu.id == this.menuToUpdate.id
                    ) {
                        store.commit(INIT_PROSPECT_PARAMS);
                    }

                    store.dispatch(FETCH_PROSPECTS);
                } finally {
                    this.removingMenu = false;
                    store.commit(CLOSE_MODAL);
                }
            });
        },

        /**
         *
         * @param {*} category
         */
        setCategory(category) {
            this.menuCategory = category;
        },

        /**
         *
         * @param {*} category
         */
        selectedLabelsCount(category) {
            return this.menuToUpdate.filters &&
                this.menuToUpdate.filters["withCategory_" + category.id]
                ? this.menuToUpdate.filters["withCategory_" + category.id]
                      .length
                : 0;
        },

        /**
         *
         * @param {*} label
         */
        isLabelChecked(label) {
            return (
                this.menuToUpdate.filters &&
                this.menuToUpdate.filters[
                    "withCategory_" + label.category_id
                ] &&
                this.menuToUpdate.filters[
                    "withCategory_" + label.category_id
                ].indexOf(label.id) >= 0
            );
        },

        /**
         *
         * @param {*} value
         */
        labelChecked(label, value) {
            if (!this.menuToUpdate.filters) {
                this.menuToUpdate.filters = {};
            }

            // Label is checked
            if (value) {
                // Add the label into the menu filters
                if (
                    !this.menuToUpdate.filters[
                        "withCategory_" + label.category_id
                    ]
                ) {
                    this.menuToUpdate.filters[
                        "withCategory_" + label.category_id
                    ] = [];
                }

                this.menuToUpdate.filters[
                    "withCategory_" + label.category_id
                ].push(label.id);
                // Label is not checked
            } else {
                // Remove the label from the menu filters
                if (
                    this.menuToUpdate.filters[
                        "withCategory_" + label.category_id
                    ]
                ) {
                    this.menuToUpdate.filters[
                        "withCategory_" + label.category_id
                    ] = this.menuToUpdate.filters[
                        "withCategory_" + label.category_id
                    ].filter((labelId) => labelId != label.id);
                }

                // If no label is selected
                // in the category
                // remove the category from
                // the menu filters
                if (
                    this.menuToUpdate.filters[
                        "withCategory_" + label.category_id
                    ].length == 0
                ) {
                    delete this.menuToUpdate.filters[
                        "withCategory_" + label.category_id
                    ];
                }
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
         */
        async fetchMenuUsers() {
            this.fetchingMenuUsers = true;

            try {
                const { data } = await UserMenuService.users(
                    this.project.slug,
                    this.menu.id
                );
                this.menuUsers = data.map((user) => user.id);
            } finally {
                this.fetchingMenuUsers = false;
            }
        },
    },

    watch: {
        async menuCategory(newValue) {
            if (newValue) {
                this.tab = 2;

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
                this.tab = 1;
            }
        },

        async menu(newValue) {
            if (newValue) {
                this.menuToUpdate = newValue;

                this.fetchingMenu = true;

                try {
                    this.menuToUpdate = await store.dispatch(
                        SHOW_MENU,
                        newValue.id
                    );
                } finally {
                    this.fetchingMenu = false;
                }
            }
        },

        tab(newValue) {
            if (newValue == 3) {
                this.fetchMenuUsers();
            }
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "menu",
            "prospectsParams",
            "categories",
            "prospectsParamExists",
            "prospectsMenu",
            "users",
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
