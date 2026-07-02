<template>
    <bloc icon="fa fa-folder icon-blue" :name="folder.name">
        <template #options>
            <icon tag="label" class="fa fa-plus" @click.stop>
                <input type="file" @change="addFiles" multiple />
            </icon>
        </template>
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
                <file-row
                    v-for="(file, i) in files"
                    :key="file.id"
                    :file="file"
                />

                <uploading-file-row
                    v-for="c in uploadingFiles"
                    :key="c.name"
                    :folder="folder"
                    :file="c"
                    @file-uploaded="fileUploaded"
                />
            </div>

            <buttons v-if="files.length > shownFilesCount">
                <button
                    @click.prevent="showMoreFiles"
                    v-text="$t('show_more')"
                ></button>
            </buttons>
            <loading :loading="fetchingFiles" />
        </template>
    </bloc>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import FileService from "@/apis/project/prospect/file";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";
import { SET_PROSPECT } from "@/actions/project/prospect";

// Components
import Bloc from "@/components/prospect/profile/blocs/Bloc.vue";
import FileRow from "./FileRow.vue";
import UploadingFileRow from "./UploadingFileRow.vue";

export default {
    components: {
        Bloc,
        FileRow,
        UploadingFileRow,
    },

    props: {
        bloc: {
            type: Object,
        },
        folder: {
            type: Object,
        },
    },

    created() {
        this.fetchFiles();
    },

    data() {
        return {
            fetchingFiles: false,
            files: [],
            uploadingFiles: [],
        };
    },

    methods: {
        showMoreFiles() {
            this.shownFilesCount += 20;
        },

        async fetchFiles() {
            this.fetchingFiles = true;

            let { data } = await FileService.get(
                this.project.slug,
                this.prospect.id,
                this.folder.id
            );
            this.files = data;
            this.fetchingFiles = false;
        },

        /**
         * Manage prospect files
         * See: @/components/prospect/file/Slide.vue
         */
        manageFiles() {
            store.commit(OPEN_SLIDE, "prospect-manage-files");
            store.commit(SET_PROSPECT, this.prospect);
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
        fileUploaded(uploading, uploaded) {
            this.uploadingFiles = this.uploadingFiles.filter(
                (f) => f != uploading
            );

            this.files = [...this.files, uploaded];
        },
    },

    computed: {
        ...mapGetters(["project", "prospect"]),
    },
};
</script>
