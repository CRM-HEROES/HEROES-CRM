<template>
    <a class="hc-prospect-file hc-flex-column" :href="file.url" target="_blank">
        <div class="hc-prospect-file-thumbnail hc-flex-column">
            <div class="hc-prospect-file-thumbnail-content">
                <img :src="file.thumbnail" />
                <div
                    class="hc-prospect-file-remove"
                    @click.prevent.stop="removeFile"
                >
                    ×
                </div>
            </div>
        </div>
        <div class="hc-prospect-file-name" v-text="file.name"></div>
        <div class="hc-prospect-file-date" v-text="date"></div>
        <div class="hc-prospect-file-size" v-text="size"></div>

        <div v-if="can('all.project.document.update')" @click.prevent.stop="edit">Gérer les accès</div>

        <loading :loading="removingFile" />
    </a>
</template>

<style>
.hc-prospect-file {
    padding: 8px;
    float: left;
    width: 33.333%;
    display: block;
    text-decoration: none;
    position: relative;
    border-radius: 10px;
    transition: all 200ms ease-out;
}

.hc-prospect-file:hover {
    transform: scale(1.025);
    background-color: #eee;
}

.hc-prospect-file-thumbnail {
    width: 100%;
    border-radius: 5px;
    overflow: hidden;
    position: relative;
    background-color: #eee;
    box-shadow: 0 2px 5px -2px #0007;
}

.hc-prospect-file-thumbnail-content {
    text-align: center;
    width: 100%;
    padding-top: 80%;
}

.hc-prospect-file-thumbnail-content > img {
    bottom: 0;
    height: auto;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
}

.hc-prospect-file-remove {
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

.hc-prospect-file-access {
    opacity: 0;
    transform: scale(0.8);
    position: absolute;
    top: 2px;
    left: 2px;
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
    background-color: #6E6E6E;
}

.hc-prospect-file:hover .hc-prospect-file-remove,
.hc-prospect-file:hover .hc-prospect-file-access {
    opacity: 1;
    transform: scale(1);
}

.hc-prospect-file-name,
.hc-prospect-file-date {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.hc-prospect-file-name {
    padding-top: 7px;
    color: #000000;
}

.hc-prospect-file-date {
    font-size: 11px;
    color: #777;
}

.hc-prospect-file-size {
    font-size: 11px;
    color: #777;
}
</style>

<script>
import store from "@/store";

import { OPEN_MODAL } from "@/actions/modal";
import { REMOVE_PROSPECT_FILE, SET_PROSPECT_FILE } from "@/actions/project/prospect/file";
import {SET_FOLDER} from "@/actions/project/folder";
import {mapGetters} from "vuex";

export default {
    props: {
        /**
         *
         */
        file: {
            type: Object,
        },
    },

    data() {
        return {
            removingFile: false,
        };
    },

    methods: {
        async removeFile() {
            this.removingFile = true;

            try {
                await store.dispatch(REMOVE_PROSPECT_FILE, this.file);
            } finally {
                this.removingFile = false;
            }
        },
        edit() {
            store.commit(OPEN_MODAL, "file-update");
            store.commit(SET_FOLDER, this.file.folder);
            store.commit(SET_PROSPECT_FILE, this.file);
        },
    },

    computed: {
        ...mapGetters(["can"]),

        /**
         *
         */
        date() {
            return dayjs(this.file.created_at).fromNow();
        },

        size() {
            const units = ["Bytes", "KB", "MB", "GB", "TB"];

            let size = this.file.size;
            let unitIndex = 0;

            while (size >= 1024 && unitIndex < units.length - 1) {
                size /= 1024;
                unitIndex++;
            }

            // Use a maximum of two decimal places
            const formattedSize = size.toFixed(2);

            return `${formattedSize} ${units[unitIndex]}`;
        },
    },
};
</script>
