<template>
    <form
        class="hc-flex-column"
        style="height: 100%"
        v-if="documentToUpdate"
        @submit.prevent="update"
    >
        <tab-layout
            :count="2"
            :tab="tab"
            class="hc-flex-1"
            style="height: 100%"
        >
            <template #1>
                <item-list gap="5px" class="hc-flex-1" padding="10px 0">
                    <v-field :label="$t('name')" required v-slot="p"
                        ><input
                            ref="documentName"
                            :placeholder="p.document + ' ...'"
                            v-model="documentToUpdate.name"
                            required
                    /></v-field>
                    <item @click="tab = 1">
                        <icon class="fa fa-folder" />
                        <div
                            class="hc-item-main-content"
                            v-text="
                                $t('document.update.folder', {
                                    folder: associatedFolder
                                        ? associatedFolder.name
                                        : '...',
                                })
                            "
                        ></div>
                        <icon class="fa fa-caret-right" />
                    </item>
                </item-list>
            </template>
            <template #2>
                <div class="hc-flex-column" style="height: 100%">
                    <item @click="tab = 0">
                        <icon class="fa fa-caret-left" />
                        <div
                            class="hc-item-main-content"
                            v-text="
                                $t('document.update.folder', {
                                    folder: '...',
                                })
                            "
                        ></div>
                    </item>
                    <search v-model="folderKeyword" />
                    <item-list class="hc-flex-1" padding="5px">
                        <item
                            @click="
                                (documentToUpdate.folder_id = null), (tab = 0)
                            "
                        >
                            <icon class="fa fa-times" />
                            <div
                                class="hc-item-main-content"
                                v-text="$t('none')"
                            ></div>
                        </item>
                        <folder-row
                            v-for="folder in filteredFolders"
                            :key="folder.id"
                            :folder="folder"
                            @click.prevent="
                                (documentToUpdate.folder_id = folder.id),
                                    (tab = 0)
                            "
                        />
                    </item-list>
                    <loading :loading="fetchingMenuUsers" />
                </div>
            </template>
        </tab-layout>
        <buttons>
            <button
                @click.prevent="remove"
                class="hc-button-danger"
                v-text="$t('delete')"
            ></button>
            <button v-text="$t('update')"></button>
        </buttons>
        <loading :loading="updatingDocument || removingDocument" />
    </form>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { UPDATE_DOCUMENT, REMOVE_DOCUMENT } from "@/actions/project/document";
import { CLOSE_MODAL } from "@/actions/modal";

import FolderRow from "./FolderRow.vue";

export default {
    components: {
        FolderRow,
    },

    data() {
        return {
            tab: 0,
            updatingDocument: false,
            removingDocument: false,
            fetchingDocument: false,
            documentToUpdate: this.document,
            folderKeyword: "",
        };
    },

    created() {
        this.documentToUpdate = this.document;
    },

    methods: {
        /**
         *
         */
        async update() {
            this.updatingDocument = true;

            try {
                await store.dispatch(UPDATE_DOCUMENT, {
                    id: this.documentToUpdate.id,
                    name: this.documentToUpdate.name,
                    folder_id: this.documentToUpdate.folder_id
                        ? this.documentToUpdate.folder_id
                        : null,
                });
            } finally {
                this.updatingDocument = false;
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingDocument = true;

                try {
                    await store.dispatch(
                        REMOVE_DOCUMENT,
                        this.documentToUpdate.id
                    );
                } finally {
                    this.removingDocument = false;
                    store.commit(CLOSE_MODAL);
                }
            });
        },
    },

    watch: {
        async document(newValue) {
            if (newValue) {
                this.documentToUpdate = newValue;

                /*this.fetchingDocument = true;
                this.documentToUpdate = await store.dispatch(
                    SHOW_DOCUMENT,
                    newValue.id
                );
                this.fetchingDocument = false;*/
            }
        },
    },

    computed: {
        ...mapGetters(["document", "folders"]),

        /**
         *
         */
        associatedFolder() {
            return this.documentToUpdate.folder_id
                ? this.folders.find(
                      (folder) => folder.id == this.documentToUpdate.folder_id
                  )
                : null;
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
