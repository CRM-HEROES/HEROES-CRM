<template>
    <item class="hc-order-product" padding="7">
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
            <div
                class="hc-order-product-price"
                v-text="product.price_value"
            ></div>
        </div>
        <icon
            v-if="can('all.project.order.product.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
        <icon tag="a" class="fa fa-plus" />
    </item>
</template>

<style>
.hc-order-product {
    padding: 5px 0 5px 4px !important;
}

.hc-order-product-image-thumbnail {
    width: 60px;
    border-radius: 5px;
    overflow: hidden;
    position: relative;
    background-color: #eee;
    box-shadow: 0 2px 3px -2px #0009;
}

.hc-order-product-image-thumbnail-content {
    text-align: center;
    width: 100%;
    padding-top: 70%;
}

.hc-order-product-image-thumbnail-content > img {
    bottom: 0;
    height: auto;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
}

.hc-order-product-info {
    padding-left: 10px;
    margin-right: 10px;
}

.hc-order-product-name {
    color: #000;
}

.hc-order-product-price {
    color: #777;
    font-size: 11px;
}

.dark .hc-order-product-name {
    color: #cccccc;
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
            store.commit(SET_PRODUCT, this.product);
        },
    },

    computed: {
        ...mapGetters(["can"]),
    },
};
</script>
