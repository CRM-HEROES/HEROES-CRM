<template>
    <slide
        name="user-manage-calendars"
        :title="$t('user.calendars.title') + ' (' + userFullName + ')'"
        icon="fa fa-calendar"
        style="width: 260px"
        @open="fetchUserCalendars()"
    >
        <layout />
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_USER_CALENDARS } from "@/actions/project/user/calendar";

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
        fetchUserCalendars() {
            if (this.user) {
                store.dispatch(FETCH_USER_CALENDARS, this.user);
            }
        },
    },

    computed: {
        ...mapGetters(["user", "userFullName"]),
    },
};
</script>
