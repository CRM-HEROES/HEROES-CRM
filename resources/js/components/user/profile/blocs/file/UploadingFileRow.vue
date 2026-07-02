<template>
    <div class="hc-user-profile-file hc-flex-column" style="width: 50%">
        <div class="hc-user-profile-file-thumbnail hc-flex-column">
            <div class="hc-user-profile-file-thumbnail-content">
                <img :src="thumbnail" />
                <div class="hc-user-profile-file-progress">
                    <div
                        class="hc-user-profile-file-progress-bar"
                        :style="{ width: uploadProgress * 100 + '%' }"
                    ></div>
                </div>
            </div>
        </div>
        <div class="hc-user-profile-file-name" v-text="file.name"></div>
        <div class="hc-user-profile-file-date" v-text="date"></div>
    </div>
</template>

<style>
.hc-user-profile-file-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background-color: #eee;
    z-index: 1;
}

.hc-user-profile-file-progress-bar {
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

import { ADD_USER_FILE } from "@/actions/project/user/file";

export default {
    props: {
        /**
         *
         */
        file: {},
        folder: {
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
        this.uploadFile();
    },

    methods: {
        /**
         * Remove message
         */
        loadThumbnail() {
            if (!this.isImage) {
                return;
            }

            var reader = new FileReader();

            reader.onload = (e) => {
                this.thumbnail = e.target.result;
            };

            reader.readAsDataURL(this.file);
        },

        /**
         * Upload file
         */
        async uploadFile() {
            const params = new FormData();
            params.append("file", this.file);

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

            let uploadedFile = null;

            try {
                const data = await store.dispatch(ADD_USER_FILE, {
                    folder: this.folder.id,
                    params,
                    settings,
                });

                uploadedFile = data;
                this.$emit("file-uploaded", this.file, uploadedFile);
            } finally {
            }
        },
    },

    computed: {
        /**
         *
         */
        date() {
            return "En cours de téléchargement ...";
        },

        isImage() {
            return this.file.type.split("/")[0] === "image";
        },
    },
};
</script>
