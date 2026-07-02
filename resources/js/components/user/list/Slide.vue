<template>
    <slide
        name="manage-users"
        :title="$t('user.list.title')"
        icon="fa fa-user"
        style="width: 260px"
        @open="fetchUsers"
    >
        <layout />
    </slide>
</template>

<script>
import store from "@/store";

import Layout from "./Layout.vue";

// Actions
import { FETCH_USERS } from "@/actions/project/user";

export default {
    components: {
        Layout,
    },

    methods: {
        /**
         *
         * @param {*} user
         */
        fetchUsers() {
            store.dispatch(FETCH_USERS);
        },
    },
};
</script>
