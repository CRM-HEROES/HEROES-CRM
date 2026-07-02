<template>
    <tab-layout :count="3" :tab="orderTab">
        <!-- List of orders -->

        <template #1>
            <div
                class="hc-flex-column"
                style="height: 100%; position: relative"
            >
                <!-- Back -->
                <item
                    @click="$emit('back')"
                    style="border-bottom: 1px solid #eee"
                >
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content hc-flex-column"
                        v-text="$t('event.choose_order')"
                    ></div>
                </item>

                <search v-model="orderKeyword" />

                <item-list
                    class="hc-flex-1"
                    padding="12px"
                    style="position: relative"
                >
                    <order-row
                        v-for="order in filteredOrders"
                        :key="order.id"
                        :order="order"
                        @click="$emit('order-selected', order)"
                    />
                </item-list>
                <buttons>
                    <a
                        @click.prevent="orderTab = 1"
                        v-text="$t('event.new_order')"
                    ></a>
                </buttons>
                <loading :loading="fetchingOrders" />
            </div>
        </template>

        <!-- List of products if user want to create a new order -->

        <template #2>
            <div
                class="hc-flex-column"
                style="height: 100%; position: relative"
            >
                <item
                    @click="orderTab = 0"
                    style="border-bottom: 1px solid #eee"
                >
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content hc-flex-column"
                        v-text="$t('back')"
                    ></div>
                </item>
                <search v-model="productKeyword" />
                <item-list padding="12px" class="hc-flex-1">
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

        <!-- Add the selected product to the new order -->

        <template #3>
            <form
                class="hc-flex-column"
                style="height: 100%; position: relative"
                v-if="productToAdd"
                @submit.prevent="storeProductToOrder"
            >
                <!-- Back to the list of products -->

                <item
                    @click="orderTab = 1"
                    style="border-bottom: 1px solid #eee"
                >
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
                    <!-- Product quantity -->

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

                    <!-- Product price -->

                    <h-field
                        :label="$t('prospect.order.product.price')"
                        required
                        v-slot="{ label }"
                        ><input
                            required
                            :placeholder="label + ' ...'"
                            v-model.lazy="productToAddPrice"
                    /></h-field>

                    <!-- Product currency -->

                    <h-field
                        :label="$t('prospect.order.product.currency')"
                        required
                        v-slot="{ label }"
                        ><input
                            required
                            :placeholder="label + ' ...'"
                            v-model.lazy="productToAdd.currency"
                    /></h-field>

                    <!-- Product tax in percent -->

                    <h-field
                        :label="$t('prospect.order.product.tax_in_percent')"
                        required
                        v-slot="{ label }"
                        ><input
                            required
                            :placeholder="label + ' ...'"
                            v-model.lazy="productToAddTax"
                    /></h-field>

                    <!-- Product, is tax included in the price -->

                    <item tag="label" style="padding: 0 5px">
                        <icon class="fa fa-euro" :style="style" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('prospect.order.product.tax_included')"
                        ></div>
                        <checkbox v-model="productToAdd.including_tax" />
                    </item>
                </item-list>

                <!-- Add the product to the order -->

                <buttons>
                    <button
                        v-text="$t('prospect.order.product.add_to_order')"
                    ></button>
                </buttons>
                <loading :loading="addingProductToOrder" />
            </form>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Action
import {
    FETCH_PROSPECT_ORDERS,
    ADD_PROSPECT_ORDER,
    SET_PROSPECT_ORDER
} from "@/actions/project/prospect/order";
import { ADD_ORDER_PRODUCT } from "@/actions/project/prospect/order/product";
import { SET_PROSPECT } from "@/actions/project/prospect";

// Components
import OrderRow from "./OrderRow.vue";
import ProductRow from "./ProductRow.vue";

export default {
    components: {
        OrderRow,
        ProductRow,
    },

    props: {
        prospect: {
            type: Object,
        },
    },

    data() {
        return {
            orderTab: 0,
            orderKeyword: "",
            productKeyword: "",
            productToAdd: null,
            addingProductToOrder: false,
            fetchingOrders: false,
        };
    },

    created() {
        this.fetchOrders();
    },

    methods: {
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
            this.orderTab = 2;
        },

        /**
         *
         */
        async storeProductToOrder() {
            this.addingProductToOrder = true;
            const productToAdd = this.productToAdd;

            try {
                let order = await store.dispatch(ADD_PROSPECT_ORDER, {});
                await store.commit(SET_PROSPECT_ORDER, order);
                await store.dispatch(ADD_ORDER_PRODUCT, productToAdd);
                order.name = productToAdd.name;
                this.$emit("order-selected", order);
            } finally {
                this.addingProductToOrder = false;
                this.orderTab = 0;
                this.tab = 0;
                this.productToAdd = null;
            }
        },

        /**
         *
         */
        async fetchOrders() {
            if (this.prospect) {
                if (this.prospectOrders.length == 0) {
                    this.fetchingOrders = true;
                }
                store.commit(SET_PROSPECT, this.prospect);

                try {
                    await store.dispatch(FETCH_PROSPECT_ORDERS);
                } finally {
                    this.fetchingOrders = false;
                }
            }
        },
    },

    watch: {
        prospect() {
            this.fetchOrders();
        },
    },

    computed: {
        ...mapGetters(["prospectOrders", "can", "products"]),

        /**
         *
         */
        filteredOrders() {
            const keyword = removeStringAccent(this.orderKeyword);

            return this.prospectOrders.filter(
                (order) => removeStringAccent(order.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredProducts() {
            const keyword = removeStringAccent(this.productKeyword);

            return this.products.filter(
                (product) =>
                    removeStringAccent(product.name).indexOf(keyword) >= 0
            );
        },

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
    },
};
</script>
