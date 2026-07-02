<template>
    <slide
        name="role-manage-order-steps"
        :title="$t('user.order_steps.title') + ' (' + roleFullName + ')'"
        icon="fa fa-step-forward"
        style="width: 260px"
        @open="fetchRoleOrderSteps()"
    >
        <layout />
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_ROLE_ORDER_STEPS } from "@/actions/project/role/order-step";

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
        fetchRoleOrderSteps() {
            if (this.role) {
                store.dispatch(FETCH_ROLE_ORDER_STEPS, this.role);
            }
        },
    },

    computed: {
        ...mapGetters(["role", "roleFullName"]),
    },
};
</script>
