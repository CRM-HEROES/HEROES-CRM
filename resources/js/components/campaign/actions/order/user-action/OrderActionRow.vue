<template>
    <item class="hc-campaign-rule-order-action-row">
        <icon
            class="fa fa-person-digging"
            icon-size="11"
            :size="30"
            :style="style"
        />
        <div class="hc-item-main-content" v-text="orderAction.name"></div>
        <icon
            v-if="can('all.project.order.action.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            :size="30"
            @mousedown.stop
            @click.prevent.stop="edit"
        />
    </item>
</template>

<style>
.hc-campaign-rule-orderAction-row .hc-item-main-content {
    font-size: 11px;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_ORDER_ACTION } from "@/actions/project/order/action";

export default {
    props: {
        orderAction: {
            type: Object,
        },
    },

    methods: {
        ...mapGetters(["can"]),

        edit() {
            store.commit(OPEN_MODAL, "order-action-update");
            store.commit(SET_ORDER_ACTION, this.orderAction);
        },
    },

    computed: {
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
