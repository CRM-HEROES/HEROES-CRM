<template>
    <item tag="label">
        <icon class="fa fa-person-digging" :style="style" />
        <div class="hc-item-main-content" v-text="orderAction.name"></div>
        <icon
            v-if="can('all.project.order.action.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
        <checkbox v-model="selected" :value="orderAction.id" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_ORDER_ACTION } from "@/actions/project/order/action";
import { UPDATE_USER_BULK_ORDER_ACTIONS } from "@/actions/project/user";

export default {
    props: {
        orderAction: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "order-action-update");
            store.commit(SET_ORDER_ACTION, this.orderAction);
        },
    },

    computed: {
        ...mapGetters(["userBulkOrderActions", "can"]),

        /**
         *
         */
        style() {
            return {
                color: this.orderAction.bgcolor,
            };
        },

        selected: {
            get() {
                return this.userBulkOrderActions;
            },
            set(value) {
                store.commit(UPDATE_USER_BULK_ORDER_ACTIONS, value);
            },
        },
    },
};
</script>
