<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="documentKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label" class="hc-users-table-filter-document">
                <icon class="fa fa-tag" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('user.table.filters.documents.with')"
                ></div>
                <icon
                    tag="a"
                    class="fa fa-thumbs-down"
                    :style="excludeDocumentStyle"
                    :title="$t('user.table.filters.documents.without')"
                    @click.prevent="toggleExcludeDocument"
                />
                <checkbox
                    :model-value="isCheckedDocument"
                    @change="changeDocument"
                />
            </item>
            <document-row
                v-for="document in filteredDocuments"
                :key="document.id"
                :document="document"
            />
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
    REMOVE_USER_PARAMS,
    ADD_USER_PARAMS,
    FETCH_USERS,
} from "@/actions/project/user";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import DocumentRow from "./DocumentRow.vue";

export default {
    components: {
        DocumentRow,
    },

    data() {
        return {
            tab: 0,
            documentKeyword: "",
        };
    },

    methods: {
        /**
         *
         */
        addDocument() {
            store.commit(OPEN_MODAL, "document-add");
        },

        changeDocument(event) {
            // Remove without param
            store.commit(REMOVE_USER_PARAMS, {
                key: this.withoutKeyDocument,
            });

            // Add or remove with param
            store.commit(
                event.target.checked ? ADD_USER_PARAMS : REMOVE_USER_PARAMS,
                {
                    key: this.withKeyDocument,
                }
            );
            store.dispatch(FETCH_USERS);
        },

        toggleExcludeDocument() {
            // Add or remove with param
            store.commit(
                this.isExcludedDocument ? ADD_USER_PARAMS : REMOVE_USER_PARAMS,
                {
                    key: this.withKeyDocument,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcludedDocument ? REMOVE_USER_PARAMS : ADD_USER_PARAMS,
                {
                    key: this.withoutKeyDocument,
                }
            );

            store.dispatch(FETCH_USERS);
        },
    },

    computed: {
        ...mapGetters(["documents", "user", "usersParamValue", "can"]),

        /**
         *
         */
        withKeyDocument() {
            return "withDocuments";
        },

        /**
         *
         */
        withoutKeyDocument() {
            return "withoutDocuments";
        },

        /**
         *
         */
        isCheckedDocument() {
            return (
                this.usersParamValue(this.withKeyDocument) === "" ||
                this.usersParamValue(this.withoutKeyDocument) === ""
            );
        },

        /**
         *
         */
        isExcludedDocument() {
            return this.usersParamValue(this.withoutKeyDocument) === "";
        },

        /**
         *
         */
        excludeDocumentStyle() {
            return {
                color: this.isExcludedDocument ? "#CC0000" : "#CCCCCC",
            };
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
