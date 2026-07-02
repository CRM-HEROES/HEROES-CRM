<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="folderKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label">
                <icon class="fa fa-check-square" />
                <div class="hc-item-main-content" v-text="$t('all')"></div>
                <checkbox v-model="all" />
            </item>
            <folder-row
                v-for="folder in filteredFolders"
                :key="folder.id"
                :folder="folder"
                :value="isFolderChecked(folder)"
            />
        </item-list>
        <buttons v-if="can('all.project.folder.add')">
            <a @click.prevent="addFolder" v-text="$t('add')"></a>
        </buttons>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    BULK_ADD_USER_FOLDER,
    BULK_REMOVE_USER_FOLDER,
    ADD_USER_FOLDER,
    REMOVE_USER_FOLDER,
} from "@/actions/project/user/folder";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import FolderRow from "./FolderRow.vue";

export default {
    components: {
        FolderRow,
    },

    data() {
        return {
            folderKeyword: "",
        };
    },

    methods: {
        /**
         *
         * @param {*} folder
         */
        isFolderChecked(folder) {
            return (
                // this.can("all") ||
                this.userFolders.findIndex((l) => l.id == folder.id) >= 0
            );
        },

        /**
         *
         */
        addFolder() {
            store.commit(OPEN_MODAL, "folder-add");
        },
    },

    computed: {
        ...mapGetters(["folders", "user", "userFolders", "can"]),

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
        all: {
            get: function () {
                return (
                    // this.can("all") ||
                    this.userFolders.length == this.folders.length
                );
            },
            set: async function (value) {
                if (value) {
                    this.folders.forEach((f) => {
                        store.commit(ADD_USER_FOLDER, f);
                    });
                    store.dispatch(BULK_ADD_USER_FOLDER, {
                        users: [this.user.id],
                        folders: this.folders.map((f) => f.id),
                    });
                } else {
                    this.folders.forEach((f) => {
                        store.commit(REMOVE_USER_FOLDER, f);
                    });
                    store.dispatch(BULK_REMOVE_USER_FOLDER, {
                        users: [this.user.id],
                        folders: this.folders.map((f) => f.id),
                    });
                }
            },
        },
    },
};
</script>
