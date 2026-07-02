<template>
    <form class="hc-flex-column" style="height: 100%">
        <search v-model="orderStatusKeyword" />
        <div
            style="
                overflow: auto;
                padding: 8px;
                width: 100%;
                height: 100%;
                position: relative;
            "
        >
            <document-row
                v-for="document in filteredDocuments"
                :key="document.id"
                :document="document"
                @click=""
            />
        </div>
        <buttons v-if="can('all.project.document.add')">
            <a @click.prevent="addDocument" v-text="$t('add')"></a>
        </buttons>
    </form>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { FETCH_DOCUMENTS } from "@/actions/project/document";
import { OPEN_MODAL } from "@/actions/modal";

import DocumentRow from "./Document/DocumentRow.vue";

export default {
    components: {
        DocumentRow,
    },

    data() {
        return {
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

        /**
         *
         */
        fetchDocuments() {
            store.dispatch(FETCH_DOCUMENTS);
        },
    },

    computed: {
        ...mapGetters(["documents", "can"]),

        /**
         *
         */
        filteredDocuments() {
            const keyword = removeStringAccent(this.documentKeyword);

            return this.documents.filter(
                (document) =>
                    (document.for == "order" || document.for == "invoice") &&
                    removeStringAccent(document.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
