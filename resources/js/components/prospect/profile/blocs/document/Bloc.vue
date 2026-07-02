<template>
    <bloc
        icon="fa fa-file-pdf icon-garnet"
        :name="$t('prospect.profile.bloc.documents')"
    >
        <template #options>
            <icon
                tag="a"
                class="fa fa-plus"
                @click.prevent.stop="addDocument"
            />
        </template>
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
                <template
                    v-for="(document, i) in documents.filter(
                        (d) => d.for == 'prospect'
                    )"
                    :key="document.id"
                >
                    <document-row
                        v-if="i <= shownDocumentsCount"
                        :document="document"
                    />
                </template>
            </div>

            <buttons v-if="documents.length > shownDocumentsCount">
                <button
                    @click.prevent="showMoreDocuments"
                    v-text="$t('show_more')"
                ></button>
            </buttons>
            <loading :loading="fetchingDocuments" />
        </template>
    </bloc>
</template>

<style></style>

<script>
import store from "@/store";
import { mapGetters } from "vuex";

// Actions
import { OPEN_MODAL } from "@/actions/modal";

// Components
import Bloc from "@/components/prospect/profile/blocs/Bloc.vue";
import DocumentRow from "./DocumentRow.vue";

export default {
    components: {
        Bloc,
        DocumentRow,
    },

    data() {
        return {
            shownDocumentsCount: 5,
        };
    },

    methods: {
        showMoreDocuments() {
            this.shownDocumentsCount += 20;
        },

        addDocument() {
            store.commit(OPEN_MODAL, "document-add");
        },
    },

    computed: {
        ...mapGetters(["project", "prospect", "documents"]),
    },
};
</script>
