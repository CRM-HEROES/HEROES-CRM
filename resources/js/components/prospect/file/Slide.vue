<template>
    <slide
        :name="name"
        @open="fetchFolders"
        :title="$t('prospect.file.title', { prospect: prospectFullName })"
        :url="
            prospect
                ? {
                      name: 'prospect.show',
                      params: {
                          project: project.slug,
                          prospect: prospect.id,
                      },
                  }
                : null
        "
        icon="fa fa-folder"
        style="width: 400px"
    >
        <tab-layout :count="3" :tab="tab" class="hc-flex-1">
            <!-- List of folders -->
            <template #1>
                <div class="hc-flex-column" style="height: 100%">
                    <search v-model="folderKeyword" />
                    <item-list class="hc-flex-1" padding="5px">
                        <folder-row
                            v-for="c in filteredFolders"
                            :key="c.id"
                            :folder="c"
                            :count="folderFilesCount(c)"
                            @click="setFolder(c), ((tab = 1), (frameTab = 0))"
                        />
                    </item-list>
                    <buttons v-if="can('all.project.folder.add')">
                        <a @click.prevent="addFolder" v-text="$t('add')"></a>
                    </buttons>
                </div>
            </template>

            <!-- List of files -->
            <template #2>
                <frame-layout :count="3" :tab="frameTab" class="hc-flex-1">
                    <template #1 v-if="prospect">
                        <div
                            class="hc-flex-column"
                            style="height: 100%"
                            v-if="fileFolder"
                        >
                            <item
                                @click="setFolder(null), (tab = 0)"
                                class="bordered"
                            >
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="fileFolder.name"
                                ></div>
                                <div
                                    style="padding: 0 5px; color: #777"
                                    v-text="
                                        fileFolder.private
                                            ? 'Interne'
                                            : 'Partagé'
                                    "
                                ></div>
                            </item>
                            <div
                                style="
                                    overflow: auto;
                                    padding: 8px;
                                    width: 100%;
                                    height: 100%;
                                    position: relative;
                                "
                                @dragenter.prevent
                                @dragover.prevent
                                @dragleave.prevent
                                @drop.prevent="dropFiles"
                            >
                                <file-row
                                    v-for="c in filteredFiles"
                                    :key="c.id"
                                    :file="c"
                                />

                                <uploading-file-row
                                    v-for="c in uploadingFiles"
                                    :key="c.name"
                                    :folder="fileFolder"
                                    :file="c"
                                    @file-uploaded="fileUploaded"
                                />

                                <loading :loading="fetchingFiles" />
                            </div>
                            <buttons>
                                <label
                                    >{{ $t("add") }}
                                    <input
                                        type="file"
                                        @change="addFiles"
                                        multiple
                                /></label>
                            </buttons>
                        </div>
                    </template>

                    <template #3>
                        <select-prospect
                            @back="tab = 0"
                            @prospect-selected="setFileProspect"
                        />
                    </template>
                </frame-layout>
            </template>
        </tab-layout>
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { SET_PROSPECT } from "@/actions/project/prospect";
import {
    FETCH_PROSPECT_FILES,
    SET_PROSPECT_FILE_FOLDER,
    FETCH_PROSPECT_FOLDERS,
} from "@/actions/project/prospect/file";
import { FETCH_FOLDERS } from "@/actions/project/folder";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import UploadingFileRow from "./UploadingFileRow.vue";
import FileRow from "./FileRow.vue";
import FolderRow from "./FolderRow.vue";
import SelectProspect from "../select/Select.vue";

export default {
    components: {
        FileRow,
        UploadingFileRow,
        FolderRow,
        SelectProspect,
    },

    data() {
        return {
            name: "prospect-manage-files",
            tab: 0,
            folderKeyword: "",
            fileKeyword: "",

            fetchingFiles: false,
            uploadingFiles: [],
        };
    },

    methods: {
        /**
         *
         */
        addFolder() {
            store.commit(OPEN_MODAL, "folder-add");
        },

        /**
         *
         * @param {*} category
         */
        setFolder(folder) {
            store.commit(SET_PROSPECT_FILE_FOLDER, folder);
        },

        /**
         *
         */
        fetchFolders() {
            store.dispatch(FETCH_FOLDERS);

            if (!this.prospect) {
                this.tab = 1;
                this.frameTab = 2;
            }
        },

        /**
         *
         */
        async addFiles(e) {
            this.uploadingFiles = [...this.uploadingFiles, ...e.target.files];
        },

        /**
         *
         */
        async dropFiles(e) {
            this.uploadingFiles = [
                ...this.uploadingFiles,
                ...e.dataTransfer.files,
            ];
        },

        /**
         *
         * @param {*} uploading
         * @param {*} uploaded
         */
        fileUploaded(uploading) {
            this.uploadingFiles = this.uploadingFiles.filter(
                (f) => f != uploading
            );
        },

        /**
         *
         */
        setFileProspect(prospect) {
            store.commit(SET_PROSPECT, prospect);

            if (this.fileFolder) {
                this.tab = 1;
                this.frameTab = 0;
            } else {
                this.tab = 0;
            }
        },

        async fetchProspectFiles() {
            this.fetchingFiles = true;

            try {
                await store.dispatch(FETCH_PROSPECT_FILES, this.fileFolder.id);
            } finally {
                this.fetchingFiles = false;
            }
        },

        async fetchProspectFolders() {
            try {
                await store.dispatch(FETCH_PROSPECT_FOLDERS);

                let folder = this.prospectFolders.find((t) => t.total > 0);

                if (folder) {
                    folder = this.folders.find((t) => t.id == folder.folder_id);

                    if (folder) {
                        this.setFolder(folder);
                    }
                }
            } finally {
            }
        },

        /**
         *
         * @param {*} thread
         */
        folderFilesCount(folder) {
            const t = this.prospectFolders.find(
                (t) => t.folder_id == folder.id
            );
            return t ? t.total : 0;
        },
    },

    watch: {
        async fileFolder(newValue) {
            if (
                newValue &&
                newValue.for == "prospect" &&
                this.slideOpen(this.name)
            ) {
                if (this.prospect) {
                    this.tab = 1;
                    this.fetchProspectFiles();
                }
            }
        },

        async prospect(newValue) {
            if (newValue && this.slideOpen(this.name)) {
                this.fetchProspectFolders();

                // this.setFolder(null);
                if (this.fileFolder) {
                    this.fetchProspectFiles();
                }
            }
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "slideOpen",
            "folders",
            "prospect",
            "prospectFullName",
            "prospectFiles",
            "prospectFolders",
            "fileFolder",
            "can",
        ]),

        /**
         *
         */
        filteredFolders() {
            const keyword = removeStringAccent(this.folderKeyword);

            return this.folders.filter(
                (folder) =>
                    folder.for == "prospect" &&
                    removeStringAccent(folder.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredFiles() {
            if (!this.fileFolder) {
                return [];
            }

            const keyword = removeStringAccent(this.fileKeyword);

            return this.prospectFiles.filter(
                (file) =>
                    file.folder_id == this.fileFolder.id &&
                    removeStringAccent(file.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
