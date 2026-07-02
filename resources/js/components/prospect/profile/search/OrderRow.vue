<template>
    <item tag="a" v-tooltip="order.name" @click.prevent="action">
        <icon class="fa fa-shopping-cart" :style="style" :size="30" />
        <div class="hc-item-main-content" v-text="order.name"></div>
    </item>
</template>

<script>
import store from "@/store";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";
import {
    SET_PROSPECT_ORDER,
    SET_PROSPECT_ORDER_TAB,
} from "@/actions/project/prospect/order";

export default {
    props: {
        order: {
            type: Object,
        },
    },

    methods: {
        action() {
            store.commit(SET_PROSPECT_ORDER, this.order);

            // Open event edit slide
            store.commit(SET_PROSPECT_ORDER_TAB, 0);
            store.commit(OPEN_SLIDE, "prospect-manage-orders");
        },
    },

    computed: {
        /**
         *
         */
        style() {
            return {
                color: this.order.status
                    ? this.order.status.bgcolor
                    : "#DDDDDD",
            };
        },
    },
};
</script>
