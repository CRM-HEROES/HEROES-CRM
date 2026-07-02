<template>
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
                <item @click="tab = 0" style="border-bottom: 1px solid #eee">
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content"
                        v-text="
                            $t('prospect.order.product.add_product_to_order', {
                                product: productToAdd.name,
                            })
                        "
                    ></div>
                </item>
                <item-list gap="5px" padding="12px" class="hc-flex-1">
                    <h-field
                        :label="$t('prospect.order.product.quantity')"
                        required
                        v-slot="{ label }"
                        ><input
                            required
                            type="number"
                            :placeholder="label + ' ...'"
                            v-model.lazy="productToAdd.quantity"
                    /></h-field>
                    <h-field
                        :label="$t('prospect.order.product.price')"
                        required
                        v-slot="{ label }"
                        ><input
                            required
                            :placeholder="label + ' ...'"
                            v-model.lazy="productToAddPrice"
                    /></h-field>
                    <h-field
                        :label="$t('prospect.order.product.currency')"
                        required
                        v-slot="{ label }"
                        ><input
                            required
                            :placeholder="label + ' ...'"
                            v-model.lazy="productToAdd.currency"
                    /></h-field>
                    <h-field
                        :label="$t('prospect.order.product.tax_in_percent')"
                        required
                        v-slot="{ label }"
                        ><input
                            required
                            :placeholder="label + ' ...'"
                            v-model.lazy="productToAddTax"
                    /></h-field>
                    <item tag="label" style="padding: 0 5px">
                        <icon class="fa fa-euro" :style="style" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('prospect.order.product.tax_included')"
                        ></div>
                        <checkbox v-model="productToAdd.including_tax" />
                    </item>
                    <order-product-field-row
                        v-for="field in orderProductMetaFields"
                        :key="field.id"
                        :field="field"
                        v-model="meta[field.slug]"
                    />
                    <action-row
                        v-for="commission in productToAddCommissions"
                        :key="commission.id"
                        :commission="commission"
                    />
                </item-list>
                <buttons>
                    <button
                        @click.prevent="addField"
                        v-text="$t('prospect.order.tabs.fields.add')"
                        class="hc-button-grey"
                    ></button>
                    <button
                        v-text="$t('prospect.order.product.add_to_order')"
                    ></button>
                </buttons>
            </form>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { OPEN_MODAL } from "@/actions/modal";
import { ADD_ORDER_PRODUCT } from "@/actions/project/prospect/order/product";
import { SHOW_PROSPECT_ORDER } from "@/actions/project/prospect/order";
import { SET_NEW_FIELD_FOR } from "@/actions/project/field";

// Components
import ActionRow from "./Product/ActionRow.vue";
import ProductRow from "./Product/ProductRow.vue";
import OrderProductFieldRow from "./Product/OrderProductFieldRow.vue";
import AddedProductRow from "./Product/AddedProductRow.vue";

export default {
    components: {
        ActionRow,
        ProductRow,
        OrderProductFieldRow,
        AddedProductRow,
    },

    data() {
        return {
            tab: 0,
            productKeyword: "",
            productToAdd: null,
            meta: {},
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
                currency: product.pivot.currency,
                including_tax: product.pivot.including_tax,
                price: product.pivot.price,
                tax: product.pivot.tax,
                quantity: product.pivot.quantity,
                meta: product.pivot.meta,
            };

            meta = product.pivot.meta;
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
            productToAdd.meta = this.meta;
            this.productToAdd = null;

            try {
                await store.dispatch(ADD_ORDER_PRODUCT, productToAdd);
                this.$emit("product-selected", productToAdd);
                store.dispatch(SHOW_PROSPECT_ORDER, this.prospectOrder.id);
            } finally {
            }
        },

        addField() {
            store.commit(OPEN_MODAL, "field-add");
            store.commit(SET_NEW_FIELD_FOR, "order-product");
        },
    },

    watch: {
        productToAdd(newValue) {
            if (newValue) {
                this.tab = 1;
                this.meta = newValue.meta ? newValue.meta : {};
            } else {
                this.tab = 0;
            }
        },
    },

    computed: {
        ...mapGetters([
            "products",
            "prospectOrder",
            "prospectOrderCommissions",
            "fields",
            "can",
        ]),

        /**
         *
         */
        productToAddPrice: {
            get() {
                return parseFloat(
                    (this.productToAdd.price + "").replace(",", ".")
                );
            },
            set(value) {
                value = parseFloat(value.replace(",", "."));

                if (value < 0) {
                    value = 0;
                }

                this.productToAdd.price = value;
            },
        },

        /**
         *
         */
        productToAddTax: {
            get() {
                return (
                    parseFloat((this.productToAdd.tax + "").replace(",", ".")) *
                    100
                );
            },
            set(value) {
                value = parseFloat(value.replace(",", "."));

                if (value < 0) {
                    value = 0;
                }

                if (value > 100) {
                    value = 100;
                }

                this.productToAdd.tax = value / 100;
            },
        },

        /**
         *
         */
        filteredAddedProducts() {
            const keyword = removeStringAccent(this.productKeyword);

            return this.prospectOrder.products.filter(
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
                    !this.prospectOrder.products.find(
                        (p) => p.id == product.id
                    ) && removeStringAccent(product.name).indexOf(keyword) >= 0
            );
        },

        productToAddCommissions() {
            if (!this.productToAdd) {
                return [];
            }

            return this.prospectOrderCommissions.filter(
                (poc) => poc.product_id == this.productToAdd.id
            );
        },

        /**
         *
         */
        orderProductMetaFields() {
            return this.fields.filter(
                (field) => field.meta && field.for == "order-product"
            );
        },
    },
};
</script>
