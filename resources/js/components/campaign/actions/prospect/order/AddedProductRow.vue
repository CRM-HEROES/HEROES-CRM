<template>
    <item class="hc-order-product hc-order-added-product">
        <div class="hc-order-product-image-thumbnail hc-flex-column">
            <div class="hc-order-product-image-thumbnail-content">
                <img
                    v-if="product.images && product.images.length > 0"
                    :src="product.images[0].thumbnail"
                />
            </div>
        </div>
        <div class="hc-item-main-content hc-flex-column hc-order-product-info">
            <div class="hc-order-product-name" v-text="product.name"></div>
        </div>
        <icon
            v-if="can('all.project.order.product.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
        <span
            class="hc-order-product-quantity"
            v-text="product.quantity"
        ></span>
        <icon
            tag="a"
            class="fa fa-times"
            @click.prevent.stop="removeOrderProduct"
        />
        <loading :loading="removingOrderProduct" />
    </item>
</template>

<style>
.hc-order-added-product {
    background-color: #0001;
    margin-top: -1px;
}

.hc-order-product-quantity {
    display: inline-block;
    color: white;
    background-color: #7939b8;
    font-size: 12px;
    padding: 0 5px;
    border-radius: 43%;
    font-family: monospace;
    height: 18px;
    line-height: 18px;
}

.hc-order-product-price-old {
    text-decoration: line-through;
    margin-left: 5px;
}

.dark .hc-order-added-product {
    background-color: #fff1;
    border-color: #fff1;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_PRODUCT } from "@/actions/project/product";

export default {
    props: {
        product: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "product-update");
            store.commit(
                SET_PRODUCT,
                this.products.find((p) => p.id == this.product.id)
            );
        },

        async removeOrderProduct() {
            this.$emit("remove", this.product);
        },
    },

    computed: {
        ...mapGetters(["products", "can"]),
    },
};
</script>
