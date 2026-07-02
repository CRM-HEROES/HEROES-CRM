<template>
    <component :is="tag" :class="[isFiltered ? 'filtered' : '']" :style="style">
        <default-cell
            v-if="category == 'default'"
            :order="order"
            :field="column.key"
        />
        <meta-cell
            v-else-if="category == 'meta'"
            :order="order"
            :field="column.id"
            :type="column.type"
        />
        <total-commission-cell
            v-else-if="category == 'total_commissions'"
            :order="order"
            :field="column.key"
        />
        <prospect-cell v-if="category == 'prospect'" :order="order" />
        <relation-cell
            v-else-if="category == 'prospect'"
            :order="order"
            :items="[order.prospect]"
            @click=""
        />
        <relation-cell
            v-else-if="category == 'category'"
            :order="order"
            :items="categoryLabels(column.id)"
            @click="manageLabels"
        />
        <relation-cell
            v-else-if="category == 'status'"
            :order="order"
            :items="order.status ? [order.status] : []"
            @click="showStatus"
        />
        <relation-cell
            v-else-if="category == 'steps'"
            :order="order"
            :items="steps"
            @click="showSteps"
        />
        <relation-cell
            v-else-if="category == 'products'"
            :order="order"
            :items="order.products"
            @click="showProducts"
        />
        <relation-cell
            v-else-if="category == 'commissions'"
            :order="order"
            :items="userCommissions"
            @click="showActions"
        />
    </component>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";
import {
    SET_PROSPECT,
    SET_PROSPECT_CATEGORY,
} from "@/actions/project/prospect";

import {
    SHOW_PROSPECT_ORDER,
    SET_PROSPECT_ORDER_TAB,
} from "@/actions/project/prospect/order";

// Components
import DefaultCell from "./cell/DefaultCell.vue";
import MetaCell from "./cell/MetaCell.vue";
import ProspectCell from "./cell/ProspectCell.vue";
import RelationCell from "./cell/RelationCell.vue";
import TotalCommissionCell from "./cell/TotalCommissionCell.vue";

export default {
    components: {
        DefaultCell,
        MetaCell,
        ProspectCell,
        RelationCell,
        TotalCommissionCell,
    },

    props: {
        /**
         * HTML tag
         * Default <td>
         * When the column is fixed
         * we use <div> as tag
         */
        tag: {
            type: String,
            default: "td",
        },

        /**
         * Column
         */
        column: {
            type: Array,
        },

        /**
         * Order
         */
        order: {
            type: Object,
        },
    },

    methods: {
        /**
         * Associated order labels
         * that belongs to the given category
         *
         * @param {*} categoryId
         */
        categoryLabels(categoryId) {
            if (
                !this.order.prospect ||
                !this.order.prospect.labels ||
                this.order.id <= 0
            ) {
                return [];
            }

            return this.order.prospect.labels.filter(
                (label) => label.category_id == categoryId
            );
        },

        /**
         * Manage order labels
         * See: @/components/order/label/Slide.vue
         */
        manageLabels() {
            if (!this.order.prospect || this.order.prospect.id <= 0) {
                return;
            }

            store.commit(SET_PROSPECT, this.order.prospect);
            store.commit(
                SET_PROSPECT_CATEGORY,
                this.categories.find((c) => c.id == this.column.id)
            );
            store.commit(OPEN_SLIDE, "prospect-manage-labels");
        },

        /**
         *
         * @param {*} order
         */
        showOrder() {
            store.commit(SET_PROSPECT_ORDER_TAB, 0);
            store.commit(OPEN_SLIDE, "prospect-manage-orders");

            store.commit(SET_PROSPECT, this.order.prospect);
            store.dispatch(SHOW_PROSPECT_ORDER, this.order.id);
        },

        /**
         * Go to the order products
         */
        showProducts() {
            store.commit(SET_PROSPECT_ORDER_TAB, 0);
            this.showOrder();
        },

        /**
         * Go to the order commissions
         */
        showActions() {
            store.commit(SET_PROSPECT_ORDER_TAB, 4);
            this.showOrder();
        },

        /**
         * Go to the order steps
         */
        showSteps() {
            store.commit(SET_PROSPECT_ORDER_TAB, 2);
            this.showOrder();
        },

        /**
         * Go to the order status
         */
        showStatus() {
            store.commit(SET_PROSPECT_ORDER_TAB, 5);
            this.showOrder();
        },
    },

    computed: {
        ...mapGetters([
            "categories",
            "ordersParamExists",
            "threads",
            "commissions",
            "projectUserSetting",
        ]),

        /**
         * Do not allow user
         * to edit field
         * on certain conditions applied to the order
         */
        disabled() {
            return this.order.deleted_at || this.order.processed_at;
        },

        /**
         *
         */
        decimal() {
            return this.projectUserSetting("orders-table.decimal", 2);
        },

        /**
         * Define the width of the column
         */
        style() {
            const width = this.column.width ? this.column.width : 120;

            return {
                maxWidth: `${width}px`,
                minWidth: `${width}px`,
            };
        },

        /**
         *
         */
        category() {
            return this.column.category;
        },

        /**
         *
         */
        userCommissions() {
            return this.order.actions.map((action) => {
                const commissions = this.commissions.filter(
                    (commission) =>
                        commission.action_id == action.id &&
                        commission.user_id == action.pivot.user_id &&
                        this.order.products.find(
                            (product) => product.id == commission.product_id
                        )
                );

                const total = commissions
                    .reduce(
                        (carry, commission) =>
                            carry + commission.absolute_value,
                        0
                    )
                    .toFixed(parseInt(this.decimal));

                return {
                    ...action,
                    name:
                        action.pivot.user.name +
                        ": " +
                        total +
                        this.order.currency,
                };
            });
        },

        /**
         *
         */
        steps() {
            return [...this.order.steps]
                .sort((a, b) => (a.order > b.order ? 1 : -1))
                .map((step, i) => ({
                    ...step,
                    name: i + 1 + ": " + step.name,
                }));
        },

        /**
         * Check if column is filtered
         */
        isFiltered() {
            switch (this.category) {
                case "commissions":
                    return (
                        this.ordersParamExists("withActions") ||
                        this.ordersParamExists("withoutActions")
                    );
                case "products":
                    return (
                        this.ordersParamExists("withProducts") ||
                        this.ordersParamExists("withoutProducts")
                    );
                case "status":
                    return (
                        this.ordersParamExists("withStatuses") ||
                        this.ordersParamExists("withoutStatuses")
                    );
                case "steps":
                    return (
                        this.ordersParamExists("withSteps") ||
                        this.ordersParamExists("withoutSteps")
                    );
                default:
                    return false;
            }
        },
    },
};
</script>
