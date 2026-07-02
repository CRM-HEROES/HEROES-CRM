<template>
    <item tag="label">
        <icon class="fa fa-step-forward" :style="style" />
        <div class="hc-item-main-content" v-text="orderStep.name"></div>
        <icon
            v-if="can('all.project.order.step.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
        <checkbox :model-value="value" :disabled="disabled" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    ADD_ROLE_ORDER_STEP,
    REMOVE_ROLE_ORDER_STEP,
} from "@/actions/project/role/order-step";
import { OPEN_MODAL } from "@/actions/modal";
import { SET_ORDER_STEP } from "@/actions/project/order/step";

export default {
    props: {
        value: {
            type: Boolean,
            default: false,
        },

        disabled: {
            type: Boolean,
        },

        orderStep: {
            type: Object,
        },
    },

    data() {
        return {
            currentValue: this.value,
        };
    },

    methods: {
        change(event) {
            store.dispatch(
                event.target.checked
                    ? ADD_ROLE_ORDER_STEP
                    : REMOVE_ROLE_ORDER_STEP,
                this.orderStep
            );
            this.$emit("change", event);
        },

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
