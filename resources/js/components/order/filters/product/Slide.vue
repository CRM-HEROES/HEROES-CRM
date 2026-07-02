<template>
    <slide
        name="orders-table-filter-product"
        :title="$t('order.table.filters.products.title')"
        icon="fa fa-tags"
        style="width: 260px"
    >
        <div class="hc-flex-column" style="height: 100%">
            <search v-model="productKeyword" />
            <item-list class="hc-flex-1" padding="5px">
                <item tag="label" class="hc-orders-table-filter-product">
                    <icon class="fa fa-filter" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('order.table.filters.products.with')"
                    ></div>
                    <icon
                        tag="a"
                        class="fa fa-thumbs-down"
                        :style="excludeProductStyle"
                        :title="$t('order.table.filters.products.without')"
                        @click.prevent="toggleExcludeProduct"
                    />
                    <checkbox
                        :model-value="isCheckedProduct"
                        @change="changeProduct"
                    />
                </item>
                <product-row
                    v-for="product in filteredProducts"
                    :key="product.id"
                    :product="product"
                />
            </item-list>
            <buttons v-if="can('all.project.product.add')">
                <a @click.prevent="addProduct" v-text="$t('add')"></a>
            </buttons>
        </div>
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    REMOVE_ORDER_PARAMS,
    ADD_ORDER_PARAMS,
    FETCH_ORDERS,
} from "@/actions/project/order";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import ProductRow from "./ProductRow.vue";

export default {
    components: {
        ProductRow,
    },

    data() {
        return {
            tab: 0,
            productKeyword: "",
        };
    },

    methods: {
        /**
         *
         */
        addProduct() {
            store.commit(OPEN_MODAL, "order-product-add");
        },

        changeProduct(event) {
            // Remove without param
            store.commit(REMOVE_ORDER_PARAMS, {
                key: this.withoutKeyProduct,
            });

            // Add or remove with param
            store.commit(
                event.target.checked ? ADD_ORDER_PARAMS : REMOVE_ORDER_PARAMS,
                {
                    key: this.withKeyProduct,
                }
            );
            store.dispatch(FETCH_ORDERS);
        },

        toggleExcludeProduct() {
            // Add or remove with param
            store.commit(
                this.isExcludedProduct ? ADD_ORDER_PARAMS : REMOVE_ORDER_PARAMS,
                {
                    key: this.withKeyProduct,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcludedProduct ? REMOVE_ORDER_PARAMS : ADD_ORDER_PARAMS,
                {
                    key: this.withoutKeyProduct,
                }
            );

            store.dispatch(FETCH_ORDERS);
        },
    },

    computed: {
        ...mapGetters(["orderProducts", "ordersParamValue", "can"]),

        /**
         *
         */
        withKeyProduct() {
            return "withProducts";
        },

        /**
         *
         */
        withoutKeyProduct() {
            return "withoutProducts";
        },

        /**
         *
         */
        isCheckedProduct() {
            return (
                this.ordersParamValue(this.withKeyProduct) === "" ||
                this.ordersParamValue(this.withoutKeyProduct) === ""
            );
        },

        /**
         *
         */
        isExcludedProduct() {
            return this.ordersParamValue(this.withoutKeyProduct) === "";
        },

        /**
         *
         */
        excludeProductStyle() {
            return {
                color: this.isExcludedProduct ? "#CC0000" : "#CCCCCC",
            };
        },

        /**
         *
         */
        filteredProducts() {
            const keyword = removeStringAccent(this.productKeyword);

            return this.orderProducts.filter(
                (product) =>
                    removeStringAccent(product.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
./ProductRow.vue
