<template>
    <tr
        :class="[
            order.deleted_at ? 'deleted' : '',
            order.processed_at ? 'processed' : '',
            isSelected ? 'selected' : '',
        ]"
    >
        <td class="fixed">
            <!-- Select order -->
            <label class="hc-table-selector">
                <input
                    type="checkbox"
                    v-model="selected"
                    :value="order.id"
                    @click="toggleSelectedOrder"
                />
                <span></span>
            </label>

            <div class="hc-table-row-options">
                <template v-for="menu in projectUserSettingsOrdersTableMenus">
                    <!-- View order -->
                    <a
                        v-if="menu == 'view'"
                        class="hc-table-order-option-view"
                        @click.prevent="showOrder"
                        ><i class="fa fa-eye"></i
                    ></a>

                    <!-- Commission -->
                    <a
                        v-else-if="menu == 'commission'"
                        @click.prevent="showCommissions"
                        ><i class="fa fa-money-bill"></i
                    ></a>

                    <!-- Document -->
                    <a
                        v-else-if="menu == 'document'"
                        @click.prevent="showDocuments"
                        ><i class="fa fa-file-pdf"></i
                    ></a>
                </template>
            </div>

            <!-- List of fixed columns -->
            <div class="hc-table-fixed-cells">
                <cell
                    v-for="column in fixedColumns"
                    tag="div"
                    :key="order.id + '-' + column.key"
                    :order="order"
                    :column="column"
                />
            </div>
        </td>

        <!-- List of none fixed columns -->
        <cell
            v-for="column in notFixedColumns"
            :key="order.id + '-' + column.key"
            :order="order"
            :column="column"
        />
    </tr>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    UPDATE_SELECTED_ORDERS,
    TOGGLE_SELECTED_ORDERS,
} from "@/actions/project/order";
import { SET_PROSPECT } from "@/actions/project/prospect";
import {
    SHOW_PROSPECT_ORDER,
    SET_PROSPECT_ORDER_TAB,
} from "@/actions/project/prospect/order";
import { OPEN_SLIDE } from "@/actions/slide";

// Components
import Cell from "./Cell.vue";

export default {
    components: {
        Cell,
    },

    props: {
        /**
         * Order
         */
        order: {
            type: Object,
        },

        /**
         * List of fixed columns
         */
        fixedColumns: {
            type: Array,
        },

        /**
         * List of none fixed columns
         */
        notFixedColumns: {
            type: Array,
        },

        /**
         * Index of the row in the orders table
         * we need it when we use SHIFT
         * to select multiple orders
         * See: toggleSelectedOrder
         */
        index: {
            type: Number,
        },
    },

    methods: {
        /**
         * Handle SHIFT
         * when clicking checkbox
         *
         * @param {*} event
         */
        toggleSelectedOrder(event) {
            const index = this.index;
            const shift = event.shiftKey;
            const checked = event.currentTarget.checked;

            store.commit(TOGGLE_SELECTED_ORDERS, { index, shift, checked });
        },

        /**
         * Go to the order slide
         */
        showOrder() {
            store.commit(SET_PROSPECT_ORDER_TAB, 0);
            store.commit(OPEN_SLIDE, "prospect-manage-orders");

            store.commit(SET_PROSPECT, this.order.prospect);
            store.dispatch(SHOW_PROSPECT_ORDER, this.order.id);
        },

        /**
         * Go to the order commissions
         */
        showCommissions() {
            store.commit(SET_PROSPECT_ORDER_TAB, 4);
            this.showOrder();
        },

        /**
         * Go to the order documents
         */
        showDocuments() {
            store.commit(SET_PROSPECT_ORDER_TAB, 3);
            this.showOrder();
        },
    },

    computed: {
        /**
         * Select a row
         */
        selected: {
            get() {
                return this.ordersSelected;
            },
            set(value) {
                store.commit(UPDATE_SELECTED_ORDERS, value);
            },
        },

        /**
         * Is row selected
         */
        isSelected() {
            return this.ordersSelected.indexOf(this.order.id) >= 0;
        },

        ...mapGetters("auth", ["user"]),
        ...mapGetters([
            "project",
            "ordersSelected",
            "projectUserSettingsOrdersTableMenus",
            "can",
        ]),
    },
};
</script>
