import RoleOrderActionService from "@/apis/project/role/order-action";

import {
    FETCH_ROLE_ORDER_ACTIONS,
    SET_ROLE_ORDER_ACTIONS,
    ADD_ROLE_ORDER_ACTION,
    REMOVE_ROLE_ORDER_ACTION,
    BULK_ADD_ROLE_ORDER_ACTION,
    BULK_REMOVE_ROLE_ORDER_ACTION,
} from "@/actions/project/role/order-action";

/**
 * RoleOrderAction Store state
 */
export const state = {
    orderActions: [],
};

/**
 * RoleOrderAction Store Actions
 */
export const actions = {
    /**
     * Fetch role orderActions
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_ROLE_ORDER_ACTIONS](context, params) {
        const { data } = await RoleOrderActionService.get(
            context.state.project.slug,
            context.state.project.role.id,
            params
        );
        context.commit(SET_ROLE_ORDER_ACTIONS, data);
        return data;
    },

    /**
     * Add role orderAction
     *
     * @param {*} context
     * @param {Object} params
     * @returns roleOrderAction
     */
    async [ADD_ROLE_ORDER_ACTION](context, orderAction) {
        context.commit(ADD_ROLE_ORDER_ACTION, orderAction);
        await RoleOrderActionService.update(
            context.state.project.slug,
            context.state.project.role.id,
            orderAction.id
        );
    },

    /**
     * Remove role orderAction
     *
     * @param {*} context
     * @param {Number} params role orderAction id
     * @returns roleOrderAction
     */
    async [REMOVE_ROLE_ORDER_ACTION](context, orderAction) {
        context.commit(REMOVE_ROLE_ORDER_ACTION, orderAction);
        await RoleOrderActionService.destroy(
            context.state.project.slug,
            context.state.project.role.id,
            orderAction.id
        );
    },

    /**
     * Add role orderAction
     *
     * @param {*} context
     * @param {Object} params
     * @returns roleOrderAction
     */
    async [BULK_ADD_ROLE_ORDER_ACTION](context, { roles, orderActions }) {
        await RoleOrderActionService.bulkUpdate(
            context.state.project.slug,
            roles,
            orderActions
        );
    },

    /**
     * Remove role orderAction
     *
     * @param {*} context
     * @param {Number} params role orderAction id
     * @returns roleOrderAction
     */
    async [BULK_REMOVE_ROLE_ORDER_ACTION](context, { roles, orderActions }) {
        await RoleOrderActionService.bulkDestroy(
            context.state.project.slug,
            roles,
            orderActions
        );
    },
};

/**
 * Role orderAction Store Mutations
 */
export const mutations = {
    /**
     * Set role orderActions
     *
     * @param {*} state
     * @returns
     */
    [SET_ROLE_ORDER_ACTIONS](state, orderActions) {
        state.project.role.order_actions = orderActions;
    },

    /**
     * Add role orderAction
     *
     * @param {*} state
     * @param {Number} orderAction to append to role orderActions list
     */
    [ADD_ROLE_ORDER_ACTION](state, orderAction) {
        state.project.role.order_actions = [
            ...(state.project.role.order_actions
                ? state.project.role.order_actions
                : []),
            orderAction,
        ];
        state.project.roles = state.project.roles.map((o) =>
            o.id == state.project.role.id ? { ...o, ...state.project.role } : o
        );
    },

    /**
     * Remove role orderAction
     *
     * @param {*} context
     * @param {Number} params role orderAction id
     */
    [REMOVE_ROLE_ORDER_ACTION](state, orderAction) {
        state.project.role.order_actions = (
            state.project.role.order_actions
                ? state.project.role.order_actions
                : []
        ).filter((o) => o.id != orderAction.id);
        state.project.roles = state.project.roles.map((o) =>
            o.id == state.project.role.id ? { ...o, ...state.project.role } : o
        );
    },
};

/**
 * Role orderAction Store Getters
 */
export const getters = {
    /**
     * Get list of role orderActions
     *
     * @param {*} state
     * @returns
     */
    roleOrderActions: (state) =>
        state.project &&
        state.project.role &&
        state.project.role.order_actions &&
        Array.isArray(state.project.role.order_actions)
            ? state.project.role.order_actions
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
