import orderActionService from "@/apis/project/order/action";

import {
    FETCH_ORDER_ACTIONS,
    SET_ORDER_ACTIONS,
    SET_ORDER_ACTION,
    ADD_ORDER_ACTION,
    SHOW_ORDER_ACTION,
    UPDATE_ORDER_ACTION,
    REMOVE_ORDER_ACTION,
} from "@/actions/project/order/action";

/**
 * orderAction Store state
 */
export const state = {
    orderActions: [],
    orderAction: null,
};

/**
 * orderAction Store Actions
 */
const actions = {
    /**
     * Fetch orderActions
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_ORDER_ACTIONS](context, params) {
        const { data } = await orderActionService.get(
            context.state.project.slug,
            params
        );
        context.commit(SET_ORDER_ACTIONS, data);
        return data;
    },

    /**
     * Show orderAction
     *
     * @param {*} context
     * @param {Number} slug orderAction id
     * @returns orderAction
     */
    async [SHOW_ORDER_ACTION](context, slug) {
        const { data } = await orderActionService.show(
            context.state.project.slug,
            slug
        );
        context.commit(SET_ORDER_ACTION, data);
        return data;
    },

    /**
     * Add orderAction
     *
     * @param {*} context
     * @param {Object} params orderAction orderAction values
     * @returns orderAction
     */
    async [ADD_ORDER_ACTION](context, params) {
        const { data } = await orderActionService.create(
            context.state.project.slug,
            params
        );
        context.commit(ADD_ORDER_ACTION, data);
        return data;
    },

    /**
     * update orderAction
     *
     * @param {*} context
     * @param {Object} params new orderAction orderAction values
     */
    async [UPDATE_ORDER_ACTION](context, params) {
        await orderActionService.update(
            context.state.project.slug,
            params.id,
            params
        );
        context.commit(UPDATE_ORDER_ACTION, params);
    },

    /**
     * remove orderAction
     *
     * @param {*} context
     * @param {Number} params orderAction id
     * @returns orderAction
     */
    async [REMOVE_ORDER_ACTION](context, slug) {
        await orderActionService.destroy(context.state.project.slug, slug);
        context.commit(REMOVE_ORDER_ACTION, slug);
    },
};

/**
 * orderAction Store Mutations
 */
const mutations = {
    /**
     * Set orderActions
     *
     * @param {*} state
     * @returns
     */
    [SET_ORDER_ACTIONS](state, orderActions) {
        state.project.order_actions = [...orderActions];
    },

    /**
     * Set current orderAction
     *
     * @param {*} state
     * @param {Object} orderAction
     */
    [SET_ORDER_ACTION](state, orderAction) {
        state.project.orderAction = orderAction;
    },

    /**
     * Add orderAction
     *
     * @param {*} state
     * @param {Number} orderAction to append to orderActions list
     */
    [ADD_ORDER_ACTION](state, orderAction) {
        state.project.order_actions = [
            ...(state.project.order_actions ? state.project.order_actions : []),
            orderAction,
        ];
    },

    /**
     * Update orderAction
     *
     * @param {*} state
     */
    [UPDATE_ORDER_ACTION](state, params) {
        state.project.order_actions = state.project.order_actions.map((o) =>
            o.id == params.id ? { ...o, ...params } : o
        );
    },

    /**
     * remove orderAction
     *
     * @param {*} context
     * @param {Number} params orderAction id
     */
    [REMOVE_ORDER_ACTION](state, slug) {
        state.project.order_actions = state.project.order_actions.filter(
            (o) => o.id != slug
        );
    },
};

/**
 * orderAction Store Getters
 */
const getters = {
    /**
     * Get list of orderActions
     *
     * @param {*} state
     * @returns
     */
    orderActions: (state) =>
        state.project && state.project.order_actions
            ? state.project.order_actions
            : [],

    /**
     * Get current orderAction
     *
     * @param {*} state
     * @returns
     */
    orderAction: (state) =>
        state.project && state.project.orderAction
            ? state.project.orderAction
            : null,
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
