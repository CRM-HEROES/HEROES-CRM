<template>
    <slide
        name="user-manage-sessions"
        :title="$t('user.sessions.title')"
        icon="fa fa-session"
        style="width: 300px"
        @open="fetchUserSessions()"
    >
        <layout />
    </slide>
</template>

<script>
import store from "@/store";

// Actions
import { FETCH_USER_SESSIONS } from "@/actions/user/session";

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
        async fetchUserSessions() {
            this.fetchingUserSessions = true;

            try {
                await store.dispatch(FETCH_USER_SESSIONS, this.user);
            } finally {
                this.fetchingUserSessions = false;
            }
        },
    },
};
</script>
