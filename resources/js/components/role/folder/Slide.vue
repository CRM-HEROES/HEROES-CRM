<template>
    <slide
        name="role-manage-folders"
        :title="$t('user.folders.title') + ' (' + roleFullName + ')'"
        icon="fa fa-folder"
        style="width: 260px"
        @open="fetchRoleFolders()"
    >
        <layout />
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_ROLE_FOLDERS } from "@/actions/project/role/folder";

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
        fetchRoleFolders() {
            if (this.role) {
                store.dispatch(FETCH_ROLE_FOLDERS, this.role);
            }
        },
    },

    computed: {
        ...mapGetters(["roleFullName", "role"]),
    },
};
</script>
