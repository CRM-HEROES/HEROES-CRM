<template>
    <item-list>
        <template v-for="(value, productId) in orderProducts" :key="productId">
            <order-product
                v-if="getProduct(productId)"
                :product="getProduct(productId)"
                :value="value"
                @updated="updateProduct"
                @remove="removeProduct"
            />
        </template>
        <div style="width: 100%">
            <label class="hc-campaign-item-value-row">
                <div
                    class="hc-campaign-item-value-row-label"
                    v-text="'Ajouter un produit'"
                ></div>
                <div class="hc-campaign-item-value-row-select">
                    <select @change="addProduct">
                        <option v-text="'Produit ...'"></option>
                        <option
                            v-for="product in availableProducts"
                            :value="product.id"
                            v-text="product.name"
                        ></option>
                    </select>
                </div>
            </label>
        </div>
    </item-list>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import { UPDATE_CAMPAIGN_ACTION } from "@/actions/project/campaign";

import OrderProduct from "./OrderProduct.vue";

export default {
    components: {
        OrderProduct,
    },

    props: {
        action: {
            type: Object,
        },
    },

    methods: {
        addProduct(e) {
            if (!e.target.value) {
                return;
            }
            store.dispatch(UPDATE_CAMPAIGN_ACTION, {
                id: this.action.id,
                value: {
                    ...this.action.value,
                    products: {
                        ...this.orderProducts,
                        [e.target.value]: { quantity: 1 },
                    },
                },
            });
        },

        updateProduct(id, value) {
            store.dispatch(UPDATE_CAMPAIGN_ACTION, {
                id: this.action.id,
                value: {
                    ...this.action.value,
                    products: {
                        ...this.orderProducts,
                        [id]: {
                            ...(this.orderProducts[id]
                                ? this.orderProducts[id]
                                : {}),
                            ...value,
                        },
                    },
                },
            });
        },

        removeProduct(id) {
            store.dispatch(UPDATE_CAMPAIGN_ACTION, {
                id: this.action.id,
                value: {
                    ...this.action.value,
                    products: Object.fromEntries(
                        Object.entries(this.orderProducts).filter(
                            ([k, v]) => k != id
                        )
                    ),
                },
            });
        },

        getProduct(id) {
            return this.products.find((p) => p.id == id);
        },
    },

    computed: {
        ...mapGetters(["products"]),

        orderProducts() {
            return this.action.value && this.action.value.products
                ? this.action.value.products
                : {};
        },

        availableProducts() {
            const orderProductsId = Object.keys(this.orderProducts);

            return this.products.filter(
                (product) => !orderProductsId.find((op) => op == product.id)
            );
        },
    },
};
</script>
