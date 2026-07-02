<template>
    <item tag="label">
        <icon class="fa fa-tag" :style="style" />
        <div class="hc-item-main-content" v-text="step.name"></div>
        <icon
            v-if="can('all.project.step.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent="edit"
        />
        <icon
            tag="a"
            class="fa fa-thumbs-down"
            :style="excludeStyle"
            :title="
                $t('order.table.filters.step.without_step', {
                    step: step.name,
                })
            "
            @click.prevent="toggleExclude"
        />
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    FETCH_ORDERS,
    ADD_ORDER_PARAMS,
    REMOVE_ORDER_PARAMS,
} from "@/actions/project/order";
import { OPEN_MODAL } from "@/actions/modal";
import { SET_ORDER_STEP } from "@/actions/project/order/step";

export default {
    props: {
        step: {
            type: Object,
        },
    },

    methods: {
        change(event) {
            // Remove without param
            store.commit(REMOVE_ORDER_PARAMS, {
                key: this.withoutKey,
                value: this.value,
                multiple: true,
            });

            // Add or remove with param
            store.commit(
                event.target.checked ? ADD_ORDER_PARAMS : REMOVE_ORDER_PARAMS,
                {
                    key: this.withKey,
                    value: this.value,
                    multiple: true,
                }
            );
            store.dispatch(FETCH_ORDERS);
            this.$emit("change", event);
        },

        toggleExclude() {
            // Add or remove with param
            store.commit(
                this.isExcluded ? ADD_ORDER_PARAMS : REMOVE_ORDER_PARAMS,
                {
                    key: this.withKey,
                    value: this.value,
                    multiple: true,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcluded ? REMOVE_ORDER_PARAMS : ADD_ORDER_PARAMS,
                {
                    key: this.withoutKey,
                    value: this.value,
                    multiple: true,
                }
            );

            store.dispatch(FETCH_ORDERS);
        },

        edit() {
            store.commit(OPEN_MODAL, "step-update");
            store.commit(SET_ORDER_STEP, this.step);
        },
    },

    computed: {
        ...mapGetters(["ordersParamExists", "can"]),

        /**
         *
         */
        withKey() {
            return "withSteps";
        },

        /**
         *
         */
        withoutKey() {
            return "withoutSteps";
        },

        /**
         *
         */
        value() {
            return this.step.id;
        },

        /**
         *
         */
        isExcluded() {
            return this.ordersParamExists(this.withoutKey, this.value);
        },

        /**
         *
         */
        isChecked() {
            return (
                this.ordersParamExists(this.withKey, this.value) ||
                this.ordersParamExists(this.withoutKey, this.value)
            );
        },

        /**
         *
         */
        style() {
            return {
                color: this.step.bgcolor,
            };
        },

        /**
         *
         */
        excludeStyle() {
            return {
                color: this.isExcluded ? "#CC0000" : "#CCCCCC",
            };
        },
    },
};
</script>
