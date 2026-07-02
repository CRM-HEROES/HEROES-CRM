<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="orderStepKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label">
                <icon class="fa fa-check-square" />
                <div class="hc-item-main-content" v-text="$t('all')"></div>
                <checkbox v-model="all" @change="change" :disabled="canAll" />
            </item>
            <order-step-row
                v-for="orderStep in filteredOrderSteps"
                :key="orderStep.id"
                :order-step="orderStep"
                :value="isOrderStepChecked(orderStep)"
                :disabled="canAll"
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
    FETCH_ROLE_ORDER_STEPS,
    BULK_ADD_ROLE_ORDER_STEP,
    BULK_REMOVE_ROLE_ORDER_STEP,
    ADD_ROLE_ORDER_STEP,
    REMOVE_ROLE_ORDER_STEP,
} from "@/actions/project/role/order-step";
import { FETCH_ORDER_STEPS } from "@/actions/project/order/step";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import OrderStepRow from "./OrderStepRow.vue";

export default {
    components: {
        OrderStepRow,
    },

    data() {
        return {
            name: "role-manage-order-steps",
            tab: 0,
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
                this.canAll ||
                this.roleOrderSteps.findIndex((l) => l.id == orderStep.id) >= 0
            );
        },

        /**
         *
         * @param {*} orderStep
         */
        fetchOrderSteps() {
            store.dispatch(FETCH_ORDER_STEPS);
        },

        /**
         *
         */
        fetchRoleOrderSteps() {
            if (this.role) {
                store.dispatch(FETCH_ROLE_ORDER_STEPS, this.role);
            }
        },

        /**
         *
         */
        addOrderStep() {
            store.commit(OPEN_MODAL, "order-step-add");
        },
    },

    computed: {
        ...mapGetters([
            "orderSteps",
            "role",
            "roleOrderSteps",
            "rolePermissions",
            "can",
        ]),

        /**
         *
         */
        canAll() {
            return this.rolePermissions.includes("all");
        },

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
                    this.canAll ||
                    this.roleOrderSteps.length == this.orderSteps.length
                );
            },
            set: async function (value) {
                if (value) {
                    this.orderSteps.forEach((f) => {
                        store.commit(ADD_ROLE_ORDER_STEP, f);
                    });
                    store.dispatch(BULK_ADD_ROLE_ORDER_STEP, {
                        roles: [this.role.id],
                        orderSteps: this.orderSteps.map((f) => f.id),
                    });
                } else {
                    this.orderSteps.forEach((f) => {
                        store.commit(REMOVE_ROLE_ORDER_STEP, f);
                    });
                    store.dispatch(BULK_REMOVE_ROLE_ORDER_STEP, {
                        roles: [this.role.id],
                        orderSteps: this.orderSteps.map((f) => f.id),
                    });
                }
            },
        },
    },
};
</script>
