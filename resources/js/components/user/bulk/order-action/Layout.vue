<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="orderActionKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label">
                <icon class="fa fa-check-square" />
                <div class="hc-item-main-content" v-text="$t('all')"></div>
                <checkbox v-model="all" />
            </item>
            <order-action-row
                v-for="orderAction in filteredOrderActions"
                :key="orderAction.id"
                :orderAction="orderAction"
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
                v-if="can('all.project.order.action.add')"
                @click.prevent="addOrderAction"
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
    BULK_ADD_USER_ORDER_ACTION,
    BULK_REMOVE_USER_ORDER_ACTION,
} from "@/actions/project/user/order-action";
import {
    FETCH_USERS,
    UPDATE_SELECTED_USERS,
    UPDATE_USER_BULK_ORDER_ACTIONS,
} from "@/actions/project/user";
import { OPEN_MODAL } from "@/actions/modal";
import { CLOSE_SLIDE } from "@/actions/slide";

// Components
import OrderActionRow from "./OrderActionRow.vue";

export default {
    components: {
        OrderActionRow,
    },

    data() {
        return {
            bulking: false,
            orderActionKeyword: "",
        };
    },

    methods: {
        /**
         *
         */
        addOrderAction() {
            store.commit(OPEN_MODAL, "order-action-add");
        },

        /**
         *
         */
        async attach() {
            this.bulking = true;
            const usersSelected = this.usersSelected;
            store.commit(UPDATE_SELECTED_USERS, []);

            try {
                await store.dispatch(BULK_ADD_USER_ORDER_ACTION, {
                    users: usersSelected,
                    orderActions: this.userBulkOrderActions,
                });
                store.dispatch(FETCH_USERS);
            } finally {
                this.bulking = false;
                store.commit(UPDATE_USER_BULK_ORDER_ACTIONS, []);
                store.commit(CLOSE_SLIDE, "user-bulk-manage-order-actions");
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
                await store.dispatch(BULK_REMOVE_USER_ORDER_ACTION, {
                    users: usersSelected,
                    orderActions: this.userBulkOrderActions,
                });
                store.dispatch(FETCH_USERS);
            } finally {
                this.bulking = false;
                store.commit(UPDATE_USER_BULK_ORDER_ACTIONS, []);
                store.commit(CLOSE_SLIDE, "user-bulk-manage-order-actions");
            }
        },
    },

    computed: {
        ...mapGetters([
            "orderActions",
            "usersSelected",
            "userBulkOrderActions",
            "can",
        ]),

        /**
         *
         */
        disabled() {
            return this.userBulkOrderActions.length == 0;
        },

        /**
         *
         */
        filteredOrderActions() {
            const keyword = removeStringAccent(this.orderActionKeyword);

            return this.orderActions.filter(
                (orderAction) =>
                    removeStringAccent(orderAction.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        all: {
            get: function () {
                for (let i in this.filteredOrderActions) {
                    if (
                        !this.userBulkOrderActions.find(
                            (orderAction) =>
                                orderAction == this.filteredOrderActions[i].id
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
                        UPDATE_USER_BULK_ORDER_ACTIONS,
                        this.filteredOrderActions.map(
                            (orderAction) => orderAction.id
                        )
                    );
                } else {
                    store.commit(UPDATE_USER_BULK_ORDER_ACTIONS, []);
                }
            },
        },
    },
};
</script>
