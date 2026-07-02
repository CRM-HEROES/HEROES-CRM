import UserRoleService from "@/apis/project/user/role";

import {
    FETCH_USER_ROLES,
    SET_USER_ROLES,
    ADD_USER_ROLE,
    REMOVE_USER_ROLE,
    BULK_ADD_USER_ROLE,
    BULK_REMOVE_USER_ROLE,
} from "@/actions/project/user/role";

/**
 * UserRole Store state
 */
export const state = {
    roles: [],
};

/**
 * UserRole Store Actions
 */
export const actions = {
    /**
     * Fetch user roles
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_USER_ROLES](context, params) {
        const { data } = await UserRoleService.get(
            context.state.project.slug,
            context.state.project.user.id,
            params
        );
        context.commit(SET_USER_ROLES, data);
        return data;
    },

    /**
     * Add user role
     *
     * @param {*} context
     * @param {Object} params
     * @returns userRole
     */
    async [ADD_USER_ROLE](context, role) {
        context.commit(ADD_USER_ROLE, role);
        await UserRoleService.update(
            context.state.project.slug,
            context.state.project.user.id,
            role.id
        );
    },

    /**
     * Remove user role
     *
     * @param {*} context
     * @param {Number} params user role id
     * @returns userRole
     */
    async [REMOVE_USER_ROLE](context, role) {
        context.commit(REMOVE_USER_ROLE, role);
        await UserRoleService.destroy(
            context.state.project.slug,
            context.state.project.user.id,
            role.id
        );
    },

    /**
     * Add user role
     *
     * @param {*} context
     * @param {Object} params
     * @returns userRole
     */
    async [BULK_ADD_USER_ROLE](context, { users, roles }) {
        await UserRoleService.bulkUpdate(
            context.state.project.slug,
            users,
            roles
        );
    },

    /**
     * Remove user role
     *
     * @param {*} context
     * @param {Number} params user role id
     * @returns userRole
     */
    async [BULK_REMOVE_USER_ROLE](context, { users, roles }) {
        await UserRoleService.bulkDestroy(
            context.state.project.slug,
            users,
            roles
        );
    },
};

/**
 * User role Store Mutations
 */
export const mutations = {
    /**
     * Set user roles
     *
     * @param {*} state
     * @returns
     */
    [SET_USER_ROLES](state, roles) {
        state.project.user.roles = roles;
    },

    /**
     * Add user role
     *
     * @param {*} state
     * @param {Number} role to append to user roles list
     */
    [ADD_USER_ROLE](state, role) {
        state.project.user.roles = [
            ...(state.project.user.roles ? state.project.user.roles : []),
            role,
        ];
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },

    /**
     * Remove user role
     *
     * @param {*} context
     * @param {Number} params user role id
     */
    [REMOVE_USER_ROLE](state, role) {
        state.project.user.roles = (
            state.project.user.roles ? state.project.user.roles : []
        ).filter((o) => o.id != role.id);
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },
};

/**
 * User role Store Getters
 */
export const getters = {
    /**
     * Get list of user roles
     *
     * @param {*} state
     * @returns
     */
    userRoles: (state) =>
        state.project &&
        state.project.user &&
        state.project.user.roles &&
        Array.isArray(state.project.user.roles)
            ? state.project.user.roles
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
