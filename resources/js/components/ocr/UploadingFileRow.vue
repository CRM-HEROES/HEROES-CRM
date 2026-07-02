<template>
    <div class="hc-prospect-ocr-file hc-flex-column">
        <div class="hc-prospect-ocr-file-thumbnail hc-flex-column">
            <div class="hc-prospect-ocr-file-thumbnail-content">
                <img :src="thumbnail" />
                <div class="hc-prospect-ocr-file-progress">
                    <div
                        :class="[
                            'hc-prospect-ocr-file-progress-bar',
                            uploaded ? 'uploaded' : error ? 'error' : '',
                        ]"
                        :style="{ width: uploadProgress * 100 + '%' }"
                    ></div>
                </div>
            </div>
            <div
                v-if="ocr"
                @click.prevent.stop="showProspect"
                class="hc-prospect-ocr-file-prospect fa fa-eye"
                v-tooltip="$t('prospect.scan.view_prospect')"
            >
                <loading :loading="showingProspect" />
            </div>
        </div>
        <div class="hc-prospect-ocr-file-name" v-text="file.name"></div>
        <div class="hc-prospect-ocr-file-date" v-text="date"></div>
    </div>
</template>

<style>
.hc-prospect-ocr-file {
    padding: 8px;
    float: left;
    width: 33.333%;
    display: block;
    text-decoration: none;
    position: relative;
    border-radius: 10px;
    transition: all 200ms ease-out;
}

.hc-prospect-ocr-file:hover {
    transform: scale(1.025);
    background-color: #eee;
}

.hc-prospect-ocr-file-thumbnail {
    width: 100%;
    border-radius: 5px;
    overflow: hidden;
    position: relative;
    background-color: #eee;
    box-shadow: 0 2px 5px -2px #0007;
}

.hc-prospect-ocr-file-thumbnail-content {
    text-align: center;
    width: 100%;
    padding-top: 80%;
}

.hc-prospect-ocr-file-thumbnail-content > img {
    bottom: 0;
    height: auto;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
}

.hc-prospect-ocr-file-name,
.hc-prospect-ocr-file-date {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.hc-prospect-ocr-file-name {
    padding-top: 7px;
    color: #000000;
}

.hc-prospect-ocr-file-date {
    font-size: 11px;
    color: #777;
}

.hc-prospect-ocr-file-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background-color: #eee;
    z-index: 1;
}

.hc-prospect-ocr-file-progress-bar {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 100%;
    background-color: #1e88e5;
    transition: width 100ms ease-out;
}

.hc-prospect-ocr-file-progress-bar.uploaded {
    background-color: #05a720;
}

.hc-prospect-ocr-file-progress-bar.error {
    background-color: #a71805;
}

.hc-prospect-ocr-file-prospect {
    position: absolute;
    bottom: 10px;
    right: 10px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    color: #1e6ee5;
    background-color: #ffffff;
    border: 1px solid #eeeeee;
    font-size: 17px;
    text-align: center;
    line-height: 40px;
    cursor: pointer;
}

.hc-prospect-ocr-file-prospect:hover {
    color: #ffffff;
    background-color: #1e6ee5;
}
</style>

<script>
import store from "@/store";
import { mapGetters } from "vuex";
import ApiService from "@/apis/api.service";

import { SET_PROSPECT } from "@/actions/project/prospect";

export default {
    props: {
        /**
         *
         */
        file: {},
        /**
         *
         */
        ocrType: {
            type: Object,
        },
    },

    data() {
        return {
            thumbnail: null,
            uploadProgress: 0,
            uploaded: false,
            error: false,
            prospect: null,
            showingProspect: false,
        };
    },

    created() {
        this.loadThumbnail();
        this.uploadFile();
    },

    methods: {
        /**
         * Load thumbnail
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
            // params.append("type", "carte_de_visite");
            params.append("type", this.ocrType.type);
            params.append("api", this.ocrType.api);

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
                // Send the FormData in a POST request using Axios
                const { data } = await ApiService.post(
                    `project/${this.project.slug}/ocr`,
                    params,
                    settings
                );
                this.ocr = data;
                this.uploaded = true;
            } catch (e) {
                this.error = true;
            }

            this.$emit("file-uploaded", this.file);
        },

        /**
         * Go to the prospect profile
         */
        async showProspect() {
            this.showingProspect = true;

            try {
                const { data } = await ApiService.get(
                    `project/${this.project.slug}/ocr/${this.ocr.id}`
                );
                this.ocr = data;

                if (!this.ocr.prospect) {
                    flashWarning({
                        title: "OCR",
                        body: "La récupération d'information du prospect est en cours de traitement!",
                    });
                    return;
                }

                store.commit(SET_PROSPECT, this.ocr.prospect);

                const routeData = this.$router.resolve({
                    name: "prospect.show",
                    params: {
                        project: this.project.slug,
                        prospect: this.ocr.prospect.id,
                    },
                });
                window.open(routeData.href, "_blank");
            } finally {
                this.showingProspect = false;
            }
        },
    },

    computed: {
        ...mapGetters(["project"]),

        /**
         *
         */
        date() {
            return this.uploaded
                ? "Terminé"
                : this.error
                ? "Erreur de téléchargement"
                : "En cours de téléchargement ...";
        },

        isImage() {
            return this.file.type.split("/")[0] === "image";
        },
    },
};
</script>
