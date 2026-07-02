<template>
    <slide
        name="user-manage-users"
        :title="$t('user.users.title') + ' (' + userFullName + ')'"
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
import { FETCH_USER_USERS } from "@/actions/project/user/user";

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
                store.dispatch(FETCH_USER_USERS, this.user);
            }
        },
    },

    computed: {
        ...mapGetters(["user", "userFullName"]),
    },
};
</script>
