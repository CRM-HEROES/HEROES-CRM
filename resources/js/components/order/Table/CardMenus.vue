<template>
    <card-menu-list>
        <template v-if="bulkDisabled">
            <card-menu
                v-if="filtersActivated"
                icon="fa fa-filter"
                :label="$t('order.table.footer.init_filters')"
                title="Réinitialiser les filtres"
                style="background-color: #e6effd"
                color="#1a73e8"
                @click="initParams"
            />
            <card-menu
                icon="fa fa-ellipsis-h"
                :label="$t('order.table.footer.toggle_order_menus')"
                v-tuto="{
                    key: 'project.order.table.footer.toggle-order-menus',
                    name: 'Tableau de orders - Menus par ligne',
                    body: 'Vous pouvez afficher des menus raccourcis par order',
                }"
                @click="toggleOrdersOptions"
            />
            <card-menu
                v-if="can('all.order.add')"
                icon="fa fa-plus icon-green"
                :label="$t('order.table.footer.add_order')"
                v-tuto="{
                    key: 'project.order.table.footer.add-order',
                    name: 'Tableau de orders - Nouveau order',
                    body: 'Créer une nouvelle ligne de order',
                }"
                @click="addOrder"
            />
            <div
                class="hc-orders-table-footer-select"
                id="hc-orders-table-rows-count"
                v-tooltip="$t('order.table.footer.page_orders_count')"
            >
                <select v-model="count">
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                    <option value="300">300</option>
                    <option value="400">400</option>
                    <option value="500">500</option>
                </select>
                <loading :loading="settingOrdersCount" />
            </div>
            <div
                v-tooltip="$t('order.table.footer.orders_count')"
                id="hc-orders-table-total"
                v-text="ordersTotal"
            ></div>
            <card-menu
                icon="fa fa-table"
                :label="$t('order.table.footer.setting')"
                v-tuto="{
                    key: 'project.order.table.footer.add-column',
                    name: 'Tableau de devis - Colonnes',
                    body: 'Ajouter une nouvelle colonne dans le tableau de devis.<br>(SMS, RDV, devis, appels, filtres, ...)',
                }"
                @click="addColumn"
            />
        </template>

        <template v-if="!bulkDisabled">
            <card-menu
                icon="fa fa-check-square"
                :label="$t('order.table.footer.deselect')"
                @click="bulkDeselect"
            />
        </template>

        <template v-if="bulkDisabled">
            <card-menu
                :disabled="fetchingPreviousPage || ordersPage <= 1"
                icon="fa fa-caret-left"
                :label="$t('order.table.footer.previous')"
                @click="previousPage"
            >
                <loading :loading="fetchingPreviousPage" />
            </card-menu>
            <div
                class="hc-orders-table-footer-select"
                id="hc-orders-table-pages"
                v-tooltip="$t('order.table.footer.current_page')"
            >
                <select v-model="page">
                    <option
                        v-for="i in pagesCount"
                        :value="i"
                        v-text="i"
                    ></option>
                </select>
                <loading :loading="settingOrdersPage" />
            </div>
            <card-menu
                :disabled="fetchingNextPage || orders.length < ordersCount"
                icon="fa fa-caret-right"
                :label="$t('order.table.footer.next')"
                @click="nextPage"
            >
                <loading :loading="fetchingNextPage" />
            </card-menu>
        </template>

        <template v-if="!bulkDisabled">
            <card-menu
                v-if="can('all.order.delete')"
                icon="fa fa-trash"
                :label="$t('order.table.footer.bulk_delete')"
                color="#d9402c"
                @click="bulkRemove"
            >
                <loading :loading="bulkingRemove" />
            </card-menu>
        </template>
        <!--card-menu icon="fa fa-search" :label="$t('order.table.footer.search_and_replace')" /-->
    </card-menu-list>
</template>

<style scoped>
.hc-orders-table-footer-select {
    position: relative;
    width: unset !important;
    min-width: unset !important;
}

.hc-orders-table-footer-select > select {
    border: none;
    background: none;
    padding: 3px 20px 3px 8px;
    border-radius: 7px;
    font-size: 12px;
    -webkit-appearance: none;
    -moz-appearance: none;
    height: 40px;
    width: auto;
    background-color: #f0f0f0;
}

.hc-orders-table-footer-select > select:hover {
    background-color: #eeeeee;
    cursor: pointer;
}

.hc-orders-table-footer-select:before {
    pointer-events: none;
}

.hc-orders-table-footer-select:after {
    content: "";
    position: absolute;
    right: 7px;
    top: 17px;
    width: 0;
    height: 0;
    border-radius: 2px;
    border-top: 5px solid #555555;
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
    pointer-events: none;
}

#hc-orders-table-total {
    background-color: #7939b8;
    color: white;
    padding: 0 10px;
    margin: 5px;
    border-radius: 15px;
    height: 30px;
    line-height: 30px;
    width: auto;
    font-size: 13px;
    min-width: unset;
}

#hc-orders-table-pages:before {
    content: "";
    position: absolute;
    border-left: 1px solid;
    top: 13px;
    right: 9px;
    width: 0;
    height: 12px;
    z-index: 1;
}

#hc-orders-table-pages:after {
    top: 13px;
    right: 2px;
    width: 15px;
    height: 12px;
    border: 1px solid;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { OPEN_MODAL } from "@/actions/modal";
import { OPEN_SLIDE } from "@/actions/slide";
import { SET_PROSPECT_ORDER_TAB } from "@/actions/project/prospect/order";
import {
    BULK_REMOVE_ORDER,
    FETCH_ORDERS,
    UPDATE_SELECTED_ORDERS,
    INIT_ORDER_PARAMS,
    SET_ORDERS_PAGE,
    TOGGLE_ORDERS_OPTIONS,
    SET_ORDERS_COUNT,
} from "@/actions/project/order";
import { SET_PROSPECT } from "@/actions/project/prospect";

export default {
    data() {
        return {
            createdOrders: 0,
            bulkingRemove: false,
            bulkingRestore: false,
            bulkingProcessed: false,
            bulkingNotProcessed: false,
            fetchingPreviousPage: false,
            fetchingNextPage: false,
            settingOrdersCount: false,
            settingOrdersPage: false,
        };
    },

    methods: {
        /**
         * Deselect selected order
         */
        bulkDeselect() {
            store.commit(UPDATE_SELECTED_ORDERS, []);
        },

        /**
         * Store new order
         */
        async addOrder() {
            store.commit(SET_PROSPECT, null);
            store.commit(SET_PROSPECT_ORDER_TAB, 0);
            store.commit(OPEN_SLIDE, "prospect-manage-orders");
        },

        /**
         * Add other columns
         * in the table
         */
        addColumn() {
            store.commit(OPEN_MODAL, "orders-table-add-column");
        },

        /**
         * Previous page
         */
        async previousPage() {
            store.commit(SET_ORDERS_PAGE, parseInt(this.ordersPage) - 1);

            // Refresh orders list
            this.fetchingPreviousPage = true;

            try {
                await store.dispatch(FETCH_ORDERS);
            } finally {
                this.fetchingPreviousPage = false;
            }
        },

        /**
         * Next page
         */
        async nextPage() {
            store.commit(SET_ORDERS_PAGE, parseInt(this.ordersPage) + 1);

            // Refresh orders list
            this.fetchingNextPage = true;

            try {
                await store.dispatch(FETCH_ORDERS);
            } finally {
                this.fetchingNextPage = false;
            }
        },

        /**
         * Init filters
         */
        initParams() {
            store.commit(INIT_ORDER_PARAMS);
            store.dispatch(FETCH_ORDERS);
        },

        /**
         * Toggle orders options
         * Show or hide each order menus (sms, phone, order, ..)
         * from the orders table
         */
        toggleOrdersOptions() {
            store.commit(TOGGLE_ORDERS_OPTIONS);
        },

        /**
         * Remove multiple orders
         */
        async bulkRemove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.bulkingRemove = true;

                // Selected orders
                const ordersSelected = this.ordersSelected;
                store.commit(UPDATE_SELECTED_ORDERS, []);

                try {
                    // Dispatch bulk remove
                    await store.dispatch(BULK_REMOVE_ORDER, ordersSelected);
                    // Refresh orders list
                    store.dispatch(FETCH_ORDERS);
                } finally {
                    this.bulkingRemove = false;
                }
            });
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "ordersSelected",
            "orders",
            "ordersParamExists",
            "ordersPage",
            "ordersCount",
            "ordersTotal",
            "ordersParams",
            "ordersParamsUrl",
            "ordersSortOrder",
            "ordersSortBy",
            "can",
        ]),

        /**
         * Check if there are
         * selected orders
         */
        bulkDisabled() {
            return this.ordersSelected.length == 0;
        },

        /**
         * Check if
         * all selected orders
         * is processed
         */
        selectedOrdersProcessed() {
            return (
                this.orders.filter(
                    (p) =>
                        this.ordersSelected.indexOf(p.id) >= 0 && p.processed_at
                ).length == this.ordersSelected.length
            );
        },

        /**
         * Check if
         * all selected orders
         * is not processed
         */
        selectedOrdersNotProcessed() {
            return (
                this.orders.filter(
                    (p) =>
                        this.ordersSelected.indexOf(p.id) >= 0 &&
                        !p.processed_at
                ).length == this.ordersSelected.length
            );
        },

        /**
         * Check if
         * there are activated filters
         */
        filtersActivated() {
            return (
                this.ordersParamExists() ||
                this.ordersSortOrder ||
                this.ordersSortBy
            );
        },

        count: {
            get: function () {
                return this.ordersCount;
            },
            set: async function (value) {
                store.commit(SET_ORDERS_COUNT, value);
                this.settingOrdersCount = true;

                try {
                    await store.dispatch(FETCH_ORDERS);
                } finally {
                    this.settingOrdersCount = false;
                }
            },
        },

        page: {
            get: function () {
                return this.ordersPage;
            },
            set: async function (value) {
                store.commit(SET_ORDERS_PAGE, value);
                this.settingOrdersPage = true;

                try {
                    await store.dispatch(FETCH_ORDERS);
                } finally {
                    this.settingOrdersPage = false;
                }
            },
        },

        pagesCount() {
            return Math.ceil(this.ordersTotal / this.ordersCount);
        },
    },
};
</script>
