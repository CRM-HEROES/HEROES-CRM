<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="documentKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <selection-list
                v-model="all"
                :items="filteredDocuments"
                :is-selected="isDocumentChecked"
             :render-when-empty="true">
                <template #default="{ sortedItems }">
                    <document-row
                        v-for="document in sortedItems"
                        :key="document.id"
                        :document="document"
                        :value="isDocumentChecked(document)"
                        :disabled="canAll"
                    />
                </template>
            </selection-list>
        </item-list>
        <buttons v-if="can('all.project.document.add')">
            <a @click.prevent="addDocument" v-text="$t('add')"></a>
        </buttons>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    BULK_ADD_ROLE_DOCUMENT,
    BULK_REMOVE_ROLE_DOCUMENT,
    ADD_ROLE_DOCUMENT,
    REMOVE_ROLE_DOCUMENT,
} from "@/actions/project/role/document";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import DocumentRow from "./DocumentRow.vue";

export default {
    components: {
        DocumentRow,
    },

    data() {
        return {
            name: "role-manage-documents",
            tab: 0,
            documentKeyword: "",
        };
    },

    methods: {
        /**
         *
         * @param {*} document
         */
        isDocumentChecked(document) {
            return (
                this.canAll ||
                this.roleDocuments.findIndex((l) => l.id == document.id) >= 0
            );
        },

        /**
         *
         */
        addDocument() {
            store.commit(OPEN_MODAL, "document-add");
        },
    },

    computed: {
        ...mapGetters([
            "documents",
            "role",
            "roleDocuments",
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
        filteredDocuments() {
            const keyword = removeStringAccent(this.documentKeyword);

            return this.documents.filter(
                (document) =>
                    removeStringAccent(document.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        all: {
            get: function () {
                return (
                    this.canAll ||
                    this.roleDocuments.length == this.documents.length
                );
            },
            set: async function (value) {
                if (value) {
                    this.documents.forEach((f) => {
                        store.commit(ADD_ROLE_DOCUMENT, f);
                    });
                    store.dispatch(BULK_ADD_ROLE_DOCUMENT, {
                        roles: [this.role.id],
                        documents: this.documents.map((f) => f.id),
                    });
                } else {
                    this.documents.forEach((f) => {
                        store.commit(REMOVE_ROLE_DOCUMENT, f);
                    });
                    store.dispatch(BULK_REMOVE_ROLE_DOCUMENT, {
                        roles: [this.role.id],
                        documents: this.documents.map((f) => f.id),
                    });
                }
            },
        },
    },
};
</script>
