<template>
    <a class="hc-dashboard-document-template hc-flex-column" target="_blank">
        <div class="hc-dashboard-document-template-thumbnail hc-flex-column">
            <div class="hc-dashboard-document-template-thumbnail-content">
                <img :src="documentTemplate.thumbnail" />
            </div>
        </div>
        <div
            class="hc-dashboard-document-template-name"
            v-text="documentTemplate.name"
        ></div>
        <div
            class="hc-dashboard-document-template-description"
            v-text="documentTemplate.description"
        ></div>
    </a>
</template>

<style>
.hc-dashboard-document-template {
    padding: 8px;
    float: left;
    width: 20%;
    display: block;
    text-decoration: none;
    position: relative;
    border-radius: 10px;
    transition: all 200ms ease-out;
    cursor: pointer;
}

.hc-dashboard-document-template:hover {
    transform: scale(1.025);
    background-color: #ffffff88;
}

.hc-dashboard-document-template-thumbnail {
    width: 100%;
    border-radius: 5px;
    overflow: hidden;
    position: relative;
    background-color: #ffffff;
    outline: 1px solid #cccccc;
}

.hc-dashboard-document-template-thumbnail-content {
    text-align: center;
    width: 100%;
    padding-top: 141.285%;
}

.hc-dashboard-document-template-thumbnail-content > img {
    bottom: 0;
    height: auto;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
}

.hc-dashboard-document-template-name {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    padding-top: 7px;
    font-weight: bold;
    color: #000000;
}

.hc-dashboard-document-template-description {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    color: #888888;
    font-size: 12px;
}
</style>

<script>
import { API_URL } from "@/apis/common";

export default {
    props: {
        /**
         *
         */
        documentTemplate: {
            type: Object,
        },
    },

    computed: {
        thumbnail() {
            return (
                "/images/document/templates/" +
                this.documentTemplate.key +
                ".jpg"
            );
        },
    },
};
</script>
