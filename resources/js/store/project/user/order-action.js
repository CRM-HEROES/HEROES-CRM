import UserOrderActionService from "@/apis/project/user/order-action";

import {
    FETCH_USER_ORDER_ACTIONS,
    SET_USER_ORDER_ACTIONS,
    ADD_USER_ORDER_ACTION,
    REMOVE_USER_ORDER_ACTION,
    BULK_ADD_USER_ORDER_ACTION,
    BULK_REMOVE_USER_ORDER_ACTION,
} from "@/actions/project/user/order-action";

/**
 * UserOrderAction Store state
 */
export const state = {
    orderActions: [],
};

/**
 * UserOrderAction Store Actions
 */
export const actions = {
    /**
     * Fetch user orderActions
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_USER_ORDER_ACTIONS](context, params) {
        const { data } = await UserOrderActionService.get(
            context.state.project.slug,
            context.state.project.user.id,
            params
        );
        context.commit(SET_USER_ORDER_ACTIONS, data);
        return data;
    },

    /**
     * Add user orderAction
     *
     * @param {*} context
     * @param {Object} params
     * @returns userOrderAction
     */
    async [ADD_USER_ORDER_ACTION](context, orderAction) {
        context.commit(ADD_USER_ORDER_ACTION, orderAction);
        await UserOrderActionService.update(
            context.state.project.slug,
            context.state.project.user.id,
            orderAction.id
        );
    },

    /**
     * Remove user orderAction
     *
     * @param {*} context
     * @param {Number} params user orderAction id
     * @returns userOrderAction
     */
    async [REMOVE_USER_ORDER_ACTION](context, orderAction) {
        context.commit(REMOVE_USER_ORDER_ACTION, orderAction);
        await UserOrderActionService.destroy(
            context.state.project.slug,
            context.state.project.user.id,
            orderAction.id
        );
    },

    /**
     * Add user orderAction
     *
     * @param {*} context
     * @param {Object} params
     * @returns userOrderAction
     */
    async [BULK_ADD_USER_ORDER_ACTION](context, { users, orderActions }) {
        await UserOrderActionService.bulkUpdate(
            context.state.project.slug,
            users,
            orderActions
        );
    },

    /**
     * Remove user orderAction
     *
     * @param {*} context
     * @param {Number} params user orderAction id
     * @returns userOrderAction
     */
    async [BULK_REMOVE_USER_ORDER_ACTION](context, { users, orderActions }) {
        await UserOrderActionService.bulkDestroy(
            context.state.project.slug,
            users,
            orderActions
        );
    },
};

/**
 * User orderAction Store Mutations
 */
export const mutations = {
    /**
     * Set user orderActions
     *
     * @param {*} state
     * @returns
     */
    [SET_USER_ORDER_ACTIONS](state, orderActions) {
        state.project.user.order_actions = orderActions;
    },

    /**
     * Add user orderAction
     *
     * @param {*} state
     * @param {Number} orderAction to append to user orderActions list
     */
    [ADD_USER_ORDER_ACTION](state, orderAction) {
        state.project.user.order_actions = [
            ...(state.project.user.order_actions
                ? state.project.user.order_actions
                : []),
            orderAction,
        ];
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },

    /**
     * Remove user orderAction
     *
     * @param {*} context
     * @param {Number} params user orderAction id
     */
    [REMOVE_USER_ORDER_ACTION](state, orderAction) {
        state.project.user.order_actions = (
            state.project.user.order_actions
                ? state.project.user.order_actions
                : []
        ).filter((o) => o.id != orderAction.id);
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },
};

/**
 * User orderAction Store Getters
 */
export const getters = {
    /**
     * Get list of user orderActions
     *
     * @param {*} state
     * @returns
     */
    userOrderActions: (state) =>
        state.project &&
        state.project.user &&
        state.project.user.order_actions &&
        Array.isArray(state.project.user.order_actions)
            ? state.project.user.order_actions
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
