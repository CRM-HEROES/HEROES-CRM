<template>
    <slide
        name="user-manage-documents"
        :title="$t('user.documents.title') + ' (' + userFullName + ')'"
        icon="fa fa-file-pdf"
        style="width: 260px"
        @open="fetchUserDocuments()"
    >
        <layout />
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_USER_DOCUMENTS } from "@/actions/project/user/document";

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
        fetchUserDocuments() {
            if (this.user) {
                store.dispatch(FETCH_USER_DOCUMENTS, this.user);
            }
        },
    },

    computed: {
        ...mapGetters(["user", "userFullName"]),
    },
};
</script>
