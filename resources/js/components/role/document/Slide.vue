<template>
    <slide
        name="role-manage-documents"
        :title="$t('user.documents.title') + ' (' + roleFullName + ')'"
        icon="fa fa-file-pdf"
        style="width: 260px"
        @open="fetchRoleDocuments()"
    >
        <layout />
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_ROLE_DOCUMENTS } from "@/actions/project/role/document";

// Components
import Layout from "./Layout.vue";

export default {
    components: {
        Layout,
    },

    methods: {
        /**
         *
         */
        fetchRoleDocuments() {
            if (this.role) {
                store.dispatch(FETCH_ROLE_DOCUMENTS, this.role);
            }
        },
    },

    computed: {
        ...mapGetters(["roleFullName", "role"]),
    },
};
</script>
