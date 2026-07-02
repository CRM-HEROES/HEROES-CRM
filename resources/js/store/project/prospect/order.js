import ProspectOrderService from "@/apis/project/prospect/order";

import {
    FETCH_PROSPECT_ORDERS,
    SET_PROSPECT_ORDERS,
    SET_PROSPECT_ORDER,
    ADD_PROSPECT_ORDER,
    SHOW_PROSPECT_ORDER,
    UPDATE_PROSPECT_ORDER,
    REMOVE_PROSPECT_ORDER,
    SET_PROSPECT_ORDER_TAB,
} from "@/actions/project/prospect/order";

import productStore from "./order/product";
import actionStore from "./order/action";
import stepStore from "./order/step";
import commissionStore from "./order/commission";

/**
 * ProspectOrder Store state
 */
export const state = {
    orders: [],
    order: null,
};

/**
 * ProspectOrder Store Actions
 */
export const actions = {
    /**
     * Fetch prospect orders
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PROSPECT_ORDERS](context, params) {
        const { data } = await ProspectOrderService.get(
            context.state.project.slug,
            context.state.project.prospect.id,
            params
        );
        context.commit(SET_PROSPECT_ORDERS, data);
        return data;
    },

    /**
     * Add prospect order
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectOrder
     */
    async [ADD_PROSPECT_ORDER](context, params) {
        const { data } = await ProspectOrderService.create(
            context.state.project.slug,
            context.state.project.prospect.id,
            params
        );
        context.commit(ADD_PROSPECT_ORDER, data);
        return data;
    },

    /**
     * Show prospect order
     *
     * @param {*} context
     * @param {Number} slug folder id
     * @returns folder
     */
    async [SHOW_PROSPECT_ORDER](context, slug) {
        const { data } = await ProspectOrderService.show(
            context.state.project.slug,
            context.state.project.prospect.id,
            slug
        );
        context.commit(SET_PROSPECT_ORDER, data);
        return data;
    },

    /**
     * Update prospect order
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectOrder
     */
    async [UPDATE_PROSPECT_ORDER](context, params) {
        context.commit(UPDATE_PROSPECT_ORDER, params);
        const { data } = await ProspectOrderService.update(
            context.state.project.slug,
            context.state.project.prospect.id,
            params.id,
            params
        );
        return data;
    },

    /**
     * Remove prospect order
     *
     * @param {*} context
     * @param {Number} params prospect order id
     * @returns prospectOrder
     */
    async [REMOVE_PROSPECT_ORDER](context, slug) {
        await ProspectOrderService.destroy(
            context.state.project.slug,
            context.state.project.prospect.id,
            slug
        );
        context.commit(REMOVE_PROSPECT_ORDER, slug);
    },

    ...productStore.actions,
    ...actionStore.actions,
    ...stepStore.actions,
    ...commissionStore.actions,
};

/**
 * Prospect order Store Mutations
 */
export const mutations = {
    /**
     * Set prospect orders
     *
     * @param {*} state
     * @returns
     */
    [SET_PROSPECT_ORDERS](state, orders) {
        state.project.prospect.orders = orders;
    },

    /**
     * Set prospect order
     *
     * @param {*} state
     * @returns
     */
    [SET_PROSPECT_ORDER](state, order) {
        state.project.prospect.order = order;

        if (order) {
            state.project.prospect.orders = (
                state.project.prospect.orders &&
                Array.isArray(state.project.prospect.orders)
                    ? state.project.prospect.orders
                    : []
            ).map((o) => (o.id == order.id ? { ...o, ...order } : o));
        }
    },

    /**
     * Add prospect order
     *
     * @param {*} state
     * @param {Number} order to append to prospect orders list
     */
    [ADD_PROSPECT_ORDER](state, order) {
        state.project.prospect.orders = [
            ...(state.project.prospect.orders &&
            Array.isArray(state.project.prospect.orders)
                ? state.project.prospect.orders
                : []),
            order,
        ];
    },

    /**
     * Update prospect order
     *
     * @param {*} state
     * @param {Number} order to append to prospect orders list
     */
    [UPDATE_PROSPECT_ORDER](state, params) {
        if (
            state.project.prospect.order &&
            state.project.prospect.order.id == params.id
        ) {
            state.project.prospect.order = {
                ...state.project.prospect.order,
                ...params,
            };
        }

        state.project.prospect.orders = (
            state.project.prospect.orders &&
            Array.isArray(state.project.prospect.orders)
                ? state.project.prospect.orders
                : []
        ).map((o) => (o.id == params.id ? { ...o, ...params } : o));

        state.project.orders = (
            state.project.orders && Array.isArray(state.project.orders)
                ? state.project.orders
                : []
        ).map((o) => (o.id == params.id ? { ...o, ...params } : o));
    },

    /**
     * Remove prospect order
     *
     * @param {*} context
     * @param {Number} params prospect order id
     */
    [REMOVE_PROSPECT_ORDER](state, slug) {
        state.project.prospect.orders = (
            state.project.prospect.orders &&
            Array.isArray(state.project.prospect.orders)
                ? state.project.prospect.orders
                : []
        ).filter((o) => o.id != slug);
    },

    /**
     * Set prospect order tab
     *
     * @param {*} context
     * @param {Number} tab
     */
    [SET_PROSPECT_ORDER_TAB](state, tab) {
        state.project.orderTab = tab;
    },

    ...productStore.mutations,
    ...actionStore.mutations,
    ...stepStore.mutations,
    ...commissionStore.mutations,
};

/**
 * Prospect order Store Getters
 */
export const getters = {
    /**
     * Get list of prospect orders
     *
     * @param {*} state
     * @returns
     */
    prospectOrders: (state) =>
        state.project && state.project.prospect && state.project.prospect.orders
            ? state.project.prospect.orders
            : [],

    /**
     * Get current prospect order
     *
     * @param {*} state
     * @returns
     */
    prospectOrder: (state) =>
        state.project && state.project.prospect && state.project.prospect.order
            ? state.project.prospect.order
            : null,

    /**
     * Get current prospect order tab
     *
     * @param {*} state
     * @returns
     */
    prospectOrderTab: (state) =>
        state.project && state.project.orderTab ? state.project.orderTab : 0,

    ...productStore.getters,
    ...actionStore.getters,
    ...stepStore.getters,
    ...commissionStore.getters,
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
