<template>
    <slide
        name="orders-table-filter-action"
        :title="$t('order.table.filters.actions.title')"
        icon="fa fa-tags"
        style="width: 260px"
    >
        <div class="hc-flex-column" style="height: 100%">
            <search v-model="actionKeyword" />
            <item-list class="hc-flex-1" padding="5px">
                <item tag="label" class="hc-orders-table-filter-action">
                    <icon class="fa fa-filter" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('order.table.filters.actions.with')"
                    ></div>
                    <icon
                        tag="a"
                        class="fa fa-thumbs-down"
                        :style="excludeActionStyle"
                        :title="$t('order.table.filters.actions.without')"
                        @click.prevent="toggleExcludeAction"
                    />
                    <checkbox
                        :model-value="isCheckedAction"
                        @change="changeAction"
                    />
                </item>
                <action-row
                    v-for="action in filteredActions"
                    :key="action.id"
                    :action="action"
                />
            </item-list>
            <buttons v-if="can('all.project.action.add')">
                <a @click.prevent="addAction" v-text="$t('add')"></a>
            </buttons>
        </div>
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    REMOVE_ORDER_PARAMS,
    ADD_ORDER_PARAMS,
    FETCH_ORDERS,
} from "@/actions/project/order";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import ActionRow from "./ActionRow.vue";

export default {
    components: {
        ActionRow,
    },

    data() {
        return {
            tab: 0,
            actionKeyword: "",
        };
    },

    methods: {
        /**
         *
         */
        addAction() {
            store.commit(OPEN_MODAL, "order-action-add");
        },

        changeAction(event) {
            // Remove without param
            store.commit(REMOVE_ORDER_PARAMS, {
                key: this.withoutKeyAction,
            });

            // Add or remove with param
            store.commit(
                event.target.checked ? ADD_ORDER_PARAMS : REMOVE_ORDER_PARAMS,
                {
                    key: this.withKeyAction,
                }
            );
            store.dispatch(FETCH_ORDERS);
        },

        toggleExcludeAction() {
            // Add or remove with param
            store.commit(
                this.isExcludedAction ? ADD_ORDER_PARAMS : REMOVE_ORDER_PARAMS,
                {
                    key: this.withKeyAction,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcludedAction ? REMOVE_ORDER_PARAMS : ADD_ORDER_PARAMS,
                {
                    key: this.withoutKeyAction,
                }
            );

            store.dispatch(FETCH_ORDERS);
        },
    },

    computed: {
        ...mapGetters(["orderActions", "ordersParamValue", "can"]),

        /**
         *
         */
        withKeyAction() {
            return "withActions";
        },

        /**
         *
         */
        withoutKeyAction() {
            return "withoutActions";
        },

        /**
         *
         */
        isCheckedAction() {
            return (
                this.ordersParamValue(this.withKeyAction) === "" ||
                this.ordersParamValue(this.withoutKeyAction) === ""
            );
        },

        /**
         *
         */
        isExcludedAction() {
            return this.ordersParamValue(this.withoutKeyAction) === "";
        },

        /**
         *
         */
        excludeActionStyle() {
            return {
                color: this.isExcludedAction ? "#CC0000" : "#CCCCCC",
            };
        },

        /**
         *
         */
        filteredActions() {
            const keyword = removeStringAccent(this.actionKeyword);

            return this.orderActions.filter(
                (action) =>
                    removeStringAccent(action.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
./ActionRow.vue
