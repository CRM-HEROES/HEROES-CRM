<template>
    <slide
        :name="name"
        :title="$t('campaign.action.prospect.attach_user.title')"
        icon="fa fa-user"
        style="width: 260px"
    >
        <tab-layout :count="2" :tab="tab" class="hc-flex-1">
            <template #1>
                <div class="hc-flex-column" style="height: 100%">
                    <search v-model="productKeyword" />
                    <item-list padding="12px" class="hc-flex-1">
                        <added-product-row
                            v-for="product in filteredAddedProducts"
                            :key="product.id"
                            :product="product"
                            @click="updateProductFromOrder(product)"
                            @remove="removeProductFromOrder(product)"
                        />
                        <product-row
                            v-for="product in filteredProducts"
                            :key="product.id"
                            :product="product"
                            @click="addProductToOrder(product)"
                        />
                    </item-list>
                    <buttons v-if="can('all.project.order.product.add')">
                        <a @click.prevent="addProduct" v-text="$t('new')"></a>
                        <campaign-template
                            tag="button"
                            :field="action"
                            @dragging="dragging"
                            @dragged="dragged"
                            v-text="$t('campaign.action.prospect.order.add')"
                            :disabled="
                                Object.values(action.value.products).length == 0
                            "
                        ></campaign-template>
                    </buttons>
                </div>
            </template>
            <template #2>
                <form
                    class="hc-flex-column"
                    style="height: 100%"
                    v-if="productToAdd"
                    @submit.prevent="storeProductToOrder"
                >
                    <item
                        @click="tab = 0"
                        style="border-bottom: 1px solid #eee"
                    >
                        <icon class="fa fa-caret-left" />
                        <div
                            class="hc-item-main-content"
                            v-text="
                                $t(
                                    'prospect.order.product.add_product_to_order',
                                    {
                                        product: productToAdd.name,
                                    }
                                )
                            "
                        ></div>
                    </item>
                    <item-list gap="5px" padding="12px" class="hc-flex-1">
                        <v-field
                            :label="$t('prospect.order.product.quantity')"
                            required
                            v-slot="{ label }"
                            ><input
                                required
                                type="number"
                                :placeholder="label + ' ...'"
                                v-model="productToAdd.quantity"
                        /></v-field>
                        <v-field
                            :label="$t('prospect.order.product.price')"
                            required
                            v-slot="{ label }"
                            ><input
                                required
                                type="number"
                                :placeholder="label + ' ...'"
                                v-model="productToAdd.price"
                        /></v-field>
                        <v-field
                            :label="$t('prospect.order.product.currency')"
                            required
                            v-slot="{ label }"
                            ><input
                                required
                                :placeholder="label + ' ...'"
                                v-model="productToAdd.currency"
                        /></v-field>
                        <v-field
                            :label="$t('prospect.order.product.tax_in_percent')"
                            required
                            v-slot="{ label }"
                            ><input
                                required
                                :placeholder="label + ' ...'"
                                v-model="productToAddTax"
                        /></v-field>
                        <item tag="label" style="padding: 0 5px">
                            <icon class="fa fa-euro" :style="style" />
                            <div
                                class="hc-item-main-content"
                                v-text="
                                    $t('prospect.order.product.tax_included')
                                "
                            ></div>
                            <checkbox v-model="productToAdd.including_tax" />
                        </item>
                    </item-list>
                    <buttons>
                        <button
                            v-text="$t('prospect.order.product.add_to_order')"
                        ></button>
                    </buttons>
                </form>
            </template>
        </tab-layout>
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { OPEN_MODAL } from "@/actions/modal";
import { CLOSE_SLIDE } from "@/actions/slide";

// Components
import ProductRow from "./ProductRow.vue";
import AddedProductRow from "./AddedProductRow.vue";
import CampaignTemplate from "@/components/campaign/components/Template.vue";

export default {
    components: {
        ProductRow,
        AddedProductRow,
        CampaignTemplate,
    },

    data() {
        return {
            tab: 0,
            name: "campaign-action-prospect-order",
            order: {
                products: {},
            },
            productKeyword: "",
            productToAdd: null,
            action: {
                action: "prospect-order",
                name: this.$t("campaign.action.prospect.order.name", {
                    name: "",
                }),
                value: {
                    products: {},
                },
                category: "action",
                style: {
                    width: "200px",
                },
            },
        };
    },

    methods: {
        /**
         *
         */
        addProduct() {
            store.commit(OPEN_MODAL, "product-add");
        },

        /**
         *
         * @param {*} product
         */
        updateProductFromOrder(product) {
            this.productToAdd = {
                id: product.id,
                name: product.name,
                currency: product.currency,
                including_tax: product.including_tax,
                price: product.price,
                tax: product.tax,
                quantity: product.quantity,
            };
        },

        /**
         *
         * @param {*} product
         */
        removeProductFromOrder(product) {
            this.order = {
                ...this.order,
                products: Object.fromEntries(
                    Object.entries(this.order.products).filter(
                        ([k]) => k != product.id
                    )
                ),
            };
        },

        /**
         *
         * @param {*} product
         */
        addProductToOrder(product) {
            this.productToAdd = {
                id: product.id,
                name: product.name,
                currency: product.currency,
                including_tax: product.including_tax,
                price: product.price,
                tax: product.tax,
                quantity: 1,
            };
        },

        /**
         *
         */
        async storeProductToOrder() {
            const productToAdd = this.productToAdd;
            this.productToAdd = null;

            this.order = {
                ...this.order,
                products: {
                    ...this.order.products,
                    [productToAdd.id]: productToAdd,
                },
            };
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
        productToAdd(newValue) {
            if (newValue) {
                this.tab = 1;
            } else {
                this.tab = 0;
            }
        },

        order(newValue) {
            this.action.name = this.$t("campaign.action.prospect.order.name", {
                name: Object.values(newValue.products)
                    .map((p) => p.name)
                    .join(", "),
            });

            this.action.value.products = newValue.products;
        },
    },

    computed: {
        ...mapGetters(["products", "prospectOrder", "can"]),

        /**
         *
         */
        filteredAddedProducts() {
            const keyword = removeStringAccent(this.productKeyword);

            return Object.values(this.order.products).filter(
                (product) =>
                    removeStringAccent(product.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredProducts() {
            const keyword = removeStringAccent(this.productKeyword);

            return this.products.filter(
                (product) =>
                    !this.filteredAddedProducts.find(
                        (p) => p.id == product.id
                    ) && removeStringAccent(product.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        productToAddTax: {
            get() {
                return this.productToAdd.tax * 100;
            },
            set(value) {
                this.productToAdd.tax = value / 100;
            },
        },
    },
};
</script>
