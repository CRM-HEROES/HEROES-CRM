<template>
    <item @click.prevent.stop="edit">
        <icon class="fa fa-person-digging" :style="style" :size="30" />
        <div class="hc-item-main-content" v-text="orderAction.name"></div>
        <icon tag="a" class="fa fa-cog" />
    </item>
</template>

<script>
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_ORDER_ACTION } from "@/actions/project/order/action";

export default {
    props: {
        orderAction: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "order-action-update");
            store.commit(SET_ORDER_ACTION, this.orderAction);
        },
    },

    computed: {
        /**
         *
         */
        style() {
            return {
                color: this.orderAction.bgcolor,
            };
        },
    },
};
</script>
