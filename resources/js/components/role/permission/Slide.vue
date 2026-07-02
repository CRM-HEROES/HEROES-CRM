<template>
    <slide
        name="role-manage-permissions"
        :title="$t('user.permissions.title') + ' (' + roleFullName + ')'"
        style="width: 360px"
        @open="fetchRolePermissions()"
    >
        <layout />
    </slide>
</template>

<script>
import store from "@/store";
import { mapGetters } from "vuex";

// Actions
import { FETCH_ROLE_PERMISSIONS } from "@/actions/project/role/permission";

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
        fetchRolePermissions() {
            if (this.role) {
                store.dispatch(FETCH_ROLE_PERMISSIONS, this.role);
            }
        },
    },

    computed: {
        ...mapGetters(["role", "roleFullName"]),
    },
};
</script>
