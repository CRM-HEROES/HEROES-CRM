<template>
    <slide
        name="user-manage-order-actions"
        :title="$t('user.order_actions.title') + ' (' + userFullName + ')'"
        icon="fa fa-person-digging"
        style="width: 260px"
        @open="fetchUserOrderActions()"
    >
        <layout />
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_USER_ORDER_ACTIONS } from "@/actions/project/user/order-action";

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
        fetchUserOrderActions() {
            if (this.user) {
                store.dispatch(FETCH_USER_ORDER_ACTIONS, this.user);
            }
        },
    },

    computed: {
        ...mapGetters(["user", "userFullName"]),
    },
};
</script>
