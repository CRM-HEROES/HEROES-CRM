import UserGroupService from "@/apis/project/user/group";

import {
    FETCH_USER_GROUPS,
    SET_USER_GROUPS,
    ADD_USER_GROUP,
    REMOVE_USER_GROUP,
    BULK_ADD_USER_GROUP,
    BULK_REMOVE_USER_GROUP,
} from "@/actions/project/user/group";

/**
 * UserGroup Store state
 */
export const state = {
    groups: [],
};

/**
 * UserGroup Store Actions
 */
export const actions = {
    /**
     * Fetch user groups
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_USER_GROUPS](context, params) {
        const { data } = await UserGroupService.get(
            context.state.project.slug,
            context.state.project.user.id,
            params
        );
        context.commit(SET_USER_GROUPS, data);
        return data;
    },

    /**
     * Add user group
     *
     * @param {*} context
     * @param {Object} params
     * @returns userGroup
     */
    async [ADD_USER_GROUP](context, group) {
        context.commit(ADD_USER_GROUP, group);
        await UserGroupService.update(
            context.state.project.slug,
            context.state.project.user.id,
            group.id
        );
    },

    /**
     * Remove user group
     *
     * @param {*} context
     * @param {Number} params user group id
     * @returns userGroup
     */
    async [REMOVE_USER_GROUP](context, group) {
        context.commit(REMOVE_USER_GROUP, group);
        await UserGroupService.destroy(
            context.state.project.slug,
            context.state.project.user.id,
            group.id
        );
    },

    /**
     * Add user group
     *
     * @param {*} context
     * @param {Object} params
     * @returns userGroup
     */
    async [BULK_ADD_USER_GROUP](context, { users, groups }) {
        await UserGroupService.bulkUpdate(
            context.state.project.slug,
            users,
            groups
        );
    },

    /**
     * Remove user group
     *
     * @param {*} context
     * @param {Number} params user group id
     * @returns userGroup
     */
    async [BULK_REMOVE_USER_GROUP](context, { users, groups }) {
        await UserGroupService.bulkDestroy(
            context.state.project.slug,
            users,
            groups
        );
    },
};

/**
 * User group Store Mutations
 */
export const mutations = {
    /**
     * Set user groups
     *
     * @param {*} state
     * @returns
     */
    [SET_USER_GROUPS](state, groups) {
        state.project.user.groups = groups;
    },

    /**
     * Add user group
     *
     * @param {*} state
     * @param {Number} group to append to user groups list
     */
    [ADD_USER_GROUP](state, group) {
        state.project.user.groups = [
            ...(state.project.user.groups ? state.project.user.groups : []),
            group,
        ];
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },

    /**
     * Remove user group
     *
     * @param {*} context
     * @param {Number} params user group id
     */
    [REMOVE_USER_GROUP](state, group) {
        state.project.user.groups = (
            state.project.user.groups ? state.project.user.groups : []
        ).filter((o) => o.id != group.id);
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },
};

/**
 * User group Store Getters
 */
export const getters = {
    /**
     * Get list of user groups
     *
     * @param {*} state
     * @returns
     */
    userGroups: (state) =>
        state.project &&
        state.project.user &&
        state.project.user.groups &&
        Array.isArray(state.project.user.groups)
            ? state.project.user.groups
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
