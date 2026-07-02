import ProspectUserService from "@/apis/project/prospect/user";

import {
    FETCH_PROSPECT_USERS,
    SET_PROSPECT_USERS,
    ADD_PROSPECT_USER,
    REMOVE_PROSPECT_USER,
    BULK_ADD_PROSPECT_USER,
    BULK_REMOVE_PROSPECT_USER,
} from "@/actions/project/prospect/user";

/**
 * ProspectUser Store state
 */
export const state = {
    users: [],
};

/**
 * ProspectUser Store Actions
 */
export const actions = {
    /**
     * Fetch prospect users
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PROSPECT_USERS](context, params) {
        const { data } = await ProspectUserService.get(
            context.state.project.slug,
            context.state.project.prospect.id,
            params
        );
        context.commit(SET_PROSPECT_USERS, data);
        return data;
    },

    /**
     * Add prospect user
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectUser
     */
    async [ADD_PROSPECT_USER](context, user) {
        context.commit(ADD_PROSPECT_USER, user);
        await ProspectUserService.update(
            context.state.project.slug,
            context.state.project.prospect.id,
            user.id
        );
    },

    /**
     * Remove prospect user
     *
     * @param {*} context
     * @param {Number} params prospect user id
     * @returns prospectUser
     */
    async [REMOVE_PROSPECT_USER](context, user) {
        context.commit(REMOVE_PROSPECT_USER, user.id);
        await ProspectUserService.destroy(
            context.state.project.slug,
            context.state.project.prospect.id,
            user.id
        );
    },

    /**
     * Add prospect group
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectGroup
     */
    async [BULK_ADD_PROSPECT_USER](context, { filters, users }) {
        await ProspectUserService.bulkUpdate(
            context.state.project.slug,
            filters,
            users
        );
    },

    /**
     * Remove prospect label
     *
     * @param {*} context
     * @param {Number} params prospect group id
     * @returns prospectGroup
     */
    async [BULK_REMOVE_PROSPECT_USER](context, { filters, users }) {
        await ProspectUserService.bulkDestroy(
            context.state.project.slug,
            filters,
            users
        );
    },
};

/**
 * Prospect user Store Mutations
 */
export const mutations = {
    /**
     * Set prospect users
     *
     * @param {*} state
     * @returns
     */
    [SET_PROSPECT_USERS](state, users) {
        state.project.prospect.users = users;
    },

    /**
     * Add prospect user
     *
     * @param {*} state
     * @param {Number} user to append to prospect users list
     */
    [ADD_PROSPECT_USER](state, user) {
        const users = state.project.prospect.users
            ? state.project.prospect.users
            : [];

        if (!users.find((u) => u.id == user.id)) {
            state.project.prospect.users = [...users, user];

            state.project.prospects = state.project.prospects.map((o) =>
                o.id == state.project.prospect.id
                    ? { ...o, ...state.project.prospect }
                    : o
            );
        }
    },

    /**
     * Remove prospect user
     *
     * @param {*} context
     * @param {Number} params prospect user id
     */
    [REMOVE_PROSPECT_USER](state, slug) {
        state.project.prospect.users = (
            state.project.prospect.users ? state.project.prospect.users : []
        ).filter((o) => o.id != slug);
        state.project.prospects = state.project.prospects.map((o) =>
            o.id == state.project.prospect.id
                ? { ...o, ...state.project.prospect }
                : o
        );
    },
};

/**
 * Prospect user Store Getters
 */
export const getters = {
    /**
     * Get list of prospect users
     *
     * @param {*} state
     * @returns
     */
    prospectUsers: (state) =>
        state.project && state.project.prospect && state.project.prospect.users
            ? state.project.prospect.users
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
