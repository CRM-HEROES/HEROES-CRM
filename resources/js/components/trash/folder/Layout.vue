<template>
    <div class="hc-flex-column" style="height: 100%">
        <div class="hc-flex-column hc-flex-1" style="overflow: hidden">
            <search v-model="keyword" />
            <item-list class="hc-flex-1" padding="5px" style="overflow: auto">
                <item tag="label">
                    <icon class="fa fa-check-square" />
                    <div class="hc-item-main-content" v-text="$t('all')"></div>

                    <checkbox v-model="all" />
                </item>

                <folder-row
                    v-for="folder in folders"
                    :key="folder.id"
                    :folder="folder"
                    v-model="selectedFolders"
                />

                <file-row
                    v-for="file in files"
                    :key="file.id"
                    :file="file"
                    v-model="selectedFiles"
                />
                <loading :loading="removing" />
            </item-list>
            <buttons>
                <button
                    @click.prevent="restore"
                    v-text="$t('restore')"
                ></button>
                <button
                    @click.prevent="remove"
                    class="hc-button-danger"
                    v-text="$t('delete')"
                ></button>
            </buttons>
        </div>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import TrashService from "@/apis/project/trash";
import FolderRow from "./FolderRow.vue";
import FileRow from "./FileRow.vue";

export default {
    components: {
        FolderRow,
        FileRow,
    },

    data() {
        return {
            keyword: "",
            folders: [],
            files: [],
            selectedFolders: [],
            selectedFiles: [],
            removing: false,
        };
    },

    mounted() {
        this.fetch();
    },

    methods: {
        async remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                try {
                    this.removing = true;

                    await TrashService.bulkDestroy(this.project.slug, {
                        folders: this.selectedFolders,
                        files: this.selectedFiles,
                    });
                    this.fetch();
                } finally {
                    this.removing = false;
                }
            });
        },

        async restore() {
            try {
                await TrashService.bulkRestore(this.project.slug, {
                    folders: this.selectedFolders,
                    files: this.selectedFiles,
                });
                this.fetch();
            } finally {
            }
        },

        /**
         * Fetch trashed folders and files
         */
        async fetch() {
            this.fetchFolders();
            this.fetchFiles();
        },

        /**
         * Fetch trashed folders
         */
        async fetchFolders() {
            const params = {};

            if (this.keyword) {
                params.query = this.keyword;
            }

            const { data } = await TrashService.folder(this.project.slug, {
                params,
            });

            this.folders = data.data;
        },

        /**
         * Fetch trashed files
         */
        async fetchFiles() {
            const params = {};

            if (this.keyword) {
                params.query = this.keyword;
            }

            const { data } = await TrashService.file(this.project.slug, {
                params,
            });

            this.files = data.data;
        },
    },

    watch: {
        keyword() {
            this.fetch();
        },
    },

    computed: {
        ...mapGetters(["project"]),

        all: {
            get() {
                return (
                    this.selectedFolders.length == this.folders.length &&
                    this.selectedFiles.length == this.files.length
                );
            },
            set(value) {
                this.selectedFolders = value
                    ? this.folders.map((folder) => folder.id)
                    : [];
                this.selectedFiles = value
                    ? this.files.map((file) => file.id)
                    : [];
            },
        },
    },
};
</script>
