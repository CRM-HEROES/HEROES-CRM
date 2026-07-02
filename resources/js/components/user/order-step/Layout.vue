<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="orderStepKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label">
                <icon class="fa fa-check-square" />
                <div class="hc-item-main-content" v-text="$t('all')"></div>
                <checkbox v-model="all" />
            </item>
            <order-step-row
                v-for="orderStep in filteredOrderSteps"
                :key="orderStep.id"
                :order-step="orderStep"
                :value="isOrderStepChecked(orderStep)"
            />
        </item-list>
        <buttons v-if="can('all.project.order.step.add')">
            <a @click.prevent="addOrderStep" v-text="$t('add')"></a>
        </buttons>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    BULK_ADD_USER_ORDER_STEP,
    BULK_REMOVE_USER_ORDER_STEP,
    ADD_USER_ORDER_STEP,
    REMOVE_USER_ORDER_STEP,
} from "@/actions/project/user/order-step";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import OrderStepRow from "./OrderStepRow.vue";

export default {
    components: {
        OrderStepRow,
    },

    data() {
        return {
            orderStepKeyword: "",
        };
    },

    methods: {
        /**
         *
         * @param {*} orderStep
         */
        isOrderStepChecked(orderStep) {
            return (
                // this.can("all") ||
                this.userOrderSteps.findIndex((l) => l.id == orderStep.id) >= 0
            );
        },

        /**
         *
         */
        addOrderStep() {
            store.commit(OPEN_MODAL, "order-step-add");
        },
    },

    computed: {
        ...mapGetters(["orderSteps", "user", "userOrderSteps", "can"]),

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
                return (
                    // this.can("all") ||
                    this.userOrderSteps.length == this.orderSteps.length
                );
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
