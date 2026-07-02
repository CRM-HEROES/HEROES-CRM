<template>
    <slide
        name="orders-table-filter-status"
        :title="$t('order.table.filters.statuses.title')"
        icon="fa fa-tags"
        style="width: 260px"
    >
        <div class="hc-flex-column" style="height: 100%">
            <search v-model="statusKeyword" />
            <item-list class="hc-flex-1" padding="5px">
                <item tag="label" class="hc-orders-table-filter-status">
                    <icon class="fa fa-filter" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('order.table.filters.statuses.with')"
                    ></div>
                    <icon
                        tag="a"
                        class="fa fa-thumbs-down"
                        :style="excludeStatusStyle"
                        :title="$t('order.table.filters.statuses.without')"
                        @click.prevent="toggleExcludeStatus"
                    />
                    <checkbox
                        :model-value="isCheckedStatus"
                        @change="changeStatus"
                    />
                </item>
                <status-row
                    v-for="status in filteredStatuses"
                    :key="status.id"
                    :status="status"
                />
            </item-list>
            <buttons v-if="can('all.project.status.add')">
                <a @click.prevent="addStatus" v-text="$t('add')"></a>
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
import StatusRow from "./StatusRow.vue";

export default {
    components: {
        StatusRow,
    },

    data() {
        return {
            tab: 0,
            statusKeyword: "",
        };
    },

    methods: {
        /**
         *
         */
        addStatus() {
            store.commit(OPEN_MODAL, "order-status-add");
        },

        changeStatus(event) {
            // Remove without param
            store.commit(REMOVE_ORDER_PARAMS, {
                key: this.withoutKeyStatus,
            });

            // Add or remove with param
            store.commit(
                event.target.checked ? ADD_ORDER_PARAMS : REMOVE_ORDER_PARAMS,
                {
                    key: this.withKeyStatus,
                }
            );
            store.dispatch(FETCH_ORDERS);
        },

        toggleExcludeStatus() {
            // Add or remove with param
            store.commit(
                this.isExcludedStatus ? ADD_ORDER_PARAMS : REMOVE_ORDER_PARAMS,
                {
                    key: this.withKeyStatus,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcludedStatus ? REMOVE_ORDER_PARAMS : ADD_ORDER_PARAMS,
                {
                    key: this.withoutKeyStatus,
                }
            );

            store.dispatch(FETCH_ORDERS);
        },
    },

    computed: {
        ...mapGetters(["orderStatuses", "ordersParamValue", "can"]),

        /**
         *
         */
        withKeyStatus() {
            return "withStatuses";
        },

        /**
         *
         */
        withoutKeyStatus() {
            return "withoutStatuses";
        },

        /**
         *
         */
        isCheckedStatus() {
            return (
                this.ordersParamValue(this.withKeyStatus) === "" ||
                this.ordersParamValue(this.withoutKeyStatus) === ""
            );
        },

        /**
         *
         */
        isExcludedStatus() {
            return this.ordersParamValue(this.withoutKeyStatus) === "";
        },

        /**
         *
         */
        excludeStatusStyle() {
            return {
                color: this.isExcludedStatus ? "#CC0000" : "#CCCCCC",
            };
        },

        /**
         *
         */
        filteredStatuses() {
            const keyword = removeStringAccent(this.statusKeyword);

            return this.orderStatuses.filter(
                (status) =>
                    removeStringAccent(status.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
./StatusRow.vue
