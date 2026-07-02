<template>
    <bloc icon="fa fa-file-pdf" :name="$t('project.profile.blocs.documents')">
        <template #options>
            <icon
                tag="a"
                class="fa fa-plus"
                @click.prevent.stop="addDocument"
            />
            <icon v-if="documents.length > 0" class="fa fa-caret-down" />
        </template>
        <template #body>
            <div
                style="padding: 10px 10px; float: left; width: 100%"
                v-if="documents.length > 0"
            >
                <template v-for="(document, i) in documents" :key="document.id">
                    <document-row :document="document" />
                </template>
            </div>
            <loading :loading="fetchingDocuments" />
        </template>
    </bloc>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";

// Components
import Bloc from "@/components/project/profile/blocs/Bloc.vue";
import DocumentRow from "./DocumentRow.vue";

export default {
    components: {
        Bloc,
        DocumentRow,
    },

    methods: {
        /**
         * Add group
         * See: @/components/group/add/Modal.vue
         */
        addDocument() {
            store.commit(OPEN_MODAL, "document-add");
        },
    },

    computed: {
        ...mapGetters(["project", "documents"]),
    },
};
</script>
