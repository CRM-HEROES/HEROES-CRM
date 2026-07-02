<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <form
                class="hc-flex-column"
                style="height: 100%; overflow: hidden"
                @submit.prevent="storeProduct"
            >
                <item-list class="hc-flex-1" gap="5px" style="overflow: auto">
                    <v-field :label="$t('name')" required v-slot="{ label }"
                        ><input
                            required
                            type="text"
                            :placeholder="label + ' ...'"
                            v-model="product.name"
                    /></v-field>
                    <v-field :label="$t('description')" v-slot="p">
                        <textarea
                            v-model="product.description"
                            :placeholder="p.label + ' ...'"
                        ></textarea>
                    </v-field>
                    <v-field
                        :label="$t('product.price')"
                        required
                        v-slot="{ label }"
                        ><input
                            required
                            type="number"
                            :placeholder="label + ' ...'"
                            v-model="product.price"
                    /></v-field>
                    <v-field
                        :label="$t('product.currency')"
                        required
                        v-slot="{ label }"
                        ><input
                            required
                            :placeholder="label + ' ...'"
                            v-model="product.currency"
                    /></v-field>
                    <v-field
                        :label="$t('product.tax_in_percent')"
                        required
                        v-slot="{ label }"
                        ><input
                            required
                            :placeholder="label + ' ...'"
                            v-model.lazy="tax"
                    /></v-field>
                    <item tag="label" style="padding: 0 5px">
                        <icon class="fa fa-euro" :style="style" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('product.included_tax')"
                        ></div>
                        <checkbox v-model="includingTax" />
                    </item>
                    <v-field :label="$t('product.image')" v-slot="{ label }"
                        ><input
                            type="file"
                            :placeholder="label + ' ...'"
                            @change="setImage"
                    /></v-field>
                </item-list>
                <item @click="tab = 1">
                    <icon class="fa fa-users" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('label.affected_users')"
                    ></div>
                    <div
                        v-if="productUsers.length"
                        class="hc-item-count"
                        v-text="productUsers.length"
                    ></div>
                    <icon class="fa fa-caret-right" />
                </item>
                <buttons>
                    <button v-text="$t('add')"></button>
                </buttons>
                <loading :loading="addingProduct" />
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
                        v-model="productUsers"
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
import { ADD_PRODUCT } from "@/actions/project/product";
import { CLOSE_MODAL } from "@/actions/modal";
import { BULK_ADD_USER_PRODUCT } from "@/actions/project/user/product";

import UserRow from "./UserRow.vue";

export default {
    components: {
        UserRow,
    },

    data() {
        return {
            product: this.newProduct(),
            addingProduct: false,
            userKeyword: "",
            productUsers: [],
            tab: 0,
        };
    },

    methods: {
        /**
         *
         */
        newProduct() {
            return {
                name: "",
                description: "",
                price: "",
                tax: 0,
                including_tax: 0,
                currency: "€",
            };
        },

        /**
         *
         */
        async storeProduct() {
            this.addingProduct = true;

            var formData = new FormData();
            for (const i in this.product) {
                formData.append(i, this.product[i]);
            }

            try {
                const product = await store.dispatch(ADD_PRODUCT, formData);

                if (this.productUsers.length > 0) {
                    await store.dispatch(BULK_ADD_USER_PRODUCT, {
                        users: this.productUsers,
                        products: [product.id],
                    });
                }
            } finally {
                this.addingProduct = false;
                this.product = this.newProduct();
                this.productUsers = [];
                store.commit(CLOSE_MODAL);
            }
        },

        setImage(e) {
            this.product.image = e.target.files[0];
        },
    },

    computed: {
        ...mapGetters(["users"]),

        /**
         *
         */
        all: {
            get: function () {
                return this.productUsers.length == this.users.length;
            },
            set: async function (value) {
                if (value) {
                    const users = this.users.map((f) => f.id);

                    this.productUsers = users;
                } else {
                    this.productUsers = [];
                }
            },
        },

        /**
         *
         */
        tax: {
            get() {
                return this.product.tax * 100;
            },
            set(value) {
                this.product.tax = parseFloat(value.replace(",", ".")) / 100;
            },
        },

        /**
         *
         */
        price: {
            get() {
                return this.product.price;
            },
            set(value) {
                this.product.price = parseFloat(value.replace(",", "."));
            },
        },

        /**
         *
         */
        includingTax: {
            get() {
                return this.product.including_tax == 1;
            },
            set(value) {
                this.product.including_tax = value ? 1 : 0;
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
    },
};
</script>
