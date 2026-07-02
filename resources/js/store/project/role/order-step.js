import RoleOrderStepService from "@/apis/project/role/order-step";

import {
    FETCH_ROLE_ORDER_STEPS,
    SET_ROLE_ORDER_STEPS,
    ADD_ROLE_ORDER_STEP,
    REMOVE_ROLE_ORDER_STEP,
    BULK_ADD_ROLE_ORDER_STEP,
    BULK_REMOVE_ROLE_ORDER_STEP,
} from "@/actions/project/role/order-step";

/**
 * RoleOrderStep Store state
 */
export const state = {
    orderSteps: [],
};

/**
 * RoleOrderStep Store Actions
 */
export const actions = {
    /**
     * Fetch role orderSteps
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_ROLE_ORDER_STEPS](context, params) {
        const { data } = await RoleOrderStepService.get(
            context.state.project.slug,
            context.state.project.role.id,
            params
        );
        context.commit(SET_ROLE_ORDER_STEPS, data);
        return data;
    },

    /**
     * Add role orderStep
     *
     * @param {*} context
     * @param {Object} params
     * @returns roleOrderStep
     */
    async [ADD_ROLE_ORDER_STEP](context, orderStep) {
        context.commit(ADD_ROLE_ORDER_STEP, orderStep);
        await RoleOrderStepService.update(
            context.state.project.slug,
            context.state.project.role.id,
            orderStep.id
        );
    },

    /**
     * Remove role orderStep
     *
     * @param {*} context
     * @param {Number} params role orderStep id
     * @returns roleOrderStep
     */
    async [REMOVE_ROLE_ORDER_STEP](context, orderStep) {
        context.commit(REMOVE_ROLE_ORDER_STEP, orderStep);
        await RoleOrderStepService.destroy(
            context.state.project.slug,
            context.state.project.role.id,
            orderStep.id
        );
    },

    /**
     * Add role orderStep
     *
     * @param {*} context
     * @param {Object} params
     * @returns roleOrderStep
     */
    async [BULK_ADD_ROLE_ORDER_STEP](context, { roles, orderSteps }) {
        await RoleOrderStepService.bulkUpdate(
            context.state.project.slug,
            roles,
            orderSteps
        );
    },

    /**
     * Remove role orderStep
     *
     * @param {*} context
     * @param {Number} params role orderStep id
     * @returns roleOrderStep
     */
    async [BULK_REMOVE_ROLE_ORDER_STEP](context, { roles, orderSteps }) {
        await RoleOrderStepService.bulkDestroy(
            context.state.project.slug,
            roles,
            orderSteps
        );
    },
};

/**
 * Role orderStep Store Mutations
 */
export const mutations = {
    /**
     * Set role orderSteps
     *
     * @param {*} state
     * @returns
     */
    [SET_ROLE_ORDER_STEPS](state, orderSteps) {
        state.project.role.order_steps = orderSteps;
    },

    /**
     * Add role orderStep
     *
     * @param {*} state
     * @param {Number} orderStep to append to role orderSteps list
     */
    [ADD_ROLE_ORDER_STEP](state, orderStep) {
        state.project.role.order_steps = [
            ...(state.project.role.order_steps
                ? state.project.role.order_steps
                : []),
            orderStep,
        ];
        state.project.roles = state.project.roles.map((o) =>
            o.id == state.project.role.id ? { ...o, ...state.project.role } : o
        );
    },

    /**
     * Remove role orderStep
     *
     * @param {*} context
     * @param {Number} params role orderStep id
     */
    [REMOVE_ROLE_ORDER_STEP](state, orderStep) {
        state.project.role.order_steps = (
            state.project.role.order_steps ? state.project.role.order_steps : []
        ).filter((o) => o.id != orderStep.id);
        state.project.roles = state.project.roles.map((o) =>
            o.id == state.project.role.id ? { ...o, ...state.project.role } : o
        );
    },
};

/**
 * Role orderStep Store Getters
 */
export const getters = {
    /**
     * Get list of role orderSteps
     *
     * @param {*} state
     * @returns
     */
    roleOrderSteps: (state) =>
        state.project &&
        state.project.role &&
        state.project.role.order_steps &&
        Array.isArray(state.project.role.order_steps)
            ? state.project.role.order_steps
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
