<template>
    <form class="hc-flex-column" style="height: 100%">
        <search v-model="orderStatusKeyword" />
        <draggable
            tag="item-list"
            :list="orderStatusKeyword ? filteredOrderStatuses : orderStatuses"
            class="hc-flex-1"
            style="overflow-y: auto; overflow-x: hidden"
            item-key="id"
            :component-data="{ padding: '12px' }"
            :disabled="orderStatusKeyword"
            @end="updateStatusOrder"
        >
            <template #item="{ element }">
                <status-row
                    :status="element"
                    :value="isStatusChecked(element)"
                />
            </template>
        </draggable>
        <buttons v-if="can('all.project.order.status.add')">
            <a @click.prevent="addStatus" v-text="$t('add')"></a>
        </buttons>
    </form>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import {
    FETCH_ORDER_STATUSES,
    UPDATE_ORDER_STATUS,
} from "@/actions/project/order/status";
import { OPEN_MODAL } from "@/actions/modal";

import StatusRow from "./Status/StatusRow.vue";

export default {
    components: {
        StatusRow,
    },

    data() {
        return {
            orderStatusKeyword: "",
            currentOrderStatuses: [],
        };
    },

    methods: {
        /**
         *
         */
        addStatus() {
            store.commit(OPEN_MODAL, "order-status-add");
        },

        /**
         *
         */
        fetchStatuses() {
            store.dispatch(FETCH_ORDER_STATUSES);
        },

        /**
         *
         * @param {*} status
         */
        isStatusChecked(status) {
            return this.prospectOrder.status_id == status.id;
        },

        /**
         *
         * @param {*} e
         */
        async updateStatusOrder(e) {
            const status = this.currentOrderStatuses[e.newDraggableIndex];

            try {
                await store.dispatch(UPDATE_ORDER_STATUS, {
                    id: status.id,
                    order: e.newDraggableIndex,
                });
                this.fetchStatuses();
            } finally {
            }
        },
    },

    watch: {
        orderStatuses(newValue) {
            this.currentOrderStatuses = newValue;
        },
    },

    computed: {
        ...mapGetters(["orderStatuses", "prospectOrder", "can"]),

        /**
         *
         */
        filteredOrderStatuses() {
            const keyword = removeStringAccent(this.orderStatusKeyword);

            return this.currentOrderStatuses.filter(
                (orderStatus) =>
                    removeStringAccent(orderStatus.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
