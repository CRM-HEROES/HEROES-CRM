<template>
    <label class="hc-prospect-ocr-type hc-flex-column" v-tooltip="ocrType.name">
        <div class="hc-prospect-ocr-type-thumbnail hc-flex-column">
            <div class="hc-prospect-ocr-type-thumbnail-content">
                <img :src="ocrType.thumbnail" />
            </div>
        </div>
        <input type="file" @change="addFiles" multiple />
        <div class="hc-prospect-ocr-type-name" v-text="ocrType.name"></div>
    </label>
</template>

<style>
.hc-prospect-ocr-type {
    padding: 8px;
    float: left;
    width: 33.333%;
    display: block;
    text-decoration: none;
    position: relative;
    border-radius: 10px;
    transition: all 200ms ease-out;
}

.hc-prospect-ocr-type:hover {
    transform: scale(1.025);
    background-color: #eee;
}

.hc-prospect-ocr-type > input {
    display: none;
}

.hc-prospect-ocr-type-thumbnail {
    width: 100%;
    border-radius: 5px;
    overflow: hidden;
    position: relative;
    background-color: #eee;
    box-shadow: 0 2px 5px -2px #0007;
}

.hc-prospect-ocr-type-thumbnail-content {
    text-align: center;
    width: 100%;
    padding-top: 80%;
}

.hc-prospect-ocr-type-thumbnail-content > img {
    bottom: 0;
    height: auto;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
}

.hc-prospect-ocr-type-name {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    padding-top: 7px;
    color: #000000;
}
</style>

<script>
import store from "@/store";
import { mapGetters } from "vuex";
import ApiService from "@/apis/api.service";

export default {
    props: {
        /**
         *
         */
        ocrType: {
            type: Object,
        },
    },

    methods: {
        /**
         *
         */
        async addFiles(e) {
            this.$emit(
                "add-files",
                [...e.target.files].map((f) => ({
                    file: f,
                    ocrType: this.ocrType,
                }))
            );
        },
    },
};
</script>
