import UserActionService from "@/apis/project/user/action";

import {
    FETCH_USER_ACTIONS,
    SET_USER_ACTIONS,
} from "@/actions/project/user/action";

/**
 * UserAction Store state
 */
export const state = {
    actions: [],
};

/**
 * UserAction Store Actions
 */
export const actions = {
    /**
     * Fetch user actions
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_USER_ACTIONS](context, params) {
        const { data } = await UserActionService.get(
            context.state.project.slug,
            context.state.project.user.id,
            params
        );
        context.commit(SET_USER_ACTIONS, data);
        return data;
    },
};

/**
 * User action Store Mutations
 */
export const mutations = {
    /**
     * Set user actions
     *
     * @param {*} state
     * @returns
     */
    [SET_USER_ACTIONS](state, actions) {
        state.project.user.actions = actions;
    },
};

/**
 * User action Store Getters
 */
export const getters = {
    /**
     * Get list of user actions
     *
     * @param {*} state
     * @returns
     */
    userActions: (state) =>
        state.project && state.project.user && state.project.user.actions
            ? state.project.user.actions
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
