<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="orderActionKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label">
                <icon class="fa fa-check-square" />
                <div class="hc-item-main-content" v-text="$t('all')"></div>
                <checkbox v-model="all" @change="change" :disabled="canAll" />
            </item>
            <order-action-row
                v-for="orderAction in filteredOrderActions"
                :key="orderAction.id"
                :order-action="orderAction"
                :value="isOrderActionChecked(orderAction)"
                :disabled="canAll"
            />
        </item-list>
        <buttons v-if="can('all.project.order.action.add')">
            <a @click.prevent="addOrderAction" v-text="$t('add')"></a>
        </buttons>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    BULK_ADD_ROLE_ORDER_ACTION,
    BULK_REMOVE_ROLE_ORDER_ACTION,
    ADD_ROLE_ORDER_ACTION,
    REMOVE_ROLE_ORDER_ACTION,
} from "@/actions/project/role/order-action";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import OrderActionRow from "./OrderActionRow.vue";

export default {
    components: {
        OrderActionRow,
    },

    data() {
        return {
            tab: 0,
            orderActionKeyword: "",
        };
    },

    methods: {
        /**
         *
         * @param {*} orderAction
         */
        isOrderActionChecked(orderAction) {
            return (
                this.canAll ||
                this.roleOrderActions.findIndex(
                    (l) => l.id == orderAction.id
                ) >= 0
            );
        },

        /**
         *
         */
        addOrderAction() {
            store.commit(OPEN_MODAL, "order-action-add");
        },
    },

    computed: {
        ...mapGetters([
            "orderActions",
            "role",
            "roleOrderActions",
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
                return (
                    this.canAll ||
                    this.roleOrderActions.length == this.orderActions.length
                );
            },
            set: async function (value) {
                if (value) {
                    this.orderActions.forEach((f) => {
                        store.commit(ADD_ROLE_ORDER_ACTION, f);
                    });
                    store.dispatch(BULK_ADD_ROLE_ORDER_ACTION, {
                        roles: [this.role.id],
                        orderActions: this.orderActions.map((f) => f.id),
                    });
                } else {
                    this.orderActions.forEach((f) => {
                        store.commit(REMOVE_ROLE_ORDER_ACTION, f);
                    });
                    store.dispatch(BULK_REMOVE_ROLE_ORDER_ACTION, {
                        roles: [this.role.id],
                        orderActions: this.orderActions.map((f) => f.id),
                    });
                }
            },
        },
    },
};
</script>
