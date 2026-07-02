<template>
    <a class="hc-dashboard-document hc-flex-column" target="_blank">
        <div class="hc-dashboard-document-thumbnail hc-flex-column">
            <div class="hc-dashboard-document-thumbnail-content">
                <img v-if="document.thumbnail" :src="document.thumbnail" />
            </div>
            <a class="hc-dashboard-document-remove" @click.prevent.stop="remove"
                >&times;</a
            >
        </div>
        <div class="hc-dashboard-document-name" v-text="document.name"></div>
        <loading :loading="removingDocument" />
    </a>
</template>

<style>
.hc-dashboard-document {
    padding: 8px;
    float: left;
    width: 20%;
    display: block;
    text-decoration: none;
    position: relative;
    border-radius: 10px;
    transition: all 200ms ease-out;
    cursor: pointer;
    position: relative;
}

.hc-dashboard-document:hover {
    transform: scale(1.025);
    background-color: #ffffff88;
}

.hc-dashboard-document-thumbnail {
    width: 100%;
    border-radius: 5px;
    overflow: hidden;
    position: relative;
    background-color: #ffffff;
    outline: 1px solid #cccccc;
}

.hc-dashboard-document-thumbnail-content {
    text-align: center;
    width: 100%;
    padding-top: 141.285%;
}

.hc-dashboard-document-thumbnail-content > img {
    bottom: 0;
    height: auto;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
}

.hc-dashboard-document-name {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    padding-top: 7px;
    color: #000000;
}

.hc-dashboard-document-remove {
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

.hc-dashboard-document:hover .hc-dashboard-document-remove {
    opacity: 1;
    transform: scale(1);
}

.hc-dashboard-document-remove:hover {
    background-color: #ff000033;
    color: #c05547;
    transform: scale(1);
}
</style>

<script>
import store from "@/store";

import { REMOVE_DOCUMENT } from "@/actions/project/document";

export default {
    props: {
        /**
         *
         */
        document: {
            type: Object,
        },
    },

    data() {
        return {
            removingDocument: false,
        };
    },

    methods: {
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingDocument = true;

                try {
                    await store.dispatch(REMOVE_DOCUMENT, this.document.id);
                } finally {
                    this.removingDocument = false;
                }
            });
        },
    },
};
</script>
