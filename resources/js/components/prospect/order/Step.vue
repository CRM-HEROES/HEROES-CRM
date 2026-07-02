<template>
    <form class="hc-flex-column" style="height: 100%">
        <search v-model="orderStepKeyword" />
        <draggable
            tag="item-list"
            :list="orderStepKeyword ? filteredOrderSteps : currentOrderSteps"
            class="hc-flex-1"
            style="overflow-y: auto; overflow-x: hidden"
            item-key="id"
            :component-data="{ padding: '12px' }"
            :disabled="orderStepKeyword"
            @end="updateStepOrder"
        >
            <template #item="{ element }">
                <step-row :step="element" :value="isStepChecked(element)" />
            </template>
        </draggable>
        <buttons v-if="can('all.project.order.step.add')">
            <a @click.prevent="addStep" v-text="$t('add')"></a>
        </buttons>
    </form>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import {
    FETCH_ORDER_STEPS,
    UPDATE_ORDER_STEP,
} from "@/actions/project/order/step";
import { OPEN_MODAL } from "@/actions/modal";

import StepRow from "./Step/StepRow.vue";

export default {
    components: {
        StepRow,
    },

    data() {
        return {
            orderStepKeyword: "",
            currentOrderSteps: [],
        };
    },

    created() {
        this.currentOrderSteps = this.orderSteps;
    },

    methods: {
        /**
         *
         */
        addStep() {
            store.commit(OPEN_MODAL, "order-step-add");
        },

        /**
         *
         */
        fetchSteps() {
            store.dispatch(FETCH_ORDER_STEPS);
        },

        /**
         *
         * @param {*} step
         */
        isStepChecked(step) {
            return (
                this.prospectOrderSteps.findIndex((s) => s.id == step.id) >= 0
            );
        },

        /**
         *
         * @param {*} e
         */
        async updateStepOrder(e) {
            const step = this.currentOrderSteps[e.newDraggableIndex];

            try {
                await store.dispatch(UPDATE_ORDER_STEP, {
                    id: step.id,
                    order: e.newDraggableIndex,
                });
                this.fetchSteps();
            } finally {
            }
        },
    },

    watch: {
        orderSteps(newValue) {
            this.currentOrderSteps = newValue;
        },
    },

    computed: {
        ...mapGetters(["orderSteps", "prospectOrderSteps", "can"]),

        /**
         *
         */
        filteredOrderSteps() {
            const keyword = removeStringAccent(this.orderStepKeyword);

            return this.orderSteps.filter(
                (orderStep) =>
                    removeStringAccent(orderStep.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
