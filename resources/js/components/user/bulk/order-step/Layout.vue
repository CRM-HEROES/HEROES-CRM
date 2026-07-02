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
                :orderStep="orderStep"
            />
        </item-list>
        <buttons>
            <button @click.prevent="attach" :disabled="disabled">
                Assigner
            </button>
            <button
                @click.prevent="detach"
                :disabled="disabled"
                class="hc-button-danger"
            >
                Retirer
            </button>
            <a
                v-if="can('all.project.order.step.add')"
                @click.prevent="addOrderStep"
                v-text="$t('add')"
            ></a>
        </buttons>
        <loading :loading="bulking" />
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    BULK_ADD_USER_ORDER_STEP,
    BULK_REMOVE_USER_ORDER_STEP,
} from "@/actions/project/user/order-step";
import {
    FETCH_USERS,
    UPDATE_SELECTED_USERS,
    UPDATE_USER_BULK_ORDER_STEPS,
} from "@/actions/project/user";
import { OPEN_MODAL } from "@/actions/modal";
import { CLOSE_SLIDE } from "@/actions/slide";

// Components
import OrderStepRow from "./OrderStepRow.vue";

export default {
    components: {
        OrderStepRow,
    },

    data() {
        return {
            bulking: false,
            orderStepKeyword: "",
        };
    },

    methods: {
        /**
         *
         */
        addOrderStep() {
            store.commit(OPEN_MODAL, "order-step-add");
        },

        /**
         *
         */
        async attach() {
            this.bulking = true;
            const usersSelected = this.usersSelected;
            store.commit(UPDATE_SELECTED_USERS, []);

            try {
                await store.dispatch(BULK_ADD_USER_ORDER_STEP, {
                    users: usersSelected,
                    orderSteps: this.userBulkOrderSteps,
                });
                store.dispatch(FETCH_USERS);
            } finally {
                this.bulking = false;
                store.commit(UPDATE_USER_BULK_ORDER_STEPS, []);
                store.commit(CLOSE_SLIDE, "user-bulk-manage-order-steps");
            }
        },

        /**
         *
         */
        async detach() {
            this.bulking = true;
            const usersSelected = this.usersSelected;
            store.commit(UPDATE_SELECTED_USERS, []);

            try {
                await store.dispatch(BULK_REMOVE_USER_ORDER_STEP, {
                    users: usersSelected,
                    orderSteps: this.userBulkOrderSteps,
                });
                store.dispatch(FETCH_USERS);
            } finally {
                this.bulking = false;
                store.commit(UPDATE_USER_BULK_ORDER_STEPS, []);
                store.commit(CLOSE_SLIDE, "user-bulk-manage-order-steps");
            }
        },
    },

    computed: {
        ...mapGetters([
            "orderSteps",
            "usersSelected",
            "userBulkOrderSteps",
            "can",
        ]),

        /**
         *
         */
        disabled() {
            return this.userBulkOrderSteps.length == 0;
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
                for (let i in this.filteredOrderSteps) {
                    if (
                        !this.userBulkOrderSteps.find(
                            (orderStep) =>
                                orderStep == this.filteredOrderSteps[i].id
                        )
                    ) {
                        return false;
                    }
                }

                return true;
            },
            set: async function (value) {
                if (value) {
                    store.commit(
                        UPDATE_USER_BULK_ORDER_STEPS,
                        this.filteredOrderSteps.map((orderStep) => orderStep.id)
                    );
                } else {
                    store.commit(UPDATE_USER_BULK_ORDER_STEPS, []);
                }
            },
        },
    },
};
</script>
