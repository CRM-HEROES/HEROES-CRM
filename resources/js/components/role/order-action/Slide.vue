<template>
    <slide
        name="role-manage-order-actions"
        :title="$t('user.order_actions.title') + ' (' + roleFullName + ')'"
        icon="fa fa-person-digging"
        style="width: 260px"
        @open="fetchRoleOrderActions()"
    >
        <layout />
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_ROLE_ORDER_ACTIONS } from "@/actions/project/role/order-action";

// Components
import OrderActionRow from "./OrderActionRow.vue";

export default {
    components: {
        OrderActionRow,
    },

    methods: {
        /**
         *
         */
        fetchRoleOrderActions() {
            if (this.role) {
                store.dispatch(FETCH_ROLE_ORDER_ACTIONS, this.role);
            }
        },
    },

    computed: {
        ...mapGetters(["roleFullName", "role"]),
    },
};
</script>
