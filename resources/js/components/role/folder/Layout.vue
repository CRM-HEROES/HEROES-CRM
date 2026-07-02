<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="folderKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label">
                <icon class="fa fa-check-square" />
                <div class="hc-item-main-content" v-text="$t('all')"></div>
                <checkbox v-model="all" @change="change" :disabled="canAll" />
            </item>
            <folder-row
                v-for="folder in filteredFolders"
                :key="folder.id"
                :folder="folder"
                :value="isFolderChecked(folder)"
                :disabled="canAll"
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
    BULK_ADD_ROLE_FOLDER,
    BULK_REMOVE_ROLE_FOLDER,
    ADD_ROLE_FOLDER,
    REMOVE_ROLE_FOLDER,
} from "@/actions/project/role/folder";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import FolderRow from "./FolderRow.vue";

export default {
    components: {
        FolderRow,
    },

    data() {
        return {
            tab: 0,
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
                this.canAll ||
                this.roleFolders.findIndex((l) => l.id == folder.id) >= 0
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
        ...mapGetters([
            "folders",
            "role",
            "roleFolders",
            "rolePermissions",
            "can",
        ]),

        /**
         *
         */
        canAll() {
            return this.rolePermissions.includes("all");
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
                return (
                    this.canAll ||
                    this.roleFolders.length == this.folders.length
                );
            },
            set: async function (value) {
                if (value) {
                    this.folders.forEach((f) => {
                        store.commit(ADD_ROLE_FOLDER, f);
                    });
                    store.dispatch(BULK_ADD_ROLE_FOLDER, {
                        roles: [this.role.id],
                        folders: this.folders.map((f) => f.id),
                    });
                } else {
                    this.folders.forEach((f) => {
                        store.commit(REMOVE_ROLE_FOLDER, f);
                    });
                    store.dispatch(BULK_REMOVE_ROLE_FOLDER, {
                        roles: [this.role.id],
                        folders: this.folders.map((f) => f.id),
                    });
                }
            },
        },
    },
};
</script>
