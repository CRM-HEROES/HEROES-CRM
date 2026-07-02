<template>
    <div :class="['hc-document-page-new', selected ? 'selected' : '']">
        <div class="hc-document-page-new-container">
            <div class="hc-document-page-new-content">
                <img :src="thumbnail" />
            </div>
            <div class="hc-document-page-new-page" v-text="page"></div>
        </div>
    </div>
</template>

<style>
.hc-document-page-new {
    width: 100px;
    padding: 7px;
    display: flex;
    flex-direction: column;
    cursor: pointer;
    border-radius: 5px;
}

.hc-document-page-new:hover {
    background-color: #eeeeee;
}

.hc-document-page-new-container {
    width: 100%;
    outline: 1px solid #bbbbbb;
    border-radius: 2px;
    overflow: hidden;
    position: relative;
}

.hc-document-page-new.selected .hc-document-page-new-container {
    outline: 2px solid #7939b8;
}

.hc-document-page-new-content {
    text-align: center;
    width: 100%;
    padding-top: 142%;
}

.hc-document-page-new-content > img {
    bottom: 0;
    height: auto;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
}

.hc-document-page-new-page {
    position: absolute;
    bottom: 0;
    right: 0;
    padding: 0px 7px;
    font-weight: bold;
    font-size: 12px;
}

.hc-document-page-new-remove {
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

.hc-document-page-new:hover .hc-document-page-new-remove {
    opacity: 1;
    transform: scale(1);
}

.hc-document-page-new-remove:hover {
    background-color: #ff000033;
    color: #c05547;
    transform: scale(1);
}
</style>

<script>
import { API_URL } from "@/apis/common";
import { mapGetters } from "vuex";

export default {
    props: {
        /**
         *
         */
        file: {
            type: Object,
        },

        /**
         *
         */
        page: {
            type: Number,
        },

        /**
         *
         */
        selected: {
            type: Boolean,
            default: false,
        },
    },

    computed: {
        /**
         * Thumbnail URL
         */
        thumbnail() {
            return (
                API_URL +
                "/project/" +
                this.project.slug +
                "/document/" +
                this.file.document_id +
                "/file/" +
                this.file.id +
                "/page/" +
                this.page +
                "/thumbnail"
            );
        },

        // Store
        ...mapGetters(["project"]),
    },
};
</script>
