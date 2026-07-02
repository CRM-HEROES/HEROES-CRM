<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="orderActionKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label" class="hc-users-table-filter-order-action">
                <icon class="fa fa-tag" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('user.table.filters.orderActions.with')"
                ></div>
                <icon
                    tag="a"
                    class="fa fa-thumbs-down"
                    :style="excludeOrderActionStyle"
                    :title="$t('user.table.filters.orderActions.without')"
                    @click.prevent="toggleExcludeOrderAction"
                />
                <checkbox
                    :model-value="isCheckedOrderAction"
                    @change="changeOrderAction"
                />
            </item>
            <order-action-row
                v-for="orderAction in filteredOrderActions"
                :key="orderAction.id"
                :orderAction="orderAction"
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
    REMOVE_USER_PARAMS,
    ADD_USER_PARAMS,
    FETCH_USERS,
} from "@/actions/project/user";
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
         */
        addOrderAction() {
            store.commit(OPEN_MODAL, "order-action-add");
        },

        changeOrderAction(event) {
            // Remove without param
            store.commit(REMOVE_USER_PARAMS, {
                key: this.withoutKeyOrderAction,
            });

            // Add or remove with param
            store.commit(
                event.target.checked ? ADD_USER_PARAMS : REMOVE_USER_PARAMS,
                {
                    key: this.withKeyOrderAction,
                }
            );
            store.dispatch(FETCH_USERS);
        },

        toggleExcludeOrderAction() {
            // Add or remove with param
            store.commit(
                this.isExcludedOrderAction
                    ? ADD_USER_PARAMS
                    : REMOVE_USER_PARAMS,
                {
                    key: this.withKeyOrderAction,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcludedOrderAction
                    ? REMOVE_USER_PARAMS
                    : ADD_USER_PARAMS,
                {
                    key: this.withoutKeyOrderAction,
                }
            );

            store.dispatch(FETCH_USERS);
        },
    },

    computed: {
        ...mapGetters(["orderActions", "user", "usersParamValue", "can"]),

        /**
         *
         */
        withKeyOrderAction() {
            return "withOrderActions";
        },

        /**
         *
         */
        withoutKeyOrderAction() {
            return "withoutOrderActions";
        },

        /**
         *
         */
        isCheckedOrderAction() {
            return (
                this.usersParamValue(this.withKeyOrderAction) === "" ||
                this.usersParamValue(this.withoutKeyOrderAction) === ""
            );
        },

        /**
         *
         */
        isExcludedOrderAction() {
            return this.usersParamValue(this.withoutKeyOrderAction) === "";
        },

        /**
         *
         */
        excludeOrderActionStyle() {
            return {
                color: this.isExcludedOrderAction ? "#CC0000" : "#CCCCCC",
            };
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
    },
};
</script>
