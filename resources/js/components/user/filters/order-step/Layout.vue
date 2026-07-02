<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="orderStepKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label" class="hc-users-table-filter-order-step">
                <icon class="fa fa-tag" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('user.table.filters.orderSteps.with')"
                ></div>
                <icon
                    tag="a"
                    class="fa fa-thumbs-down"
                    :style="excludeOrderStepStyle"
                    :title="$t('user.table.filters.orderSteps.without')"
                    @click.prevent="toggleExcludeOrderStep"
                />
                <checkbox
                    :model-value="isCheckedOrderStep"
                    @change="changeOrderStep"
                />
            </item>
            <order-step-row
                v-for="orderStep in filteredOrderSteps"
                :key="orderStep.id"
                :orderStep="orderStep"
            />
        </item-list>
        <buttons v-if="can('all.project.order.step.add')">
            <a @click.prevent="addOrderStep" v-text="$t('add')"></a>
        </buttons>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    REMOVE_USER_PARAMS,
    ADD_USER_PARAMS,
    FETCH_USERS,
} from "@/actions/project/user";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import OrderStepRow from "./OrderStepRow.vue";

export default {
    components: {
        OrderStepRow,
    },

    data() {
        return {
            tab: 0,
            orderStepKeyword: "",
        };
    },

    methods: {
        /**
         *
         */
        addOrderStep() {
            store.commit(OPEN_MODAL, "order-step-add");
        },

        changeOrderStep(event) {
            // Remove without param
            store.commit(REMOVE_USER_PARAMS, {
                key: this.withoutKeyOrderStep,
            });

            // Add or remove with param
            store.commit(
                event.target.checked ? ADD_USER_PARAMS : REMOVE_USER_PARAMS,
                {
                    key: this.withKeyOrderStep,
                }
            );
            store.dispatch(FETCH_USERS);
        },

        toggleExcludeOrderStep() {
            // Add or remove with param
            store.commit(
                this.isExcludedOrderStep ? ADD_USER_PARAMS : REMOVE_USER_PARAMS,
                {
                    key: this.withKeyOrderStep,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcludedOrderStep ? REMOVE_USER_PARAMS : ADD_USER_PARAMS,
                {
                    key: this.withoutKeyOrderStep,
                }
            );

            store.dispatch(FETCH_USERS);
        },
    },

    computed: {
        ...mapGetters(["orderSteps", "user", "usersParamValue", "can"]),

        /**
         *
         */
        withKeyOrderStep() {
            return "withOrderSteps";
        },

        /**
         *
         */
        withoutKeyOrderStep() {
            return "withoutOrderSteps";
        },

        /**
         *
         */
        isCheckedOrderStep() {
            return (
                this.usersParamValue(this.withKeyOrderStep) === "" ||
                this.usersParamValue(this.withoutKeyOrderStep) === ""
            );
        },

        /**
         *
         */
        isExcludedOrderStep() {
            return this.usersParamValue(this.withoutKeyOrderStep) === "";
        },

        /**
         *
         */
        excludeOrderStepStyle() {
            return {
                color: this.isExcludedOrderStep ? "#CC0000" : "#CCCCCC",
            };
        },

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
