<template>
    <div :class="['hc-document-page', selected ? 'selected' : '']">
        <div @click.stop="setAsCurrentPage" class="hc-document-page-container">
            <div :style="style" class="hc-document-page-content">
                <img v-if="file" :src="thumbnail" />
            </div>
            <div class="hc-document-page-page" v-text="page.page"></div>
            <a class="hc-document-page-remove" @click.prevent.stop="removePage"
                >&times;</a
            >
        </div>
    </div>
</template>

<style>
.hc-document-page {
    width: 100%;
    padding: 7px;
    cursor: pointer;
}

.hc-document-page-container {
    width: 100%;
    border-radius: 7px;
    box-shadow: 0px 2px 5px -2px #0006;
    overflow: hidden;
    position: relative;
}

.hc-document-page.selected .hc-document-page-container {
    outline: 2px solid #7939b8;
}

.hc-document-page-content {
    text-align: center;
    width: 100%;
}

.hc-document-page-content > img,
.hc-document-page-content > span {
    bottom: 0;
    height: auto;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
}

.hc-document-page-content > span {
    display: flex;
    font-size: 12px;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 10px;
    background-color: #fff3;
    text-shadow: 0 0 15px #fff;
}

.hc-document-page-page {
    position: absolute;
    bottom: 0;
    right: 0;
    padding: 0px 7px;
    font-weight: bold;
    font-size: 12px;
}

.hc-document-page-remove {
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

.hc-document-page:hover .hc-document-page-remove {
    opacity: 1;
    transform: scale(1);
}
</style>

<script>
import { API_URL } from "@/apis/common";
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    SHOW_DOCUMENT_PAGE,
    REMOVE_DOCUMENT_PAGE,
    FETCH_DOCUMENT_PAGES,
} from "@/actions/project/document";

export default {
    props: {
        /**
         *
         */
        page: {
            type: Object,
        },
    },

    methods: {
        /**
         * Set this as current document page
         */
        setAsCurrentPage() {
            store.commit(SHOW_DOCUMENT_PAGE, this.page);
        },

        /**
         * Remove document page
         */
        async removePage() {
            try {
                await store.dispatch(REMOVE_DOCUMENT_PAGE, this.page.id);
                store.dispatch(FETCH_DOCUMENT_PAGES);
            } finally {
            }
        },
    },

    computed: {
        /**
         * Check if current page
         */
        selected() {
            return this.documentPage && this.documentPage.id == this.page.id;
        },

        /**
         * Thumbnail style
         */
        style() {
            // Width: fill container
            // Height: proportional to width
            return {
                "padding-top":
                    (parseFloat(this.page.height) * 100) /
                        parseFloat(this.page.width) +
                    "%",
            };
        },

        /**
         * Current page pdf
         */
        file() {
            // Only page associated to file
            if (!this.page.file_id) {
                return null;
            }

            return this.documentFiles.find((df) => df.id == this.page.file_id);
        },

        /**
         * Thumbnail URL
         */
        thumbnail() {
            return (
                API_URL +
                "/project/" +
                this.project.slug +
                "/document/" +
                this.page.document_id +
                "/page/" +
                this.page.id +
                "/thumbnail"
            );
        },

        // Store
        ...mapGetters(["project", "documentPage", "documentFiles"]),
    },
};
</script>
