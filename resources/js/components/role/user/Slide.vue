<template>
    <slide
        name="role-manage-users"
        :title="$t('user.users.title') + ' (' + roleFullName + ')'"
        icon="fa fa-user"
        style="width: 260px"
        @open="fetchUserUsers()"
    >
        <layout />
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_ROLE_USERS } from "@/actions/project/role/user";

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
        fetchUserUsers() {
            if (this.user) {
                store.dispatch(FETCH_ROLE_USERS, this.user);
            }
        },
    },

    computed: {
        ...mapGetters(["role", "roleFullName"]),
    },
};
</script>
