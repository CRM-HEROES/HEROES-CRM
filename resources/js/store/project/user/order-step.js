import UserOrderStepService from "@/apis/project/user/order-step";

import {
    FETCH_USER_ORDER_STEPS,
    SET_USER_ORDER_STEPS,
    ADD_USER_ORDER_STEP,
    REMOVE_USER_ORDER_STEP,
    BULK_ADD_USER_ORDER_STEP,
    BULK_REMOVE_USER_ORDER_STEP,
} from "@/actions/project/user/order-step";

/**
 * UserOrderStep Store state
 */
export const state = {
    orderSteps: [],
};

/**
 * UserOrderStep Store Actions
 */
export const actions = {
    /**
     * Fetch user orderSteps
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_USER_ORDER_STEPS](context, params) {
        const { data } = await UserOrderStepService.get(
            context.state.project.slug,
            context.state.project.user.id,
            params
        );
        context.commit(SET_USER_ORDER_STEPS, data);
        return data;
    },

    /**
     * Add user orderStep
     *
     * @param {*} context
     * @param {Object} params
     * @returns userOrderStep
     */
    async [ADD_USER_ORDER_STEP](context, orderStep) {
        context.commit(ADD_USER_ORDER_STEP, orderStep);
        await UserOrderStepService.update(
            context.state.project.slug,
            context.state.project.user.id,
            orderStep.id
        );
    },

    /**
     * Remove user orderStep
     *
     * @param {*} context
     * @param {Number} params user orderStep id
     * @returns userOrderStep
     */
    async [REMOVE_USER_ORDER_STEP](context, orderStep) {
        context.commit(REMOVE_USER_ORDER_STEP, orderStep);
        await UserOrderStepService.destroy(
            context.state.project.slug,
            context.state.project.user.id,
            orderStep.id
        );
    },

    /**
     * Add user orderStep
     *
     * @param {*} context
     * @param {Object} params
     * @returns userOrderStep
     */
    async [BULK_ADD_USER_ORDER_STEP](context, { users, orderSteps }) {
        await UserOrderStepService.bulkUpdate(
            context.state.project.slug,
            users,
            orderSteps
        );
    },

    /**
     * Remove user orderStep
     *
     * @param {*} context
     * @param {Number} params user orderStep id
     * @returns userOrderStep
     */
    async [BULK_REMOVE_USER_ORDER_STEP](context, { users, orderSteps }) {
        await UserOrderStepService.bulkDestroy(
            context.state.project.slug,
            users,
            orderSteps
        );
    },
};

/**
 * User orderStep Store Mutations
 */
export const mutations = {
    /**
     * Set user orderSteps
     *
     * @param {*} state
     * @returns
     */
    [SET_USER_ORDER_STEPS](state, orderSteps) {
        state.project.user.order_steps = orderSteps;
    },

    /**
     * Add user orderStep
     *
     * @param {*} state
     * @param {Number} orderStep to append to user orderSteps list
     */
    [ADD_USER_ORDER_STEP](state, orderStep) {
        state.project.user.order_steps = [
            ...(state.project.user.order_steps
                ? state.project.user.order_steps
                : []),
            orderStep,
        ];
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },

    /**
     * Remove user orderStep
     *
     * @param {*} context
     * @param {Number} params user orderStep id
     */
    [REMOVE_USER_ORDER_STEP](state, orderStep) {
        state.project.user.order_steps = (
            state.project.user.order_steps ? state.project.user.order_steps : []
        ).filter((o) => o.id != orderStep.id);
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },
};

/**
 * User orderStep Store Getters
 */
export const getters = {
    /**
     * Get list of user orderSteps
     *
     * @param {*} state
     * @returns
     */
    userOrderSteps: (state) =>
        state.project &&
        state.project.user &&
        state.project.user.order_steps &&
        Array.isArray(state.project.user.order_steps)
            ? state.project.user.order_steps
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
