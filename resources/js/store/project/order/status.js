import orderStatusService from "@/apis/project/order/Status";

import {
    FETCH_ORDER_STATUSES,
    SET_ORDER_STATUSES,
    SET_ORDER_STATUS,
    ADD_ORDER_STATUS,
    SHOW_ORDER_STATUS,
    UPDATE_ORDER_STATUS,
    REMOVE_ORDER_STATUS,
} from "@/actions/project/order/status";

/**
 * orderStatus Store state
 */
export const state = {
    orderStatuses: [],
    orderStatus: null,
};

/**
 * orderStatus Store Actions
 */
const actions = {
    /**
     * Fetch orderStatuses
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_ORDER_STATUSES](context, params) {
        const { data } = await orderStatusService.get(
            context.state.project.slug,
            params
        );
        context.commit(SET_ORDER_STATUSES, data);
        return data;
    },

    /**
     * Show orderStatus
     *
     * @param {*} context
     * @param {Number} slug orderStatus id
     * @returns orderStatus
     */
    async [SHOW_ORDER_STATUS](context, slug) {
        const { data } = await orderStatusService.show(
            context.state.project.slug,
            slug
        );
        context.commit(SET_ORDER_STATUS, data);
        return data;
    },

    /**
     * Add orderStatus
     *
     * @param {*} context
     * @param {Object} params orderStatus orderStatus values
     * @returns orderStatus
     */
    async [ADD_ORDER_STATUS](context, params) {
        const { data } = await orderStatusService.create(
            context.state.project.slug,
            params
        );
        context.commit(ADD_ORDER_STATUS, data);
        return data;
    },

    /**
     * update orderStatus
     *
     * @param {*} context
     * @param {Object} params new orderStatus orderStatus values
     */
    async [UPDATE_ORDER_STATUS](context, params) {
        await orderStatusService.update(
            context.state.project.slug,
            params.id,
            params
        );
        context.commit(UPDATE_ORDER_STATUS, params);
    },

    /**
     * remove orderStatus
     *
     * @param {*} context
     * @param {Number} params orderStatus id
     * @returns orderStatus
     */
    async [REMOVE_ORDER_STATUS](context, slug) {
        await orderStatusService.destroy(context.state.project.slug, slug);
        context.commit(REMOVE_ORDER_STATUS, slug);
    },
};

/**
 * orderStatus Store Mutations
 */
const mutations = {
    /**
     * Set orderStatuses
     *
     * @param {*} state
     * @returns
     */
    [SET_ORDER_STATUSES](state, orderStatuses) {
        state.project.order_statuses = [...orderStatuses];
    },

    /**
     * Set current orderStatus
     *
     * @param {*} state
     * @param {Object} orderStatus
     */
    [SET_ORDER_STATUS](state, orderStatus) {
        state.project.orderStatus = orderStatus;
    },

    /**
     * Add orderStatus
     *
     * @param {*} state
     * @param {Number} orderStatus to append to orderStatuses list
     */
    [ADD_ORDER_STATUS](state, orderStatus) {
        state.project.order_statuses = [
            ...state.project.order_statuses,
            orderStatus,
        ];
    },

    /**
     * Update orderStatus
     *
     * @param {*} state
     */
    [UPDATE_ORDER_STATUS](state, params) {
        state.project.order_statuses = state.project.order_statuses.map((o) =>
            o.id == params.id ? { ...o, ...params } : o
        );
    },

    /**
     * remove orderStatus
     *
     * @param {*} context
     * @param {Number} params orderStatus id
     */
    [REMOVE_ORDER_STATUS](state, slug) {
        state.project.order_statuses = state.project.order_statuses.filter(
            (o) => o.id != slug
        );
    },
};

/**
 * orderStatus Store Getters
 */
const getters = {
    /**
     * Get list of orderStatuses
     *
     * @param {*} state
     * @returns
     */
    orderStatuses: (state) =>
        state.project && state.project.order_statuses
            ? state.project.order_statuses
            : [],

    /**
     * Get current orderStatus
     *
     * @param {*} state
     * @returns
     */
    orderStatus: (state) =>
        state.project && state.project.orderStatus
            ? state.project.orderStatus
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
