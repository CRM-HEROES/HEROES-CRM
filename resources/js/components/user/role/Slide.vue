<template>
    <slide
        name="user-manage-roles"
        :title="$t('user.roles.title') + ' (' + userFullName + ')'"
        icon="fa fa-users"
        style="width: 260px"
        @open="fetchUserRoles()"
    >
        <layout />
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_USER_ROLES } from "@/actions/project/user/role";

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
        fetchUserRoles() {
            if (this.user) {
                store.dispatch(FETCH_USER_ROLES, this.user);
            }
        },
    },

    computed: {
        ...mapGetters(["user", "userFullName"]),
    },
};
</script>
