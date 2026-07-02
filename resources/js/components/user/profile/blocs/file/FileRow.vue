<template>
    <a
        class="hc-user-profile-file hc-flex-column"
        :href="file.url"
        style="width: 50%"
    >
        <div class="hc-user-profile-file-thumbnail hc-flex-column">
            <div class="hc-user-profile-file-thumbnail-content">
                <img :src="file.thumbnail" />
                <div
                    class="hc-user-profile-file-remove"
                    @click.prevent.stop="removeFile"
                >
                    ×
                </div>
            </div>
        </div>
        <div class="hc-user-profile-file-name" v-text="file.name"></div>
        <div class="hc-user-profile-file-date" v-text="date"></div>
        <loading :loading="removingFile" />
    </a>
</template>

<style>
.hc-user-profile-file {
    padding: 3px;
    float: left;
    width: 33.333%;
    display: block;
    text-decoration: none;
    position: relative;
    border-radius: 10px;
    transition: all 200ms ease-out;
}

.hc-user-profile-file:hover {
    transform: scale(1.025);
    background-color: #eee;
}

.hc-user-profile-file-thumbnail {
    width: 100%;
    border-radius: 5px;
    overflow: hidden;
    position: relative;
    background-color: #eee;
}

.hc-user-profile-file-thumbnail-content {
    text-align: center;
    width: 100%;
    padding-top: 80%;
}

.hc-user-profile-file-thumbnail-content > img {
    bottom: 0;
    height: auto;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
}

.hc-user-profile-file-remove {
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

.hc-user-profile-file:hover .hc-user-profile-file-remove {
    opacity: 1;
    transform: scale(1);
}

.hc-user-profile-file-name,
.hc-user-profile-file-date {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.hc-user-profile-file-name {
    padding-top: 7px;
    color: #000000;
}

.hc-user-profile-file-date {
    font-size: 11px;
    color: #777;
}
</style>

<script>
import store from "@/store";

import { REMOVE_USER_FILE } from "@/actions/project/user/file";

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
                await store.dispatch(REMOVE_USER_FILE, this.file);
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
    },
};
</script>
