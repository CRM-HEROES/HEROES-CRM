<template>
    <slide
        name="user-manage-order-steps"
        :title="$t('user.order_steps.title') + ' (' + userFullName + ')'"
        icon="fa fa-step-forward"
        style="width: 260px"
        @open="fetchUserOrderSteps()"
    >
        <layout />
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_USER_ORDER_STEPS } from "@/actions/project/user/order-step";

// Components
import Layout from "./Layout.vue";

export default {
    components: {
        Layout,
    },

    data() {
        return {
            orderStepKeyword: "",
        };
    },

    methods: {
        /**
         *
         */
        fetchUserOrderSteps() {
            if (this.user) {
                store.dispatch(FETCH_USER_ORDER_STEPS, this.user);
            }
        },
    },

    computed: {
        ...mapGetters(["user", "userFullName"]),

        /**
         *
         */
        filteredOrderSteps() {
            const keyword = removeStringAccent(this.orderStepKeyword);

            return this.orderSteps.filter(
                (orderStep) =>
                    removeStringAccent(orderStep.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        all: {
            get: function () {
                return this.userOrderSteps.length == this.orderSteps.length;
            },
            set: async function (value) {
                if (value) {
                    this.orderSteps.forEach((f) => {
                        store.commit(ADD_USER_ORDER_STEP, f);
                    });
                    store.dispatch(BULK_ADD_USER_ORDER_STEP, {
                        users: [this.user.id],
                        orderSteps: this.orderSteps.map((f) => f.id),
                    });
                } else {
                    this.orderSteps.forEach((f) => {
                        store.commit(REMOVE_USER_ORDER_STEP, f);
                    });
                    store.dispatch(BULK_REMOVE_USER_ORDER_STEP, {
                        users: [this.user.id],
                        orderSteps: this.orderSteps.map((f) => f.id),
                    });
                }
            },
        },
    },
};
</script>
