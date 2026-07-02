<template>
    <slide
        name="orders-table-filter-step"
        :title="$t('order.table.filters.steps.title')"
        icon="fa fa-tags"
        style="width: 260px"
    >
        <div class="hc-flex-column" style="height: 100%">
            <search v-model="stepKeyword" />
            <item-list class="hc-flex-1" padding="5px">
                <item tag="label" class="hc-orders-table-filter-step">
                    <icon class="fa fa-filter" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('order.table.filters.steps.with')"
                    ></div>
                    <icon
                        tag="a"
                        class="fa fa-thumbs-down"
                        :style="excludeStepStyle"
                        :title="$t('order.table.filters.steps.without')"
                        @click.prevent="toggleExcludeStep"
                    />
                    <checkbox
                        :model-value="isCheckedStep"
                        @change="changeStep"
                    />
                </item>
                <step-row
                    v-for="step in filteredSteps"
                    :key="step.id"
                    :step="step"
                />
            </item-list>
            <buttons v-if="can('all.project.step.add')">
                <a @click.prevent="addStep" v-text="$t('add')"></a>
            </buttons>
        </div>
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    REMOVE_ORDER_PARAMS,
    ADD_ORDER_PARAMS,
    FETCH_ORDERS,
} from "@/actions/project/order";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import StepRow from "./StepRow.vue";

export default {
    components: {
        StepRow,
    },

    data() {
        return {
            tab: 0,
            stepKeyword: "",
        };
    },

    methods: {
        /**
         *
         */
        addStep() {
            store.commit(OPEN_MODAL, "order-step-add");
        },

        changeStep(event) {
            // Remove without param
            store.commit(REMOVE_ORDER_PARAMS, {
                key: this.withoutKeyStep,
            });

            // Add or remove with param
            store.commit(
                event.target.checked ? ADD_ORDER_PARAMS : REMOVE_ORDER_PARAMS,
                {
                    key: this.withKeyStep,
                }
            );
            store.dispatch(FETCH_ORDERS);
        },

        toggleExcludeStep() {
            // Add or remove with param
            store.commit(
                this.isExcludedStep ? ADD_ORDER_PARAMS : REMOVE_ORDER_PARAMS,
                {
                    key: this.withKeyStep,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcludedStep ? REMOVE_ORDER_PARAMS : ADD_ORDER_PARAMS,
                {
                    key: this.withoutKeyStep,
                }
            );

            store.dispatch(FETCH_ORDERS);
        },
    },

    computed: {
        ...mapGetters(["orderSteps", "ordersParamValue", "can"]),

        /**
         *
         */
        withKeyStep() {
            return "withSteps";
        },

        /**
         *
         */
        withoutKeyStep() {
            return "withoutSteps";
        },

        /**
         *
         */
        isCheckedStep() {
            return (
                this.ordersParamValue(this.withKeyStep) === "" ||
                this.ordersParamValue(this.withoutKeyStep) === ""
            );
        },

        /**
         *
         */
        isExcludedStep() {
            return this.ordersParamValue(this.withoutKeyStep) === "";
        },

        /**
         *
         */
        excludeStepStyle() {
            return {
                color: this.isExcludedStep ? "#CC0000" : "#CCCCCC",
            };
        },

        /**
         *
         */
        filteredSteps() {
            const keyword = removeStringAccent(this.stepKeyword);

            return this.orderSteps.filter(
                (step) => removeStringAccent(step.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
