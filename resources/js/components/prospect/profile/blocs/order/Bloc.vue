<template>
    <bloc
        icon="fa fa-shopping-cart icon-cyan"
        :name="$t('prospect.profile.bloc.orders')"
    >
        <template #options>
            <icon
                tag="a"
                class="fa fa-plus"
                @click.prevent.stop="manageOrders"
            />
        </template>
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
                <order-row
                    v-for="(order, i) in prospectOrders"
                    :key="order.id"
                    :order="order"
                    :prospect="prospect"
                />
            </div>
        </template>
    </bloc>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";
import { SET_PROSPECT_ORDER_TAB } from "@/actions/project/prospect/order";
import { SET_PROSPECT } from "@/actions/project/prospect";

// Components
import Bloc from "@/components/prospect/profile/blocs/Bloc.vue";
import OrderRow from "./OrderRow.vue";

export default {
    components: {
        Bloc,
        OrderRow,
    },

    data() {
        return {
            shownOrdersCount: 5,
        };
    },

    methods: {
        showMoreOrders() {
            this.shownOrdersCount += 20;
        },

        /**
         * Manage prospect orders
         * See: @/components/prospect/order/Slide.vue
         */
        manageOrders() {
            store.commit(SET_PROSPECT_ORDER_TAB, 0);
            store.commit(OPEN_SLIDE, "prospect-manage-orders");
            store.commit(SET_PROSPECT, this.prospect);
        },
    },

    computed: {
        ...mapGetters(["project", "prospect", "prospectOrders"]),
    },
};
</script>
