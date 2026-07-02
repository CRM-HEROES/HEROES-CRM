<template>
    <slide
        :name="name"
        @open="fetchFolders"
        :title="$t('user.files.title', { user: userFullName })"
        :url="
            user
                ? {
                      name: 'user.show',
                      params: {
                          project: project.slug,
                          user: user.id,
                      },
                  }
                : null
        "
        icon="fa fa-folder"
        style="width: 400px"
    >
        <tab-layout :count="2" :tab="tab" class="hc-flex-1">
            <!-- List of folders -->
            <template #1>
                <div class="hc-flex-column" style="height: 100%">
                    <search v-model="folderKeyword" />
                    <item-list class="hc-flex-1" padding="5px">
                        <folder-row
                            v-for="c in filteredFolders"
                            :key="c.id"
                            :folder="c"
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
                    <template #1 v-if="user">
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
                </frame-layout>
            </template>
        </tab-layout>
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { SET_USER } from "@/actions/project/user";
import {
    FETCH_USER_FILES,
    SET_USER_FILE_FOLDER,
} from "@/actions/project/user/file";
import { FETCH_FOLDERS } from "@/actions/project/folder";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import UploadingFileRow from "./UploadingFileRow.vue";
import FileRow from "./FileRow.vue";
import FolderRow from "./FolderRow.vue";

export default {
    components: {
        FileRow,
        UploadingFileRow,
        FolderRow,
    },

    data() {
        return {
            name: "user-manage-files",
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
            store.commit(SET_USER_FILE_FOLDER, folder);
        },

        /**
         *
         */
        fetchFolders() {
            store.dispatch(FETCH_FOLDERS);

            if (!this.user) {
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
        setFileUser(user) {
            store.commit(SET_USER, user);

            if (this.fileFolder) {
                this.tab = 1;
                this.frameTab = 0;
            } else {
                this.tab = 0;
            }
        },

        async fetchUserFiles() {
            this.fetchingFiles = true;

            try {
                await store.dispatch(FETCH_USER_FILES, this.fileFolder.id);
            } finally {
                this.fetchingFiles = false;
            }
        },
    },

    watch: {
        async fileFolder(newValue) {
            if (
                newValue &&
                newValue.for == "user" &&
                this.slideOpen(this.name)
            ) {
                if (this.user) {
                    this.tab = 1;
                    this.fetchUserFiles();
                }
            }
        },

        async user(newValue) {
            if (newValue && this.slideOpen(this.name)) {
                // this.setFolder(null);
                if (this.fileFolder) {
                    this.fetchUserFiles();
                }
            }
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "slideOpen",
            "folders",
            "user",
            "userFullName",
            "userFiles",
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
                    folder.for == "user" &&
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

            return this.userFiles.filter(
                (file) =>
                    file.folder_id == this.fileFolder.id &&
                    removeStringAccent(file.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
