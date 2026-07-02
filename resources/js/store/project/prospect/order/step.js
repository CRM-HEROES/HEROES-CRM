import OrderStepService from "@/apis/project/prospect/order/step";

import {
    FETCH_PROSPECT_ORDER_STEPS,
    SET_PROSPECT_ORDER_STEPS,
    ADD_PROSPECT_ORDER_STEP,
    REMOVE_PROSPECT_ORDER_STEP,
} from "@/actions/project/prospect/order/step";

/**
 * OrderStep Store state
 */
export const state = {
    orderSteps: [],
};

/**
 * OrderStep Store Steps
 */
export const actions = {
    /**
     * Fetch order steps
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PROSPECT_ORDER_STEPS](context, params) {
        const { data } = await OrderStepService.get(
            context.state.project.slug,
            context.state.project.prospect.id,
            context.state.project.prospect.order.id,
            params
        );
        context.commit(SET_PROSPECT_ORDER_STEPS, data);
        return data;
    },

    /**
     * Add order step
     *
     * @param {*} context
     * @param {Object} params
     * @returns orderStep
     */
    async [ADD_PROSPECT_ORDER_STEP](context, step) {
        context.commit(ADD_PROSPECT_ORDER_STEP, step);
        await OrderStepService.update(
            context.state.project.slug,
            context.state.project.prospect.id,
            context.state.project.prospect.order.id,
            step.id
        );
    },

    /**
     * Remove order step
     *
     * @param {*} context
     * @param {Number} params order step id
     * @returns orderStep
     */
    async [REMOVE_PROSPECT_ORDER_STEP](context, step) {
        context.commit(REMOVE_PROSPECT_ORDER_STEP, step);
        await OrderStepService.destroy(
            context.state.project.slug,
            context.state.project.prospect.id,
            context.state.project.prospect.order.id,
            step.id
        );
    },
};

/**
 * Order step Store Mutations
 */
export const mutations = {
    /**
     * Set order steps
     *
     * @param {*} state
     * @returns
     */
    [SET_PROSPECT_ORDER_STEPS](state, steps) {
        state.project.prospect.order = {
            ...state.project.prospect.order,
            steps: [...steps],
        };
    },

    /**
     * Add order step
     *
     * @param {*} state
     * @param {Number} step to append to order steps list
     */
    [ADD_PROSPECT_ORDER_STEP](state, step) {
        state.project.prospect.order = {
            ...state.project.prospect.order,
            steps: [
                ...(state.project.prospect.order.steps
                    ? state.project.prospect.order.steps
                    : []),
                step,
            ],
        };

        state.project.orders = (
            state.project.orders && Array.isArray(state.project.orders)
                ? state.project.orders
                : []
        ).map((o) =>
            o.id == state.project.prospect.order.id
                ? { ...o, ...state.project.prospect.order }
                : o
        );
    },

    /**
     * Remove order step
     *
     * @param {*} context
     * @param {Number} params order step id
     */
    [REMOVE_PROSPECT_ORDER_STEP](state, step) {
        state.project.prospect.order = {
            ...state.project.prospect.order,
            steps: (state.project.prospect.order.steps
                ? state.project.prospect.order.steps
                : []
            ).filter((o) => o.id != step.id),
        };

        state.project.orders = (
            state.project.orders && Array.isArray(state.project.orders)
                ? state.project.orders
                : []
        ).map((o) =>
            o.id == state.project.prospect.order.id
                ? { ...o, ...state.project.prospect.order }
                : o
        );
    },
};

/**
 * Order step Store Getters
 */
export const getters = {
    /**
     * Get list of order steps
     *
     * @param {*} state
     * @returns
     */
    prospectOrderSteps: (state) =>
        state.project &&
        state.project.prospect &&
        state.project.prospect.order &&
        state.project.prospect.order.steps
            ? state.project.prospect.order.steps
            : [],
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
