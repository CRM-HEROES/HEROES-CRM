<template>
    <item @click.prevent.stop="edit">
        <icon class="fa fa-step-forward" :style="style" :size="30" />
        <div class="hc-item-main-content" v-text="orderStep.name"></div>
        <icon tag="a" class="fa fa-cog" />
    </item>
</template>

<script>
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_ORDER_STEP } from "@/actions/project/order/step";

export default {
    props: {
        orderStep: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "order-step-update");
            store.commit(SET_ORDER_STEP, this.orderStep);
        },
    },

    computed: {
        /**
         *
         */
        style() {
            return {
                color: this.orderStep.bgcolor,
            };
        },
    },
};
</script>
