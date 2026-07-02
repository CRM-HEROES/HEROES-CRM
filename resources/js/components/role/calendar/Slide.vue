<template>
    <slide
        name="role-manage-calendars"
        :title="$t('user.calendars.title') + ' (' + roleFullName + ')'"
        icon="fa fa-calendar"
        style="width: 260px"
        @open="fetchRoleCalendars()"
    >
        <layout />
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_ROLE_CALENDARS } from "@/actions/project/role/calendar";

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
        fetchRoleCalendars() {
            if (this.role) {
                store.dispatch(FETCH_ROLE_CALENDARS, this.role);
            }
        },
    },

    computed: {
        ...mapGetters(["roleFullName", "role"]),
    },
};
</script>
