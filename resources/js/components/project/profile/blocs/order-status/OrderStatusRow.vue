<template>
    <item @click.prevent.stop="edit">
        <icon class="fa fa-tags" :style="style" :size="30" />
        <div class="hc-item-main-content" v-text="orderStatus.name"></div>
        <icon tag="a" class="fa fa-cog" />
    </item>
</template>

<script>
import store from "@/store";

// Statuses
import { OPEN_MODAL } from "@/actions/modal";
import { SET_ORDER_STATUS } from "@/actions/project/order/status";

export default {
    props: {
        orderStatus: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "order-status-update");
            store.commit(SET_ORDER_STATUS, this.orderStatus);
        },
    },

    computed: {
        /**
         *
         */
        style() {
            return {
                color: this.orderStatus.bgcolor,
            };
        },
    },
};
</script>
