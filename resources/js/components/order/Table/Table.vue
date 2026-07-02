<template>
    <div :class="['hc-table', ordersOptions ? 'hc-table-show-options' : '']">
        <table v-if="this.columns.length > 0">
            <header-row
                :columns="columns"
                :fixed-columns="fixedColumns"
                :not-fixed-columns="notFixedColumns"
            />

            <tbody>
                <row
                    v-for="(order, index) in orders"
                    :key="order.id"
                    :index="index"
                    :order="order"
                    :fixed-columns="fixedColumns"
                    :not-fixed-columns="notFixedColumns"
                />

                <footer-row
                    :fixed-columns="fixedColumns"
                    :not-fixed-columns="notFixedColumns"
                />
            </tbody>
        </table>

        <loading :loading="loading" />
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { OPEN_SLIDE } from "@/actions/slide";

import {
    FETCH_ORDERS,
    INIT_ORDER_PARAMS,
    SET_ORDER_PARAMS,
    SET_ORDERS_PAGE,
    SET_ORDERS_COUNT,
    SET_ORDERS_SORT_ORDER,
    SET_ORDERS_SORT_BY,
} from "@/actions/project/order";
import { SET_PROSPECT_ORDER_TAB } from "@/actions/project/prospect/order";

import { FETCH_COMMISSIONS } from "@/actions/project/user/commission";

// Components
import HeaderRow from "./Header.vue";
import FooterRow from "./Footer.vue";
import Row from "./Row.vue";

export default {
    components: {
        HeaderRow,
        FooterRow,
        Row,
    },

    data() {
        return {
            loading: false,
        };
    },

    created() {
        store.commit(SET_PROSPECT_ORDER_TAB, 0);
        this.updateParamsFromUrl();
        this.showTable();
    },

    methods: {
        /**
         * Update params from URL
         */
        updateParamsFromUrl() {
            store.commit(INIT_ORDER_PARAMS);
            // store.commit(SET_ORDERS_SORT_BY, "id");
            // store.commit(SET_ORDERS_SORT_ORDER, "desc");

            const url = new URL(window.location.href);
            const searchParams = new URLSearchParams(url.search);
            const filters = {};

            let ordersCount = 50;

            searchParams.forEach(function (value, param) {
                if (param == "page") {
                    store.commit(SET_ORDERS_PAGE, parseInt(value));
                } else if (param == "count") {
                    ordersCount = parseInt(value);
                } else if (param == "sortOrder") {
                    store.commit(SET_ORDERS_SORT_ORDER, value);
                } else if (param == "sortBy") {
                    store.commit(SET_ORDERS_SORT_BY, value);
                } else if (param == "filters") {
                    try {
                        value = JSON.parse(value);

                        for (let key in value) {
                            filters[key] = value[key];
                        }
                    } catch (e) {}
                }
            });

            store.commit(SET_ORDER_PARAMS, filters);
            store.commit(SET_ORDERS_COUNT, ordersCount);
        },

        /**
         * Import orders (XLSX, CSV)
         */
        importOrders() {
            store.commit(OPEN_SLIDE, "import");
            store.commit(OPEN_MODAL, "import-add");
        },

        /**
         *
         */
        showTable() {
            this.loading = true;

            Promise.all([this.getOrders()])
                .then((results) => {
                    this.getCommissions();
                })
                .catch((error) => {
                    console.error("An error occurred:", error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        /**
         * Get project prospects
         */
        getOrders() {
            store.dispatch(FETCH_ORDERS);
        },

        /**
         *
         */
        getCommissions() {
            const actions = this.orders
                .map((order) => {
                    return order.actions.map((action) => action.id);
                })
                .reduce((carry, actions) => [...carry, ...actions], []);
            const users = this.orders
                .map((order) => {
                    return order.actions.map((action) => action.pivot.user_id);
                })
                .reduce((carry, users) => [...carry, ...users], []);
            const products = this.orders
                .map((order) => {
                    return order.products.map((product) => product.id);
                })
                .reduce((carry, products) => [...carry, ...products], []);

            store.dispatch(FETCH_COMMISSIONS, {
                filters: JSON.stringify({
                    actions,
                    users,
                    products,
                }),
            });
        },
    },

    computed: {
        ...mapGetters([
            "projectUserSettingsOrdersTable",
            "fields",
            "categories",
            "threads",
            "orders",
            "project",
            "ordersOptions",
            "ordersMaxLinesPerRow",
            "ordersParamExists",
            "ordersParamsUrl",
            "ordersPage",
            "ordersCount",
            "commissions",
            "can",
            "canEvent",
        ]),

        /**
         * Columns
         */
        columns: function () {
            return this.projectUserSettingsOrdersTable
                .map((column, index) => {
                    const key = column.key;
                    let type = "list";
                    let category = key;
                    let id = key;
                    let name;

                    // Users
                    if (key == "total_commissions") {
                        name = "Commissions totales";
                        // Groups
                    } else if (key == "prospect") {
                        name = "Prospect";
                        // Orders
                    } else if (key == "products") {
                        name = "Produits";
                        // Import
                    } else if (key == "status") {
                        name = "Etat";
                        // Event
                    } else if (key == "steps") {
                        name = "Etapes";
                        // Label
                    } else if (key == "commissions") {
                        name = "Commissions";
                    } else if (key.indexOf("category->") == 0) {
                        const categoryId = key.replace("category->", "");

                        const c = this.categories.find(
                            (category) => category.id == categoryId
                        );

                        if (!c) {
                            return null;
                        }

                        name = c.name;
                        category = "category";
                        id = c.id;
                        // Meta field
                    } else if (key.indexOf("meta->") == 0) {
                        const slug = key.replace("meta->", "");

                        const field = this.fields.find(
                            (field) =>
                                field.for == "order" &&
                                field.meta &&
                                field.slug == slug
                        );

                        if (!field) {
                            return null;
                        }

                        name = field.name;
                        type = field.type;
                        category = "meta";
                        id = field.slug;
                    } else {
                        const slug = key;

                        const field = this.fields.find(
                            (field) =>
                                field.for == "order" &&
                                !field.meta &&
                                field.slug == slug
                        );

                        if (!field) {
                            return null;
                        }

                        name = field.name;
                        type = field.type;
                        category = "default";
                    }

                    return {
                        ...column,
                        name: name,
                        type: type,
                        index: index,
                        id: id,
                        category: category,
                    };
                })
                .filter((column) => column);
        },

        /**
         *
         */
        fixedColumns() {
            return this.columns.filter((c) => c.fixed && !c.hidden);
        },

        /**
         *
         */
        notFixedColumns() {
            return this.columns.filter((c) => !c.fixed && !c.hidden);
        },
    },

    watch: {
        ordersParamsUrl(newValue) {
            let url = "?page=" + this.ordersPage + "&count=" + this.ordersCount;

            if (newValue) {
                url += "&filters=" + newValue;
            }

            history.pushState(null, null, url);
        },
    },
};
</script>
