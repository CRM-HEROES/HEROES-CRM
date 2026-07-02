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
            />
        </item-list>
        <buttons>
            <button @click.prevent="attach" :disabled="disabled">
                Assigner
            </button>
            <button
                @click.prevent="detach"
                :disabled="disabled"
                class="hc-button-danger"
            >
                Retirer
            </button>
            <a
                v-if="can('all.project.folder.add')"
                @click.prevent="addFolder"
                v-text="$t('add')"
            ></a>
        </buttons>
        <loading :loading="bulking" />
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    BULK_ADD_USER_FOLDER,
    BULK_REMOVE_USER_FOLDER,
} from "@/actions/project/user/folder";
import {
    FETCH_USERS,
    UPDATE_SELECTED_USERS,
    UPDATE_USER_BULK_FOLDERS,
} from "@/actions/project/user";
import { OPEN_MODAL } from "@/actions/modal";
import { CLOSE_SLIDE } from "@/actions/slide";

// Components
import FolderRow from "./FolderRow.vue";

export default {
    components: {
        FolderRow,
    },

    data() {
        return {
            bulking: false,
            folderKeyword: "",
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
         */
        async attach() {
            this.bulking = true;
            const usersSelected = this.usersSelected;
            store.commit(UPDATE_SELECTED_USERS, []);

            try {
                await store.dispatch(BULK_ADD_USER_FOLDER, {
                    users: usersSelected,
                    folders: this.userBulkFolders,
                });
                store.dispatch(FETCH_USERS);
            } finally {
                this.bulking = false;
                store.commit(UPDATE_USER_BULK_FOLDERS, []);
                store.commit(CLOSE_SLIDE, "user-bulk-manage-folders");
            }
        },

        /**
         *
         */
        async detach() {
            this.bulking = true;
            const usersSelected = this.usersSelected;
            store.commit(UPDATE_SELECTED_USERS, []);

            try {
                await store.dispatch(BULK_REMOVE_USER_FOLDER, {
                    users: usersSelected,
                    folders: this.userBulkFolders,
                });
                store.dispatch(FETCH_USERS);
            } finally {
                this.bulking = false;
                store.commit(UPDATE_USER_BULK_FOLDERS, []);
                store.commit(CLOSE_SLIDE, "user-bulk-manage-folders");
            }
        },
    },

    computed: {
        ...mapGetters(["folders", "usersSelected", "userBulkFolders", "can"]),

        /**
         *
         */
        disabled() {
            return this.userBulkFolders.length == 0;
        },

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
                for (let i in this.filteredFolders) {
                    if (
                        !this.userBulkFolders.find(
                            (folder) => folder == this.filteredFolders[i].id
                        )
                    ) {
                        return false;
                    }
                }

                return true;
            },
            set: async function (value) {
                if (value) {
                    store.commit(
                        UPDATE_USER_BULK_FOLDERS,
                        this.filteredFolders.map((folder) => folder.id)
                    );
                } else {
                    store.commit(UPDATE_USER_BULK_FOLDERS, []);
                }
            },
        },
    },
};
</script>
