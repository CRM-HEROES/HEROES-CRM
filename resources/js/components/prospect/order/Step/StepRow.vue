<template>
    <item tag="label">
        <icon class="fa fa-step-forward" :style="style" />
        <div class="hc-item-main-content" v-text="step.name"></div>
        <icon
            v-if="can('all.project.order.step.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
        <checkbox :model-value="value" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";

import store from "@/store";
import { OPEN_MODAL } from "@/actions/modal";
import { SET_ORDER_STEP } from "@/actions/project/order/step";
import {
    ADD_PROSPECT_ORDER_STEP,
    REMOVE_PROSPECT_ORDER_STEP,
} from "@/actions/project/prospect/order/step";

export default {
    props: {
        step: {
            type: Object,
        },

        value: {
            type: Boolean,
            default: false,
        },
    },

    methods: {
        change(event) {
            store.dispatch(
                event.target.checked
                    ? ADD_PROSPECT_ORDER_STEP
                    : REMOVE_PROSPECT_ORDER_STEP,
                this.step
            );
            this.$emit("change", event);
        },

        edit() {
            store.commit(OPEN_MODAL, "order-step-update");
            store.commit(SET_ORDER_STEP, this.step);
        },
    },

    computed: {
        ...mapGetters(["can"]),

        /**
         *
         */
        style() {
            return {
                color: this.step.bgcolor,
            };
        },
    },
};
</script>
