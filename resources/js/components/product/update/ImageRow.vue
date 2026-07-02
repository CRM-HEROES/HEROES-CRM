<template>
    <a class="hc-product-image hc-flex-column" :href="image.url">
        <div class="hc-product-image-thumbnail hc-flex-column">
            <div class="hc-product-image-thumbnail-content">
                <img :src="image.thumbnail" />
                <div
                    class="hc-product-image-remove"
                    @click.prevent.stop="removeImage"
                >
                    ×
                </div>
            </div>
        </div>
        <loading :loading="removingImage" />
    </a>
</template>

<style>
.hc-product-image {
    padding: 8px;
    float: left;
    min-width: 50%;
    flex: 1;
    display: block;
    text-decoration: none;
    position: relative;
    border-radius: 10px;
    transition: all 200ms ease-out;
}

.hc-product-image:hover {
    transform: scale(1.025);
    background-color: #eee;
}

.hc-product-image-thumbnail {
    width: 100%;
    border-radius: 5px;
    overflow: hidden;
    position: relative;
    background-color: #eee;
    box-shadow: 0 2px 5px -2px #0007;
}

.hc-product-image-thumbnail-content {
    text-align: center;
    width: 100%;
    padding-top: 80%;
}

.hc-product-image-thumbnail-content > img {
    bottom: 0;
    height: auto;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
}

.hc-product-image-remove {
    opacity: 0;
    transform: scale(0.8);
    position: absolute;
    top: 2px;
    right: 2px;
    width: 20px;
    height: 20px;
    transition: all 100ms ease-out;
    color: #ffffff;
    font-size: 9px;
    font-weight: bold;
    text-align: center;
    line-height: 20px;
    text-decoration: none;
    border-radius: 50%;
    cursor: pointer;
    background-color: #7939b8;
}

.hc-product-image:hover .hc-product-image-remove {
    opacity: 1;
    transform: scale(1);
}

.hc-product-image-name,
.hc-product-image-date {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.hc-product-image-name {
    padding-top: 7px;
    color: #000000;
}

.hc-product-image-date {
    font-size: 11px;
    color: #777;
}
</style>

<script>
import store from "@/store";

import { REMOVE_PRODUCT_IMAGE } from "@/actions/project/product";

export default {
    props: {
        /**
         *
         */
        product: {
            type: Object,
        },

        /**
         *
         */
        image: {
            type: Object,
        },
    },

    data() {
        return {
            removingImage: false,
        };
    },

    methods: {
        async removeImage() {
            this.removingImage = true;

            try {
                await store.dispatch(REMOVE_PRODUCT_IMAGE, {
                    product: this.product,
                    image: this.image,
                });
            } finally {
                this.removingImage = false;
            }
        },
    },
};
</script>
