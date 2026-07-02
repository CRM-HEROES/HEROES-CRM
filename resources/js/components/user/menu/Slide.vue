<template>
    <slide
        name="user-manage-menus"
        :title="$t('user.menus.title') + ' (' + userFullName + ')'"
        icon="fa fa-list"
        style="width: 260px"
        @open="fetchUserMenus()"
    >
        <layout />
    </slide>
</template>

<script>
import store from "@/store";

import { mapGetters } from "vuex";

// Actions
import { FETCH_USER_MENUS } from "@/actions/project/user/menu";

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
        fetchUserMenus() {
            if (this.user) {
                store.dispatch(FETCH_USER_MENUS, this.user);
            }
        },
    },

    computed: {
        ...mapGetters(["user", "userFullName"]),
    },
};
</script>
