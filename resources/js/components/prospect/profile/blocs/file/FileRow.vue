<template>
    <a
        class="hc-prospect-profile-file hc-flex-column"
        :href="file.url"
        target="_blank"
        style="width: 50%"
    >
        <div class="hc-prospect-profile-file-thumbnail hc-flex-column">
            <div class="hc-prospect-profile-file-thumbnail-content">
                <img :src="file.thumbnail" />
                <div
                    v-if="isPdf"
                    class="hc-prospect-profile-file-type-badge"
                >
                    <i class="fa fa-file-pdf"></i>
                </div>
                <div
                    class="hc-prospect-profile-file-remove"
                    @click.prevent.stop="removeFile"
                >
                    ×
                </div>
            </div>
        </div>
        <div class="hc-prospect-profile-file-name" v-text="file.name"></div>
        <div class="hc-prospect-profile-file-date" v-text="date"></div>
        <loading :loading="removingFile" />
    </a>
</template>

<style>
.hc-prospect-profile-file {
    padding: 3px;
    float: left;
    width: 33.333%;
    display: block;
    text-decoration: none;
    position: relative;
    border-radius: 10px;
    transition: all 200ms ease-out;
}

.hc-prospect-profile-file:hover {
    transform: scale(1.025);
    background-color: #eee;
}

.hc-prospect-profile-file-thumbnail {
    width: 100%;
    border-radius: 5px;
    overflow: hidden;
    position: relative;
    background-color: #eee;
}

.hc-prospect-profile-file-thumbnail-content {
    text-align: center;
    width: 100%;
    padding-top: 80%;
}

.hc-prospect-profile-file-thumbnail-content > img {
    bottom: 0;
    height: auto;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
}

.hc-prospect-profile-file-remove {
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

.hc-prospect-profile-file-type-badge {
    position: absolute;
    bottom: 5px;
    left: 5px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background-color: #ffffff;
    box-shadow: 0 1px 4px #0003;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    color: #d63b3b;
}

.hc-prospect-profile-file:hover .hc-prospect-profile-file-remove {
    opacity: 1;
    transform: scale(1);
}

.hc-prospect-profile-file-name,
.hc-prospect-profile-file-date {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.hc-prospect-profile-file-name {
    padding-top: 7px;
    color: #000000;
}

.hc-prospect-profile-file-date {
    font-size: 11px;
    color: #777;
}
</style>

<script>
import store from "@/store";

import { REMOVE_PROSPECT_FILE } from "@/actions/project/prospect/file";

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
    },

    computed: {
        /**
         *
         */
        date() {
            return dayjs(this.file.created_at).fromNow();
        },

        /**
         *
         */
        isPdf() {
            return this.file.extension === "pdf";
        },
    },
};
</script>
