<template>
    <item class="hc-map-prospect-order" @click.stop="edit">
        <icon class="fa fa-shopping-cart" :size="28" :icon-size="9" />
        <div class="hc-item-main-content" v-text="order.name"></div>
        <tag
            v-if="status"
            :text="status.name"
            :color="status.color"
            :bgcolor="status.bgcolor"
        />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { OPEN_SLIDE } from "@/actions/slide";
import {
    SET_PROSPECT_ORDER,
    SET_PROSPECT_ORDER_TAB,
} from "@/actions/project/prospect/order";
import { SET_PROSPECT } from "@/actions/project/prospect";

export default {
    props: {
        order: {
            type: Object,
        },
        prospect: {
            type: Object,
        },
    },

    methods: {
        /**
         * Edit a prospect event
         */
        edit() {
            store.commit(SET_PROSPECT, this.prospect);
            store.commit(SET_PROSPECT_ORDER, this.order);

            // Open event edit slide
            store.commit(SET_PROSPECT_ORDER_TAB, 0);
            store.commit(OPEN_SLIDE, "prospect-manage-orders");
        },
    },

    computed: {
        ...mapGetters(["orderStatuses"]),

        total() {
            return 0;
        },

        /**
         *
         */
        status() {
            const status = this.orderStatuses.find(
                (status) => status.id == this.order.status_id
            );

            return status;
        },
    },
};
</script>
