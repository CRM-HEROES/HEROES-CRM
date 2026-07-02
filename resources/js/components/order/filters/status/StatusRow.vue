<template>
    <item tag="label">
        <icon class="fa fa-tag" :style="style" />
        <div class="hc-item-main-content" v-text="status.name"></div>
        <icon
            v-if="can('all.project.status.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent="edit"
        />
        <icon
            tag="a"
            class="fa fa-thumbs-down"
            :style="excludeStyle"
            :title="
                $t('order.table.filters.status.without_status', {
                    status: status.name,
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
import { SET_ORDER_STATUS } from "@/actions/project/order/status";

export default {
    props: {
        status: {
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
            store.commit(OPEN_MODAL, "status-update");
            store.commit(SET_ORDER_STATUS, this.status);
        },
    },

    computed: {
        ...mapGetters(["ordersParamExists", "can"]),

        /**
         *
         */
        withKey() {
            return "withStatuses";
        },

        /**
         *
         */
        withoutKey() {
            return "withoutStatuses";
        },

        /**
         *
         */
        value() {
            return this.status.id;
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
                color: this.status.bgcolor,
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
