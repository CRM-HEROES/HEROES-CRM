<template>
    <div class="hc-product-image hc-flex-column">
        <div class="hc-product-image-thumbnail hc-flex-column">
            <div class="hc-product-image-thumbnail-content">
                <img :src="thumbnail" />
                <div class="hc-product-image-progress">
                    <div
                        class="hc-product-image-progress-bar"
                        :style="{ width: uploadProgress * 100 + '%' }"
                    ></div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.hc-product-image-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background-color: #eee;
    z-index: 1;
}

.hc-product-image-progress-bar {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 100%;
    background-color: #1e88e5;
    transition: width 100ms ease-out;
}
</style>

<script>
import store from "@/store";

import { ADD_PRODUCT_IMAGE } from "@/actions/project/product";

export default {
    props: {
        /**
         *
         */
        image: {},
        product: {
            type: Object,
        },
    },

    data() {
        return {
            thumbnail: null,
            uploadProgress: 0,
        };
    },

    created() {
        this.loadThumbnail();
        this.uploadImage();
    },

    methods: {
        /**
         * Remove message
         */
        loadThumbnail() {
            var reader = new FileReader();

            reader.onload = (e) => {
                this.thumbnail = e.target.result;
            };

            reader.readAsDataURL(this.image);
        },

        /**
         * Upload image
         */
        async uploadImage() {
            const params = new FormData();
            params.append("image", this.image);

            // upload setting
            var settings = {
                // header multipart form data
                headers: {
                    "content-type": "multipart/form-data",
                },
                // upload progress function
                onUploadProgress: (progressEvent) => {
                    this.uploadProgress =
                        progressEvent.loaded / progressEvent.total;
                },
            };

            try {
                await store.dispatch(ADD_PRODUCT_IMAGE, {
                    product: this.product,
                    params,
                    settings,
                });

                this.$emit("image-uploaded", this.image);
            } finally {
            }
        },
    },
};
</script>
