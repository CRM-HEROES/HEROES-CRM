<template>
    <slide
        :name="name"
        :title="$t('document.list.title')"
        icon="fa fa-tags"
        style="width: 260px"
        @open="fetchDocuments"
    >
        <div class="hc-flex-column" style="height: 100%">
            <search v-model="documentKeyword" />
            <item-list class="hc-flex-1" padding="5px">
                <document-row
                    v-for="document in filteredDocuments"
                    :key="document.id"
                    :document="document"
                />
            </item-list>
            <buttons v-if="can('all.project.document.add')">
                <a @click.prevent="addDocument" v-text="$t('add')"></a>
            </buttons>

            <loading :loading="fetchingDocuments" />
        </div>
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_DOCUMENTS } from "@/actions/project/document";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import DocumentRow from "./DocumentRow.vue";

export default {
    components: {
        DocumentRow,
    },

    data() {
        return {
            name: "documents",
            tab: 0,
            documentKeyword: "",
            fetchingDocuments: false,
        };
    },

    methods: {
        /**
         *
         * @param {*} document
         */
        async fetchDocuments() {
            this.fetchingDocuments = true;
            try {
                const data = await store.dispatch(FETCH_DOCUMENTS);

                if (data.length == 0) {
                    this.addDocument();
                }
            } finally {
                this.fetchingDocuments = false;
            }
        },

        /**
         *
         */
        addDocument() {
            store.commit(OPEN_MODAL, "document-add");
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
                    removeStringAccent(document.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
