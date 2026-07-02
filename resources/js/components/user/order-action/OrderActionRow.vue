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
        <checkbox :model-value="value" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    ADD_USER_ORDER_ACTION,
    REMOVE_USER_ORDER_ACTION,
} from "@/actions/project/user/order-action";
import { OPEN_MODAL } from "@/actions/modal";
import { SET_ORDER_ACTION } from "@/actions/project/order/action";

export default {
    props: {
        value: {
            type: Boolean,
            default: false,
        },

        orderAction: {
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
                    ? ADD_USER_ORDER_ACTION
                    : REMOVE_USER_ORDER_ACTION,
                this.orderAction
            );
            this.$emit("change", event);
        },

        edit() {
            store.commit(OPEN_MODAL, "order-action-update");
            store.commit(SET_ORDER_ACTION, this.orderAction);
        },
    },

    computed: {
        ...mapGetters(["can"]),

        /**
         *
         */
        style() {
            return {
                color: this.orderAction.bgcolor,
            };
        },
    },
};
</script>
