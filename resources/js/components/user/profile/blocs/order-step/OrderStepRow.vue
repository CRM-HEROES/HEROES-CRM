<template>
    <item @click.prevent.stop="edit">
        <icon class="fa fa-step-forward" :style="style" :size="30" />
        <div class="hc-item-main-content" v-text="orderStep.name"></div>
        <icon
            v-if="can('all.project.order.step.update')"
            tag="a"
            class="fa fa-cog"
            :size="28"
            :icon-size="9"
        />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
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
        ...mapGetters(["can"]),

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
