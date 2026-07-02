<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="folderKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label" class="hc-users-table-filter-folder">
                <icon class="fa fa-tag" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('user.table.filters.folders.with')"
                ></div>
                <icon
                    tag="a"
                    class="fa fa-thumbs-down"
                    :style="excludeFolderStyle"
                    :title="$t('user.table.filters.folders.without')"
                    @click.prevent="toggleExcludeFolder"
                />
                <checkbox
                    :model-value="isCheckedFolder"
                    @change="changeFolder"
                />
            </item>
            <folder-row
                v-for="folder in filteredFolders"
                :key="folder.id"
                :folder="folder"
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
    REMOVE_USER_PARAMS,
    ADD_USER_PARAMS,
    FETCH_USERS,
} from "@/actions/project/user";
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
         */
        addFolder() {
            store.commit(OPEN_MODAL, "folder-add");
        },

        changeFolder(event) {
            // Remove without param
            store.commit(REMOVE_USER_PARAMS, {
                key: this.withoutKeyFolder,
            });

            // Add or remove with param
            store.commit(
                event.target.checked ? ADD_USER_PARAMS : REMOVE_USER_PARAMS,
                {
                    key: this.withKeyFolder,
                }
            );
            store.dispatch(FETCH_USERS);
        },

        toggleExcludeFolder() {
            // Add or remove with param
            store.commit(
                this.isExcludedFolder ? ADD_USER_PARAMS : REMOVE_USER_PARAMS,
                {
                    key: this.withKeyFolder,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcludedFolder ? REMOVE_USER_PARAMS : ADD_USER_PARAMS,
                {
                    key: this.withoutKeyFolder,
                }
            );

            store.dispatch(FETCH_USERS);
        },
    },

    computed: {
        ...mapGetters(["folders", "user", "usersParamValue", "can"]),

        /**
         *
         */
        withKeyFolder() {
            return "withFolders";
        },

        /**
         *
         */
        withoutKeyFolder() {
            return "withoutFolders";
        },

        /**
         *
         */
        isCheckedFolder() {
            return (
                this.usersParamValue(this.withKeyFolder) === "" ||
                this.usersParamValue(this.withoutKeyFolder) === ""
            );
        },

        /**
         *
         */
        isExcludedFolder() {
            return this.usersParamValue(this.withoutKeyFolder) === "";
        },

        /**
         *
         */
        excludeFolderStyle() {
            return {
                color: this.isExcludedFolder ? "#CC0000" : "#CCCCCC",
            };
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
    },
};
</script>
