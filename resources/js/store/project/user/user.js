import UserUserService from "@/apis/project/user/user";

import {
    FETCH_USER_USERS,
    SET_USER_USERS,
    ADD_USER_USER,
    REMOVE_USER_USER,
    BULK_ADD_USER_USER,
    BULK_REMOVE_USER_USER,
} from "@/actions/project/user/user";

/**
 * UserUser Store state
 */
export const state = {
    users: [],
};

/**
 * UserUser Store Actions
 */
export const actions = {
    /**
     * Fetch user users
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_USER_USERS](context, params) {
        const { data } = await UserUserService.get(
            context.state.project.slug,
            context.state.project.user.id,
            params
        );
        context.commit(SET_USER_USERS, data);
        return data;
    },

    /**
     * Add user user
     *
     * @param {*} context
     * @param {Object} params
     * @returns userUser
     */
    async [ADD_USER_USER](context, user) {
        context.commit(ADD_USER_USER, user);
        await UserUserService.update(
            context.state.project.slug,
            context.state.project.user.id,
            user.id
        );
    },

    /**
     * Remove user user
     *
     * @param {*} context
     * @param {Number} params user user id
     * @returns userUser
     */
    async [REMOVE_USER_USER](context, user) {
        context.commit(REMOVE_USER_USER, user);
        await UserUserService.destroy(
            context.state.project.slug,
            context.state.project.user.id,
            user.id
        );
    },

    /**
     * Add user user
     *
     * @param {*} context
     * @param {Object} params
     * @returns userUser
     */
    async [BULK_ADD_USER_USER](context, { users, assignableUsers }) {
        await UserUserService.bulkUpdate(
            context.state.project.slug,
            users,
            assignableUsers
        );
    },

    /**
     * Remove user user
     *
     * @param {*} context
     * @param {Number} params user user id
     * @returns userUser
     */
    async [BULK_REMOVE_USER_USER](context, { users, assignableUsers }) {
        await UserUserService.bulkDestroy(
            context.state.project.slug,
            users,
            assignableUsers
        );
    },
};

/**
 * User user Store Mutations
 */
export const mutations = {
    /**
     * Set user users
     *
     * @param {*} state
     * @returns
     */
    [SET_USER_USERS](state, users) {
        state.project.user.assignable_users = users;
    },

    /**
     * Add user user
     *
     * @param {*} state
     * @param {Number} user to append to user users list
     */
    [ADD_USER_USER](state, user) {
        state.project.user.assignable_users = [
            ...(state.project.user.assignable_users
                ? state.project.user.assignable_users
                : []),
            user,
        ];
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },

    /**
     * Remove user user
     *
     * @param {*} context
     * @param {Number} params user user id
     */
    [REMOVE_USER_USER](state, user) {
        state.project.user.assignable_users = (
            state.project.user.assignable_users
                ? state.project.user.assignable_users
                : []
        ).filter((o) => o.id != user.id);
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },
};

/**
 * User user Store Getters
 */
export const getters = {
    /**
     * Get list of user users
     *
     * @param {*} state
     * @returns
     */
    userUsers: (state) =>
        state.project &&
        state.project.user &&
        state.project.user.assignable_users &&
        Array.isArray(state.project.user.assignable_users)
            ? state.project.user.assignable_users
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
