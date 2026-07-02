<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <form
                class="hc-flex-column"
                style="height: 100%"
                v-if="categoryToUpdate"
                @submit.prevent="update"
            >
                <item-list gap="5px" class="hc-flex-1" padding="10px 0">
                    <v-field :label="$t('name')" required v-slot="p"
                        ><input
                            ref="categoryName"
                            required
                            :placeholder="p.label + ' ...'"
                            :style="{
                                color: categoryToUpdate.color,
                                backgroundColor: categoryToUpdate.bgcolor,
                            }"
                            v-model="categoryToUpdate.name"
                    /></v-field>
                    <v-field :label="$t('description')" v-slot="p">
                        <textarea
                            v-model="categoryToUpdate.description"
                            :placeholder="p.label + ' ...'"
                        ></textarea>
                    </v-field>
                    <v-field :label="$t('color')"
                        ><input type="color" v-model="categoryToUpdate.color"
                    /></v-field>
                    <color-palette v-model="categoryToUpdate.color" />
                    <v-field :label="$t('bgcolor')"
                        ><input type="color" v-model="categoryToUpdate.bgcolor"
                    /></v-field>
                    <color-palette v-model="categoryToUpdate.bgcolor" />
                    <item tag="label" style="padding: 0 5px">
                        <icon class="fa fa-check-circle" :style="style" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('category.update.unique')"
                        ></div>
                        <checkbox v-model="categoryToUpdate.unique" />
                    </item>
                </item-list>
                <item @click="(tab = 1), (frameTab = 0)">
                    <icon class="fa fa-users" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('label.affected_users')"
                    ></div>
                    <icon class="fa fa-caret-right" />
                </item>
                <item @click="(tab = 1), (frameTab = 1)">
                    <icon class="fa fa-object-group" />
                    <div
                        class="hc-item-main-content"
                        v-text="'Combiner avec la catégorie ...'"
                    ></div>
                    <icon class="fa fa-caret-right" />
                </item>
                <buttons>
                    <button
                        v-if="can('all.project.category.delete')"
                        @click.prevent="remove"
                        class="hc-button-danger"
                        v-text="$t('delete')"
                    ></button>
                    <button
                        @click.prevent="toField"
                        class="hc-button-warning"
                        v-text="$t('to_field')"
                        v-tuto="{
                            key: 'project.category.to-field',
                            name: $t('tutorial.project_category_to_field.name'),
                            body:
                                $t(
                                    'tutorial.project_category_to_field.body.0'
                                ) +
                                '<br>' +
                                $t('tutorial.project_category_to_field.body.1'),
                            timeout: 500,
                        }"
                    ></button>
                    <button v-text="$t('update')"></button>
                </buttons>
                <loading
                    :loading="
                        updatingCategory || removingCategory || turningIntoField
                    "
                />
            </form>
        </template>

        <template #2>
            <frame-layout :count="2" :tab="frameTab" class="hc-flex-1">
                <template #1>
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
                            <!-- All -->
                            <item tag="label">
                                <icon class="fa fa-check-square" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="$t('all')"
                                ></div>
                                <checkbox v-model="all" />
                            </item>
                            <user-row
                                v-for="user in filteredUsers"
                                :key="user.id"
                                :user="user"
                                :category="category"
                                v-model="categoryUsers"
                            />
                        </item-list>
                        <loading :loading="fetchingCategoryUsers" />
                    </div>
                </template>
                <template #2>
                    <div class="hc-flex-column" style="height: 100%">
                        <item @click="tab = 0">
                            <icon class="fa fa-caret-left" />
                            <div
                                class="hc-item-main-content"
                                v-text="'Combiner avec la catégorie ...'"
                            ></div>
                        </item>
                        <search v-model="categoryKeyword" />
                        <item-list
                            class="hc-flex-1"
                            padding="5px"
                            style="max-height: 400px"
                        >
                            <category-row
                                v-for="category in filteredCategories"
                                :key="category.id"
                                :category="category"
                                @click="combineWithCategory(category)"
                            />
                        </item-list>
                    </div>
                </template>
            </frame-layout>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import UserCategoryService from "@/apis/project/user/category";

// Actions
import {
    CATEGORY_TO_FIELD,
    CATEGORY_COMBINE_WITH,
    SHOW_CATEGORY,
    UPDATE_CATEGORY,
    REMOVE_CATEGORY,
} from "@/actions/project/category";
import { GET_PROJECT_USER_SETTING } from "@/actions/project/user/setting";
import { FETCH_PROSPECTS } from "@/actions/project/prospect";
import { CLOSE_MODAL } from "@/actions/modal";
import {
    BULK_REMOVE_USER_CATEGORY,
    BULK_ADD_USER_CATEGORY,
} from "@/actions/project/user/category";

import CategoryRow from "./CategoryRow.vue";
import UserRow from "./UserRow.vue";

export default {
    components: {
        CategoryRow,
        UserRow,
    },

    data() {
        return {
            tab: 0,
            frameTab: 0,
            updatingCategory: false,
            removingCategory: false,
            fetchingCategory: false,
            fetchingCategoryUsers: false,
            turningIntoField: false,
            combining: false,
            categoryToUpdate: this.category,
            userKeyword: "",
            categoryKeyword: "",
            categoryUsers: [],
        };
    },

    created() {
        this.categoryToUpdate = this.category;
    },

    methods: {
        /**
         *
         */
        async update() {
            this.updatingCategory = true;

            try {
                await store.dispatch(UPDATE_CATEGORY, this.categoryToUpdate);
            } finally {
                this.updatingCategory = false;
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingCategory = true;

                try {
                    await store.dispatch(
                        REMOVE_CATEGORY,
                        this.categoryToUpdate.id
                    );
                } finally {
                    this.removingCategory = false;
                    store.commit(CLOSE_MODAL);
                }
            });
        },

        combineWithCategory(category) {
            hcConfirm(
                `Etes vous sûr de combiner la catégorie "${this.categoryToUpdate.name}" avec "${category.name}"`,
                async () => {
                    this.combining = true;

                    try {
                        await store.dispatch(CATEGORY_COMBINE_WITH, {
                            category1: this.categoryToUpdate.id,
                            category2: category.id,
                        });

                        store.dispatch(FETCH_LABELS, category.id);
                        store.dispatch(FETCH_PROSPECTS);
                    } finally {
                        this.combining = false;
                        store.commit(CLOSE_MODAL);
                    }
                }
            );
        },

        /**
         *
         */
        toField() {
            hcConfirm(this.$t("to_field_confirm"), async () => {
                this.turningIntoField = true;

                try {
                    await store.dispatch(
                        CATEGORY_TO_FIELD,
                        this.categoryToUpdate.id
                    );

                    store.dispatch(GET_PROJECT_USER_SETTING, {
                        key: "prospects-table",
                    });
                    store.dispatch(FETCH_PROSPECTS);
                } finally {
                    this.turningIntoField = false;
                    store.commit(CLOSE_MODAL);
                }
            });
        },

        /**
         *
         */
        async fetchCategoryUsers() {
            this.fetchingCategoryUsers = true;

            try {
                const { data } = await UserCategoryService.users(
                    this.project.slug,
                    this.category.id
                );
                this.categoryUsers = data.map((user) => user.id);
            } finally {
                this.fetchingCategoryUsers = false;
            }
        },
    },

    watch: {
        async category(newValue) {
            if (newValue) {
                this.categoryToUpdate = newValue;

                this.fetchingCategory = true;

                try {
                    this.categoryToUpdate = await store.dispatch(
                        SHOW_CATEGORY,
                        newValue.id
                    );
                } finally {
                    this.fetchingCategory = false;
                }
            }
        },

        tab(newValue) {
            if (newValue == 1) {
                this.fetchCategoryUsers();
            }
        },
    },

    computed: {
        ...mapGetters(["project", "category", "users", "categories", "can"]),

        /**
         *
         */
        all: {
            get: function () {
                return (
                    // this.can("all") ||
                    this.categoryUsers.length == this.users.length
                );
            },
            set: async function (value) {
                if (value) {
                    const users = this.users.map((f) => f.id);

                    store.dispatch(BULK_ADD_USER_CATEGORY, {
                        users,
                        categories: [this.category.id],
                    });
                    this.categoryUsers = users;
                } else {
                    store.dispatch(BULK_REMOVE_USER_CATEGORY, {
                        users: this.users.map((f) => f.id),
                        categories: [this.category.id],
                    });
                    this.categoryUsers = [];
                }
            },
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
    },
};
</script>
