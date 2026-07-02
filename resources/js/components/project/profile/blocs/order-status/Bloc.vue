<template>
    <bloc icon="fa fa-tags" :name="$t('project.profile.blocs.order_statuses')">
        <template #options>
            <icon
                tag="a"
                class="fa fa-plus"
                @click.prevent.stop="addOrderStatus"
            />
            <icon v-if="orderStatuses.length > 0" class="fa fa-caret-down" />
        </template>
        <template #body>
            <div
                style="padding: 10px 10px; float: left; width: 100%"
                v-if="orderStatuses.length > 0"
            >
                <order-status-row
                    v-for="(orderStatus, i) in orderStatuses"
                    :key="orderStatus.id"
                    :orderStatus="orderStatus"
                />
            </div>
        </template>
    </bloc>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Statuses
import { OPEN_MODAL } from "@/actions/modal";

// Components
import Bloc from "@/components/project/profile/blocs/Bloc.vue";
import OrderStatusRow from "./OrderStatusRow.vue";

export default {
    components: {
        Bloc,
        OrderStatusRow,
    },

    methods: {
        /**
         * Add orderStatus
         * See: @/components/orderStatus/add/Modal.vue
         */
        addOrderStatus() {
            store.commit(OPEN_MODAL, "order-status-add");
        },
    },

    computed: {
        ...mapGetters(["project", "orderStatuses"]),
    },
};
</script>
