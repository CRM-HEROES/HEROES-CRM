<template>
    <div :class="['hc-document-file', selected ? 'selected' : '']">
        <div class="hc-document-file-container">
            <div class="hc-document-file-content">
                <img :src="thumbnail" />
            </div>
            <label class="hc-document-file-edit">
                <i class="fa fa-edit"></i>
                <input type="file" accept="application/pdf" @change="update" />
            </label>
            <a class="hc-document-file-remove" @click.prevent="remove"
                >&times;</a
            >
        </div>
        <loading :loading="removingFile" />
    </div>
</template>

<style>
.hc-document-file {
    width: 100%;
    float: left;
    padding: 10px;
    display: flex;
    flex-direction: column;
    cursor: pointer;
    border-radius: 10px;
    position: relative;
}

.hc-document-file:hover {
    background-color: #eeeeee;
}

.hc-document-file-container {
    width: 100%;
    border-radius: 10px;
    box-shadow: 0px 2px 5px -2px #0006;
    overflow: hidden;
    position: relative;
}

.hc-document-file.selected .hc-document-file-container {
    outline: 2px solid #7939b8;
}

.hc-document-file-content {
    text-align: center;
    width: 100%;
    padding-top: 142%;
}

.hc-document-file-content > img {
    bottom: 0;
    height: auto;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
}

.hc-document-file-edit,
.hc-document-file-remove {
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

.hc-document-file-edit {
    background-color: #0684cc;
    right: 24px;
}

.hc-document-file-edit > input {
    display: none;
}

.hc-document-file:hover .hc-document-file-edit,
.hc-document-file:hover .hc-document-file-remove {
    opacity: 1;
    transform: scale(1);
}

.hc-document-file-edit:hover,
.hc-document-file-remove:hover {
    transform: scale(1);
}

.hc-document-file-remove:hover {
    background-color: #ff000033;
    color: #c05547;
}
</style>

<script>
import { API_URL } from "@/apis/common";
import { mapGetters } from "vuex";
import store from "@/store";

import {
    FETCH_DOCUMENT_FILES,
    UPDATE_DOCUMENT_FILE,
    REMOVE_DOCUMENT_FILE,
} from "@/actions/project/document";

export default {
    props: {
        file: {
            type: Object,
        },
    },

    data() {
        return {
            updatingFile: false,
            removingFile: false,
            thumbnailVersion: 0,
        };
    },

    methods: {
        /**
         * Update associated document file
         */
        update(event) {
            const files = event.target.files;
            const file = files[0];

            if (!file.type.match("pdf")) {
                return;
            }

            var formData = new FormData();
            formData.append("file", file);

            this.updatingFile = true;
            try {
                // dispatch document pdf
                store.dispatch(UPDATE_DOCUMENT_FILE, {
                    slug: this.file.id,
                    params: formData,
                });
                store.commit(UPDATE_DOCUMENT_FILE, {
                    slug: this.file.id,
                    params: {
                        updated_at: new Date().toISOString(),
                    },
                });
            } finally {
                this.updatingFile = false;
            }
        },

        /**
         * Remove associated document file
         */
        remove() {
            hcConfirm(
                "En supprimant ce fichier,<br>- vous allez supprimer toutes les pages liées à ce fichier,<br>- ainsi que les champs liés aux pages de ce fichier.<br><br>Etes-vous sûr d'effectuer cette suppression ?",
                async () => {
                    this.removingFile = true;

                    try {
                        await store.dispatch(
                            REMOVE_DOCUMENT_FILE,
                            this.file.id
                        );
                        store.dispatch(FETCH_DOCUMENT_FILES);
                    } finally {
                        this.removingFile = false;
                    }
                }
            );
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
                "/page/1/thumbnail?v=" +
                this.thumbnailVersion
            );
        },

        // Store
        ...mapGetters(["project"]),
    },
};
</script>
