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
        <checkbox v-model="selected" :value="orderStep.id" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_ORDER_STEP } from "@/actions/project/order/step";
import { UPDATE_USER_BULK_ORDER_STEPS } from "@/actions/project/user";

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
        ...mapGetters(["userBulkOrderSteps", "can"]),

        /**
         *
         */
        style() {
            return {
                color: this.orderStep.bgcolor,
            };
        },

        selected: {
            get() {
                return this.userBulkOrderSteps;
            },
            set(value) {
                store.commit(UPDATE_USER_BULK_ORDER_STEPS, value);
            },
        },
    },
};
</script>
