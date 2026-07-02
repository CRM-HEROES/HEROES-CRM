<template>
    <slide
        name="role-manage-threads"
        :title="$t('user.threads.title') + ' (' + roleFullName + ')'"
        icon="fa fa-envelope"
        style="width: 260px"
        @open="fetchRoleThreads()"
    >
        <layout />
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_ROLE_THREADS } from "@/actions/project/role/thread";

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
        fetchRoleThreads() {
            if (this.role) {
                store.dispatch(FETCH_ROLE_THREADS, this.role);
            }
        },
    },

    computed: {
        ...mapGetters(["role", "roleFullName"]),
    },
};
</script>
