import RoleUserService from "@/apis/project/role/user";

import {
    FETCH_ROLE_USERS,
    SET_ROLE_USERS,
    ADD_ROLE_USER,
    REMOVE_ROLE_USER,
    BULK_ADD_ROLE_USER,
    BULK_REMOVE_ROLE_USER,
} from "@/actions/project/role/user";

/**
 * RoleUser Store state
 */
export const state = {
    roles: [],
};

/**
 * RoleUser Store Actions
 */
export const actions = {
    /**
     * Fetch role users
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_ROLE_USERS](context, params) {
        const { data } = await RoleUserService.get(
            context.state.project.slug,
            context.state.project.role.id,
            params
        );
        context.commit(SET_ROLE_USERS, data);
        return data;
    },

    /**
     * Add role user
     *
     * @param {*} context
     * @param {Object} params
     * @returns roleUser
     */
    async [ADD_ROLE_USER](context, user) {
        context.commit(ADD_ROLE_USER, user);
        await RoleUserService.update(
            context.state.project.slug,
            context.state.project.role.id,
            user.id
        );
    },

    /**
     * Remove role user
     *
     * @param {*} context
     * @param {Number} params role user id
     * @returns roleUser
     */
    async [REMOVE_ROLE_USER](context, user) {
        context.commit(REMOVE_ROLE_USER, user);
        await RoleUserService.destroy(
            context.state.project.slug,
            context.state.project.role.id,
            user.id
        );
    },

    /**
     * Add role user
     *
     * @param {*} context
     * @param {Object} params
     * @returns roleUser
     */
    async [BULK_ADD_ROLE_USER](context, { roles, assignableUsers }) {
        await RoleUserService.bulkUpdate(
            context.state.project.slug,
            roles,
            assignableUsers
        );
    },

    /**
     * Remove role user
     *
     * @param {*} context
     * @param {Number} params role user id
     * @returns roleUser
     */
    async [BULK_REMOVE_ROLE_USER](context, { roles, assignableUsers }) {
        await RoleUserService.bulkDestroy(
            context.state.project.slug,
            roles,
            assignableUsers
        );
    },
};

/**
 * Role role Store Mutations
 */
export const mutations = {
    /**
     * Set role users
     *
     * @param {*} state
     * @returns
     */
    [SET_ROLE_USERS](state, roles) {
        state.project.role.assignable_users = roles;
    },

    /**
     * Add role user
     *
     * @param {*} state
     * @param {Number} role to append to role users list
     */
    [ADD_ROLE_USER](state, role) {
        state.project.role.assignable_users = [
            ...(state.project.role.assignable_users
                ? state.project.role.assignable_users
                : []),
            role,
        ];
        state.project.roles = state.project.roles.map((o) =>
            o.id == state.project.role.id ? { ...o, ...state.project.role } : o
        );
    },

    /**
     * Remove role user
     *
     * @param {*} context
     * @param {Number} params role user id
     */
    [REMOVE_ROLE_USER](state, role) {
        state.project.role.assignable_users = (
            state.project.role.assignable_users
                ? state.project.role.assignable_users
                : []
        ).filter((o) => o.id != role.id);
        state.project.roles = state.project.roles.map((o) =>
            o.id == state.project.role.id ? { ...o, ...state.project.role } : o
        );
    },
};

/**
 * Role role Store Getters
 */
export const getters = {
    /**
     * Get list of role users
     *
     * @param {*} state
     * @returns
     */
    roleUsers: (state) =>
        state.project &&
        state.project.role &&
        state.project.role.assignable_users &&
        Array.isArray(state.project.role.assignable_users)
            ? state.project.role.assignable_users
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
