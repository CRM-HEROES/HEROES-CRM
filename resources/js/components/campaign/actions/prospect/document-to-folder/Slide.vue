<template>
    <slide
        :name="name"
        :title="$t('campaign.action.prospect.document_to_folder.title')"
        icon="fa fa-folder"
        style="width: 300px"
    >
        <tab-layout :count="2" :tab="tab" class="hc-flex-1">
            <template #1>
                <div class="hc-flex-column" style="height: 100%">
                    <item-list padding="12px" style="height: auto">
                        <item
                            class="hc-campaign-action-message-item"
                            @click="(frameTab = 0), (tab = 1)"
                        >
                            <icon class="fa fa-file-pdf" :size="36" />
                            <div class="hc-item-main-content hc-flex-column">
                                <span
                                    class="hc-campaign-action-message-item-title"
                                    v-text="
                                        $t(
                                            'campaign.action.prospect.document_to_folder.document'
                                        )
                                    "
                                ></span>
                                <span
                                    class="hc-campaign-action-message-item-value"
                                    v-text="
                                        document
                                            ? document.name
                                            : $t(
                                                  'campaign.action.prospect.document_to_folder.select_document'
                                              )
                                    "
                                ></span>
                            </div>
                            <icon class="fa fa-caret-right" />
                        </item>
                        <item
                            class="hc-campaign-action-message-item"
                            @click="(frameTab = 1), (tab = 1)"
                        >
                            <icon class="fa fa-envelope" :size="36" />
                            <div class="hc-item-main-content hc-flex-column">
                                <span
                                    class="hc-campaign-action-message-item-title"
                                    v-text="
                                        $t(
                                            'campaign.action.prospect.document_to_folder.folder'
                                        )
                                    "
                                ></span>
                                <span
                                    class="hc-campaign-action-message-item-value"
                                    v-text="
                                        folder
                                            ? folder.name
                                            : $t(
                                                  'campaign.action.prospect.document_to_folder.select_folder'
                                              )
                                    "
                                ></span>
                            </div>
                            <icon class="fa fa-caret-right" />
                        </item>
                    </item-list>
                    <buttons>
                        <campaign-template
                            tag="button"
                            :field="action"
                            @dragging="dragging"
                            @dragged="dragged"
                            v-text="$t('add')"
                            :disabled="
                                !action.value.document || !action.value.folder
                            "
                        ></campaign-template>
                    </buttons>
                </div>
            </template>

            <!-- List of folders -->
            <template #2>
                <frame-layout :count="2" :tab="frameTab" class="hc-flex-1">
                    <template #1>
                        <div class="hc-flex-column" style="height: 100%">
                            <item @click="tab = 0" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        $t(
                                            'campaign.action.prospect.document_to_folder.select_document'
                                        )
                                    "
                                ></div>
                            </item>
                            <search v-model="documentKeyword" />
                            <item-list class="hc-flex-1" padding="5px">
                                <document-row
                                    v-for="c in filteredDocuments"
                                    :key="c.id"
                                    :document="c"
                                    @click="
                                        (action.value.document = c.id),
                                            (tab = 0)
                                    "
                                />
                            </item-list>
                            <buttons>
                                <a
                                    @click.prevent="addDocument"
                                    v-text="$t('add')"
                                ></a>
                            </buttons>
                        </div>
                    </template>
                    <template #2>
                        <div class="hc-flex-column" style="height: 100%">
                            <item @click="tab = 0" class="bordered">
                                <icon class="fa fa-caret-left" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        $t(
                                            'campaign.action.prospect.document_to_folder.select_folder'
                                        )
                                    "
                                ></div>
                            </item>
                            <search v-model="folderKeyword" />
                            <item-list class="hc-flex-1" padding="5px">
                                <folder-row
                                    v-for="c in filteredFolders"
                                    :key="c.id"
                                    :folder="c"
                                    @click="
                                        (action.value.folder = c.id), (tab = 0)
                                    "
                                />
                            </item-list>
                            <buttons>
                                <a
                                    @click.prevent="addFolder"
                                    v-text="$t('add')"
                                ></a>
                            </buttons>
                        </div>
                    </template>
                </frame-layout>
            </template>
        </tab-layout>
    </slide>
</template>

<style>
.hc-campaign-action-message-item {
    padding: 3px 0 !important;
    align-items: start;
}

.hc-campaign-action-message-item-title {
    color: #000000;
}

.hc-campaign-action-message-item-value {
    color: #000000;
    font-size: 11px;
    opacity: 0.7;
}
</style>

<script>
import store from "@/store";
import { mapGetters } from "vuex";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { CLOSE_SLIDE } from "@/actions/slide";

// Components
import FolderRow from "./FolderRow.vue";
import DocumentRow from "./DocumentRow.vue";
import CampaignTemplate from "@/components/campaign/components/Template.vue";

export default {
    components: {
        FolderRow,
        DocumentRow,
        CampaignTemplate,
    },

    data() {
        return {
            name: "campaign-action-prospect-document-to-folder",
            tab: 0,
            frameTab: 0,
            folderKeyword: "",
            documentKeyword: "",
            action: {
                action: "prospect-file-document",
                name: "Envoyer un message",
                value: {
                    folder: null,
                    document: null,
                },
                category: "action",
                style: {
                    width: "200px",
                },
            },
        };
    },

    methods: {
        /**
         *
         */
        addDocument() {
            store.commit(OPEN_MODAL, "document-add");
        },

        /**
         *
         */
        addFolder() {
            store.commit(OPEN_MODAL, "folder-add");
        },

        dragging(field) {
            this.$emit("dragging", field);
        },

        dragged(x, y) {
            this.$emit("dragged", x, y);
            store.commit(CLOSE_SLIDE);
        },
    },

    watch: {
        document() {
            if (this.document) {
                this.action.name = this.actionName;
            }
        },

        folder() {
            if (this.folder) {
                this.action.name = this.actionName;
                this.action.style.color = this.folder.color;
                this.action.style.backgroundColor = this.folder.bgcolor;
            }
        },
    },

    computed: {
        ...mapGetters(["folders", "documents"]),

        folder() {
            return this.folders.find(
                (folder) => folder.id === this.action.value.folder
            );
        },

        document() {
            return this.documents.find(
                (document) => document.id === this.action.value.document
            );
        },

        actionName() {
            if (!this.document) {
                return;
            }

            if (!this.folder) {
                return;
            }

            return this.$t("campaign.action.prospect.document_to_folder.name", {
                document: this.document.name,
                folder: this.folder.name,
            });
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
        filteredDocuments() {
            const keyword = removeStringAccent(this.documentKeyword);

            return this.documents.filter(
                (document) =>
                    removeStringAccent(document.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
