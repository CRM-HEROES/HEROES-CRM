<template>
    <slide
        name="user-manage-threads"
        :title="$t('user.threads.title') + ' (' + userFullName + ')'"
        icon="fa fa-envelope"
        style="width: 260px"
        @open="fetchUserThreads()"
    >
        <layout />
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_USER_THREADS } from "@/actions/project/user/thread";

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
        fetchUserThreads() {
            if (this.user) {
                store.dispatch(FETCH_USER_THREADS, this.user);
            }
        },
    },

    computed: {
        ...mapGetters(["user", "userFullName"]),
    },
};
</script>
