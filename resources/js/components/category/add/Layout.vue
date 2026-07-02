<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <form
                class="hc-flex-column"
                style="height: 100%"
                @submit.prevent="storeCategory"
            >
                <item-list gap="5px">
                    <v-field :label="$t('name')" required v-slot="{ label }"
                        ><input
                            required
                            :placeholder="label + ' ...'"
                            :style="{
                                color: category.color,
                                backgroundColor: category.bgcolor,
                            }"
                            v-model="category.name"
                    /></v-field>
                    <v-field :label="$t('description')" v-slot="p">
                        <textarea
                            v-model="category.description"
                            :placeholder="p.label + ' ...'"
                        ></textarea>
                    </v-field>
                    <v-field :label="$t('for')" required>
                        <select v-model="category.for">
                            <option
                                value="prospect"
                                v-text="$t('prospects')"
                            ></option>
                            <option value="user" v-text="$t('users')"></option>
                            <option
                                value="order"
                                v-text="$t('orders')"
                            ></option>
                            <option
                                value="product"
                                v-text="$t('products')"
                            ></option>
                        </select>
                    </v-field>
                    <v-field :label="$t('color')"
                        ><input type="color" v-model="category.color"
                    /></v-field>
                    <color-palette v-model="category.color" />
                    <v-field :label="$t('bgcolor')"
                        ><input type="color" v-model="category.bgcolor"
                    /></v-field>
                    <color-palette v-model="category.bgcolor" />
                    <item tag="label" style="padding: 0 5px">
                        <icon class="fa fa-check-circle" :style="style" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('category.add.unique')"
                        ></div>
                        <checkbox v-model="category.unique" />
                    </item>
                </item-list>
                <item @click="tab = 1">
                    <icon class="fa fa-users" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('label.affected_users')"
                    ></div>
                    <div
                        v-if="categoryUsers.length"
                        class="hc-item-count"
                        v-text="categoryUsers.length"
                    ></div>
                    <icon class="fa fa-caret-right" />
                </item>
                <buttons>
                    <button v-text="$t('add')"></button>
                </buttons>
                <loading :loading="addingCategory" />
            </form>
        </template>

        <template #2>
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
                        v-model="categoryUsers"
                    />
                </item-list>
            </div>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { ADD_CATEGORY, SET_CATEGORY } from "@/actions/project/category";
import { OPEN_SLIDE } from "@/actions/slide";
import { CLOSE_MODAL } from "@/actions/modal";
import { BULK_ADD_USER_CATEGORY } from "@/actions/project/user/category";

import UserRow from "./UserRow.vue";

export default {
    components: {
        UserRow,
    },

    data() {
        return {
            category: this.newCategory(),
            addingCategory: false,
            userKeyword: "",
            categoryUsers: [],
            tab: 0,
        };
    },

    methods: {
        /**
         *
         */
        newCategory() {
            return {
                name: "",
                description: "",
                for: "prospect",
                color: "#000000",
                bgcolor: "#FFFFFF",
                unique: false,
            };
        },

        /**
         *
         */
        async storeCategory() {
            this.addingCategory = true;

            try {
                const category = await store.dispatch(
                    ADD_CATEGORY,
                    this.category
                );
                store.commit(OPEN_SLIDE, "categories");
                store.commit(SET_CATEGORY, category);

                if (this.categoryUsers.length > 0) {
                    await store.dispatch(BULK_ADD_USER_CATEGORY, {
                        users: this.categoryUsers,
                        categories: [category.id],
                    });
                }
            } finally {
                this.addingCategory = false;
                this.category = this.newCategory();
                this.categoryUsers = [];
                store.commit(CLOSE_MODAL);
            }
        },
    },

    computed: {
        ...mapGetters(["users"]),

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
