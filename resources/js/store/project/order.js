import OrderService from "@/apis/project/order";

import {
    FETCH_ORDERS,
    SET_ORDERS,
    UPDATE_SELECTED_ORDERS,
    TOGGLE_SELECTED_ORDERS,
    SET_ORDERS_PAGE,
    SET_ORDERS_COUNT,
    SET_ORDERS_TOTAL,
    SET_ORDERS_SORT_BY,
    SET_ORDERS_SORT_ORDER,
    SET_ORDER_PARAMS,
    ADD_ORDER_PARAMS,
    REMOVE_ORDER_PARAMS,
    INIT_ORDER_PARAMS,
    BULK_REMOVE_ORDER,
    TOGGLE_ORDERS_OPTIONS,
} from "@/actions/project/order";

/**
 * Order Store state
 */
export const state = {
    orders: [],
    ordersSelected: [],
    ordersSelectedIndex: null,
    ordersPage: 1,
    ordersCount: 50,
    ordersSortBy: null,
    ordersSortOrder: "desc",
    ordersOptions: false,
    ordersParams: {},
};

/**
 * Order Store Actions
 */
const actions = {
    /**
     * Fetch orders
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_ORDERS](context) {
        const params = {
            page: context.state.project.ordersPage
                ? context.state.project.ordersPage
                : 1,
            count: context.state.project.ordersCount
                ? context.state.project.ordersCount
                : 50,
        };

        if (
            context.state.project.ordersParams &&
            Object.keys(context.state.project.ordersParams).length > 0
        ) {
            params.filters = JSON.stringify(
                context.state.project.ordersParams
                    ? context.state.project.ordersParams
                    : {}
            );
        }

        if (context.state.project.ordersSortBy) {
            params.sortBy = context.state.project.ordersSortBy;
        }

        if (context.state.project.ordersSortOrder) {
            params.sortOrder = context.state.project.ordersSortOrder;
        }

        if (context.state.project.ordersFields) {
            params.fields = context.state.project.ordersFields;
        }

        const { data } = await OrderService.get(context.state.project.slug, {
            params: params,
        });
        context.commit(SET_ORDERS, data.data);
        context.commit(SET_ORDERS_TOTAL, data.total);
        return data.data;
    },

    /**
     * Bulk remove order
     *
     * @param {*} context
     * @param {Number} params order id
     * @returns order
     */
    async [BULK_REMOVE_ORDER](context, orders) {
        context.commit(BULK_REMOVE_ORDER, orders);
        await OrderService.bulkDestroy(context.state.project.slug, orders);
    },
};

/**
 * Order Store Mutations
 */
const mutations = {
    /**
     * Set orders
     *
     * @param {*} state
     * @returns
     */
    [SET_ORDERS](state, orders) {
        state.project.orders = [...orders];
    },

    /**
     * Set orders total
     *
     * @param {*} state
     * @returns
     */
    [SET_ORDERS_TOTAL](state, total) {
        state.project.ordersTotal = total;
    },

    /**
     * Update list of selected orders
     *
     * @param {*} state
     */
    [UPDATE_SELECTED_ORDERS](state, value) {
        state.project.ordersSelected = Array.isArray(value) ? value : [];
    },

    /**
     * Toggle list of selected orders
     *
     * @param {*} state
     */
    [TOGGLE_SELECTED_ORDERS](state, { index, shift, checked }) {
        const ordersSelectedIndex = state.project.ordersSelectedIndex;
        state.project.ordersSelectedIndex = index;

        if (
            ordersSelectedIndex === null ||
            !shift ||
            index === ordersSelectedIndex
        ) {
            return;
        }

        const subset = state.project.orders
            .slice(
                Math.min(index, ordersSelectedIndex + 1),
                Math.max(index, ordersSelectedIndex - 1) + 1
            )
            .map((order) => order.id);

        state.project.ordersSelected = checked
            ? [
                  ...state.project.ordersSelected,
                  ...subset.filter(
                      (id) => state.project.ordersSelected.indexOf(id) == -1
                  ),
              ]
            : state.project.ordersSelected.filter(
                  (id) => subset.indexOf(id) == -1
              );
    },

    /**
     * @param {*} state
     */
    [SET_ORDERS_PAGE](state, page) {
        state.project.ordersPage = page;
    },

    /**
     * @param {*} state
     */
    [SET_ORDERS_COUNT](state, count) {
        state.project.ordersCount = count;
    },

    /**
     * @param {*} state
     */
    [SET_ORDERS_SORT_BY](state, column) {
        state.project.ordersSortBy = column;
    },

    /**
     * @param {*} state
     */
    [SET_ORDERS_SORT_ORDER](state, order) {
        state.project.ordersSortOrder = order;
    },

    /**
     * @param {*} state
     */
    [SET_ORDER_PARAMS](state, value) {
        if (typeof value == "object") {
            state.project.ordersParams = value;
        }
    },

    /**
     * @param {*} state
     */
    [ADD_ORDER_PARAMS](state, { key, value, multiple }) {
        state.project.ordersPage = 1;

        if (!state.project.ordersParams) {
            state.project.ordersParams = {};
        }

        if (value == undefined) {
            value = "";
        }

        if (multiple) {
            if (state.project.ordersParams[key] === undefined) {
                state.project.ordersParams[key] = [];
            }

            let values = "" + value;
            if (state.project.ordersParams[key] !== "") {
                values = [...state.project.ordersParams[key], value];
            }

            state.project.ordersParams = Object.fromEntries(
                Object.entries(state.project.ordersParams).map(([k, v]) => [
                    k,
                    k == key ? values : v,
                ])
            );
        } else {
            state.project.ordersParams = {
                ...state.project.ordersParams,
                [key]: value,
            };
        }
    },

    /**
     * @param {*} state
     */
    [REMOVE_ORDER_PARAMS](state, { key, value }) {
        state.project.ordersPage = 1;

        if (!state.project.ordersParams) {
            state.project.ordersParams = {};
        }

        if (value !== undefined) {
            if (state.project.ordersParams[key]) {
                state.project.ordersParams = Object.fromEntries(
                    Object.entries(state.project.ordersParams).map(([k, v]) => [
                        k,
                        k == key
                            ? state.project.ordersParams[k].filter(
                                  (val) => val != value
                              )
                            : v,
                    ])
                );

                if (state.project.ordersParams[key].length == 0) {
                    state.project.ordersParams = Object.fromEntries(
                        Object.entries(state.project.ordersParams).filter(
                            ([k]) => k != key
                        )
                    );
                }
            }
        } else {
            state.project.ordersParams = Object.fromEntries(
                Object.entries(state.project.ordersParams).filter(
                    ([k]) => k != key
                )
            );
        }
    },

    /**
     * @param {*} state
     */
    [INIT_ORDER_PARAMS](state) {
        state.project.ordersPage = 1;

        state.project.ordersSortOrder = null;
        state.project.ordersSortBy = null;
        state.project.ordersParams = {}; /*Object.fromEntries(
            Object.entries(state.project.ordersParams).filter(([k]) => k == "count")
        );*/
    },

    /**
     * Update list of orders bulk processed
     *
     * @param {*} state
     */
    [BULK_REMOVE_ORDER](state, orders) {
        state.project.orders = state.project.orders.map((order) =>
            orders.indexOf(order.id) >= 0
                ? { ...order, deleted_at: new Date() }
                : order
        );
    },

    /**
     * Toggle show orders table options
     *
     * @param {*} state
     */
    [TOGGLE_ORDERS_OPTIONS](state) {
        state.project.ordersOptions = !state.project.ordersOptions;
    },
};

/**
 * Order Store Getters
 */
const getters = {
    /**
     * Get list of orders
     *
     * @param {*} state
     * @returns
     */
    orders: (state) =>
        state.project &&
        state.project.orders &&
        Array.isArray(state.project.orders)
            ? state.project.orders
            : [],

    /**
     * Get total of orders
     *
     * @param {*} state
     * @returns
     */
    ordersTotal: (state) =>
        state.project && state.project.ordersTotal
            ? state.project.ordersTotal
            : 0,

    /**
     * Get list of selected orders
     *
     * @param {*} state
     * @returns
     */
    ordersSelected: (state) =>
        state.project &&
        state.project.ordersSelected &&
        Array.isArray(state.project.ordersSelected)
            ? state.project.ordersSelected
            : [],

    /**
     * @param {*} state
     * @returns
     */
    ordersParams: (state) =>
        state.project && state.project.ordersParams
            ? state.project.ordersParams
            : {},

    /**
     * @param {*} state
     * @returns
     */
    ordersParamsUrl: (state) =>
        state.project &&
        state.project.ordersParams &&
        Object.keys(state.project.ordersParams).length > 0
            ? encodeURI(JSON.stringify(state.project.ordersParams))
            : "",

    /**
     * @param {*} state
     * @returns
     */
    ordersParamValue: (state) => (param) =>
        state.project && state.project.ordersParams
            ? state.project.ordersParams[param]
            : undefined,

    /**
     * @param {*} state
     * @returns
     */
    ordersParamExists: (state) => (param, value) => {
        if (!state.project || !state.project.ordersParams) {
            return false;
        }

        if (param === undefined) {
            return Object.keys(state.project.ordersParams).length > 0;
        }

        if (value === undefined) {
            return state.project.ordersParams[param] !== undefined;
        }

        return (
            state.project.ordersParams[param] !== undefined &&
            state.project.ordersParams[param].findIndex((p) => p == value) >= 0
        );
    },

    /**
     * @param {*} state
     * @returns
     */
    ordersParamCount: (state) => (param) => {
        if (
            !state.project ||
            !state.project.ordersParams ||
            state.project.ordersParams[param] === undefined
        ) {
            return 0;
        }

        if (!Array.isArray(state.project.ordersParams[param])) {
            return 1;
        }

        return state.project.ordersParams[param].length;
    },

    /**
     * Get orders page
     *
     * @param {*} state
     * @returns
     */
    ordersPage: (state) => (state.project ? state.project.ordersPage : 0),

    /**
     * Get orders count pagination
     *
     * @param {*} state
     * @returns
     */
    ordersCount: (state) =>
        state.project && state.project.ordersCount
            ? state.project.ordersCount
            : 50,

    /**
     * Get orders sort by
     *
     * @param {*} state
     * @returns
     */
    ordersSortBy: (state) =>
        state.project ? state.project.ordersSortBy : null,

    /**
     * Get orders sort order
     *
     * @param {*} state
     * @returns
     */
    ordersSortOrder: (state) =>
        state.project ? state.project.ordersSortOrder : "desc",

    /**
     * Get orders options
     *
     * @param {*} state
     * @returns
     */
    ordersOptions: (state) =>
        state.project ? state.project.ordersOptions : false,
};

/**
 * Store
 */
export default {
    state,
    actions,
    mutations,
    getters,
};
