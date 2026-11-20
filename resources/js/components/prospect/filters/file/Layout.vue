<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="folderKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label" class="hc-prospects-table-filter-folder">
                <icon class="fa fa-filter" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('prospect.table.filters.file.with_files')"
                ></div>
                <icon
                    tag="a"
                    class="fa fa-thumbs-down"
                    :style="excludeFolderStyle"
                    :title="$t('prospect.table.filters.file.without_file')"
                    @click.prevent="toggleExcludeFolder"
                />
                <checkbox
                    :model-value="isCheckedFolder"
                    @change="changeFolder"
                />
            </item>
            <selection-list
                :items="filteredFolders"
                :is-selected="isFolderSelected"
                @select-all="selectAllFolders"
            >
                <template #default="{ sortedItems }">
                    <folder-row
                        v-for="folder in sortedItems"
                        :key="folder.id"
                        :folder="folder"
                    />
                </template>
            </selection-list>
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
    REMOVE_PROSPECT_PARAMS,
    ADD_PROSPECT_PARAMS,
    FETCH_PROSPECTS,
} from "@/actions/project/prospect";
import { OPEN_MODAL } from "@/actions/modal";
import { setProspectFilterSelection } from "@/utils/prospect-filter-selection";

// Components
import SelectionList from "../SelectionList.vue";
import FolderRow from "./FolderRow.vue";

export default {
    components: {
        SelectionList,
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
            store.commit(REMOVE_PROSPECT_PARAMS, {
                key: this.withoutKeyFolder,
                value: [],
            });

            // Add or remove with param
            store.commit(
                event.target.checked
                    ? ADD_PROSPECT_PARAMS
                    : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKeyFolder,
                    value: [],
                }
            );
            store.dispatch(FETCH_PROSPECTS);
        },

        toggleExcludeFolder() {
            // Add or remove with param
            store.commit(
                this.isExcludedFolder
                    ? ADD_PROSPECT_PARAMS
                    : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKeyFolder,
                    value: [],
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcludedFolder
                    ? REMOVE_PROSPECT_PARAMS
                    : ADD_PROSPECT_PARAMS,
                {
                    key: this.withoutKeyFolder,
                    value: [],
                }
            );

            store.dispatch(FETCH_PROSPECTS);
        },

        isFolderSelected(folder) {
            return (
                this.prospectsParamExists(this.withKeyFolder, folder.id) ||
                this.prospectsParamExists(this.withoutKeyFolder, folder.id)
            );
        },

        selectAllFolders(checked) {
            setProspectFilterSelection(
                this.filteredFolders,
                checked,
                this.withKeyFolder,
                this.withoutKeyFolder
            );
        },
    },

    computed: {
        ...mapGetters([
            "folders",
            "prospect",
            "prospectFullName",
            "prospectsParamExists",
            "prospectsParamValue",
            "can",
        ]),

        /**
         *
         */
        withKeyFolder() {
            return "withFiles";
        },

        /**
         *
         */
        withoutKeyFolder() {
            return "withoutFiles";
        },

        /**
         *
         */
        isCheckedFolder() {
            return (
                (this.prospectsParamExists(this.withKeyFolder) &&
                    this.prospectsParamValue(this.withKeyFolder).length == 0) ||
                (this.prospectsParamExists(this.withoutKeyFolder) &&
                    this.prospectsParamValue(this.withoutKeyFolder).length == 0)
            );
        },

        /**
         *
         */
        isExcludedFolder() {
            return (
                this.prospectsParamExists(this.withoutKeyFolder) &&
                this.prospectsParamValue(this.withoutKeyFolder).length == 0
            );
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
