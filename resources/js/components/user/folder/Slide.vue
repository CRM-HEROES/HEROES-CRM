<template>
    <slide
        name="user-manage-folders"
        :title="$t('user.folders.title') + ' (' + userFullName + ')'"
        icon="fa fa-folder"
        style="width: 260px"
        @open="fetchUserFolders()"
    >
        <layout />
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_USER_FOLDERS } from "@/actions/project/user/folder";

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
        fetchUserFolders() {
            if (this.user) {
                store.dispatch(FETCH_USER_FOLDERS, this.user);
            }
        },
    },

    computed: {
        ...mapGetters(["user", "userFullName"]),
    },
};
</script>
