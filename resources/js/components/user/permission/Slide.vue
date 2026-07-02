<template>
    <slide
        name="user-manage-permissions"
        :title="$t('user.permissions.title') + ' (' + userFullName + ')'"
        style="width: 360px"
        @open="fetchUserPermissions()"
    >
        <layout />
    </slide>
</template>

<script>
import store from "@/store";
import { mapGetters } from "vuex";

// Actions
import { FETCH_USER_PERMISSIONS } from "@/actions/project/user/permission";

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
        fetchUserPermissions() {
            if (this.user) {
                store.dispatch(FETCH_USER_PERMISSIONS, this.user);
            }
        },
    },

    computed: {
        ...mapGetters(["user", "userFullName"]),
    },
};
</script>
