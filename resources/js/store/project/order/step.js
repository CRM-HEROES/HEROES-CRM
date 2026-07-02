import OrderStepService from "@/apis/project/order/step";

import {
    FETCH_ORDER_STEPS,
    SET_ORDER_STEPS,
    SET_ORDER_STEP,
    ADD_ORDER_STEP,
    SHOW_ORDER_STEP,
    UPDATE_ORDER_STEP,
    REMOVE_ORDER_STEP,
} from "@/actions/project/order/step";

/**
 * orderStep Store state
 */
export const state = {
    order_steps: [],
    orderStep: null,
};

/**
 * orderStep Store Actions
 */
const actions = {
    /**
     * Fetch orderSteps
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_ORDER_STEPS](context, params) {
        const { data } = await OrderStepService.get(
            context.state.project.slug,
            params
        );
        context.commit(SET_ORDER_STEPS, data);
        return data;
    },

    /**
     * Show orderStep
     *
     * @param {*} context
     * @param {Number} slug orderStep id
     * @returns orderStep
     */
    async [SHOW_ORDER_STEP](context, slug) {
        const { data } = await OrderStepService.show(
            context.state.project.slug,
            slug
        );
        context.commit(SET_ORDER_STEP, data);
        return data;
    },

    /**
     * Add orderStep
     *
     * @param {*} context
     * @param {Object} params orderStep orderStep values
     * @returns orderStep
     */
    async [ADD_ORDER_STEP](context, params) {
        const { data } = await OrderStepService.create(
            context.state.project.slug,
            params
        );
        context.commit(ADD_ORDER_STEP, data);
        return data;
    },

    /**
     * update orderStep
     *
     * @param {*} context
     * @param {Object} params new orderStep orderStep values
     */
    async [UPDATE_ORDER_STEP](context, params) {
        await OrderStepService.update(
            context.state.project.slug,
            params.id,
            params
        );
        context.commit(UPDATE_ORDER_STEP, params);
    },

    /**
     * remove orderStep
     *
     * @param {*} context
     * @param {Number} params orderStep id
     * @returns orderStep
     */
    async [REMOVE_ORDER_STEP](context, slug) {
        await OrderStepService.destroy(context.state.project.slug, slug);
        context.commit(REMOVE_ORDER_STEP, slug);
    },
};

/**
 * orderStep Store Mutations
 */
const mutations = {
    /**
     * Set orderSteps
     *
     * @param {*} state
     * @returns
     */
    [SET_ORDER_STEPS](state, orderSteps) {
        state.project.order_steps = [...orderSteps];
    },

    /**
     * Set current orderStep
     *
     * @param {*} state
     * @param {Object} orderStep
     */
    [SET_ORDER_STEP](state, orderStep) {
        state.project.orderStep = orderStep;
    },

    /**
     * Add orderStep
     *
     * @param {*} state
     * @param {Number} orderStep to append to orderSteps list
     */
    [ADD_ORDER_STEP](state, orderStep) {
        state.project.order_steps = [
            ...(state.project.order_steps ? state.project.order_steps : []),
            orderStep,
        ];
    },

    /**
     * Update orderStep
     *
     * @param {*} state
     */
    [UPDATE_ORDER_STEP](state, params) {
        state.project.order_steps = state.project.order_steps.map((o) =>
            o.id == params.id ? { ...o, ...params } : o
        );
    },

    /**
     * remove orderStep
     *
     * @param {*} context
     * @param {Number} params orderStep id
     */
    [REMOVE_ORDER_STEP](state, slug) {
        state.project.order_steps = state.project.order_steps.filter(
            (o) => o.id != slug
        );
    },
};

/**
 * orderStep Store Getters
 */
const getters = {
    /**
     * Get list of orderSteps
     *
     * @param {*} state
     * @returns
     */
    orderSteps: (state) => {
        return state.project && state.project.order_steps
        ? state.project.order_steps
        : []
    }
        ,

    /**
     * Get current orderStep
     *
     * @param {*} state
     * @returns
     */
    orderStep: (state) =>
        state.project && state.project.orderStep
            ? state.project.orderStep
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
