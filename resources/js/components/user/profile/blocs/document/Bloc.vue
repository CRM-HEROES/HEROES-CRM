<template>
    <bloc
        icon="fa fa-file-pdf icon-brown"
        :name="$t('user.profile.blocs.documents')"
    >
        <template #options>
            <icon
                tag="a"
                class="fa fa-plus icon-blue"
                @click.prevent.stop="addDocument"
            />
        </template>
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
                <template
                    v-for="(document, i) in userDocuments"
                    :key="document.id"
                >
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
import { OPEN_SLIDE } from "@/actions/slide";

// Components
import Bloc from "@/components/user/profile/blocs/Bloc.vue";
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
            store.commit(OPEN_SLIDE, "user-manage-documents");
        },
    },

    computed: {
        ...mapGetters(["project", "userDocuments"]),
    },
};
</script>
