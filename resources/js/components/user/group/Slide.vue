<template>
    <slide
        name="user-manage-groups"
        :title="$t('user.groups.title') + ' (' + userFullName + ')'"
        icon="fa fa-users"
        style="width: 260px"
        @open="fetchUserGroups()"
    >
        <layout />
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_USER_GROUPS } from "@/actions/project/user/group";

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
        fetchUserGroups() {
            if (this.user) {
                store.dispatch(FETCH_USER_GROUPS, this.user);
            }
        },
    },

    computed: {
        ...mapGetters(["user", "userFullName"]),
    },
};
</script>
