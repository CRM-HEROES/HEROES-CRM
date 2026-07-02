import RolePermissionService from "@/apis/project/role/permission";

import {
    FETCH_ROLE_PERMISSIONS,
    SET_ROLE_PERMISSIONS,
    ADD_ROLE_PERMISSION,
    REMOVE_ROLE_PERMISSION,
} from "@/actions/project/role/permission";

/**
 * RolePermission Store state
 */
export const state = {
    permissions: [],
};

/**
 * RolePermission Store Actions
 */
export const actions = {
    /**
     * Fetch role permissions
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_ROLE_PERMISSIONS](context, params) {
        const { data } = await RolePermissionService.get(
            context.state.project.slug,
            context.state.project.role.id,
            params
        );
        context.commit(SET_ROLE_PERMISSIONS, data);
        return data;
    },

    /**
     * Add role permission
     *
     * @param {*} context
     * @param {Object} params
     * @returns rolePermission
     */
    async [ADD_ROLE_PERMISSION](context, permission) {
        context.commit(ADD_ROLE_PERMISSION, permission);
        await RolePermissionService.update(
            context.state.project.slug,
            context.state.project.role.id,
            permission
        );
    },

    /**
     * Remove role permission
     *
     * @param {*} context
     * @param {Number} params role permission id
     * @returns rolePermission
     */
    async [REMOVE_ROLE_PERMISSION](context, permission) {
        context.commit(REMOVE_ROLE_PERMISSION, permission);
        await RolePermissionService.destroy(
            context.state.project.slug,
            context.state.project.role.id,
            permission
        );
    },
};

/**
 * Role permission Store Mutations
 */
export const mutations = {
    /**
     * Set role permissions
     *
     * @param {*} state
     * @returns
     */
    [SET_ROLE_PERMISSIONS](state, permissions) {
        state.project.role.permissions = permissions;
    },

    /**
     * Add role permission
     *
     * @param {*} state
     * @param {Number} permission to append to role permissions list
     */
    [ADD_ROLE_PERMISSION](state, permission) {
        state.project.role.permissions = [
            ...(state.project.role.permissions
                ? state.project.role.permissions
                : []),
            permission,
        ];
        state.project.roles = state.project.roles.map((o) =>
            o.id == state.project.role.id ? { ...o, ...state.project.role } : o
        );
    },

    /**
     * Remove role permission
     *
     * @param {*} context
     * @param {Number} params role permission id
     */
    [REMOVE_ROLE_PERMISSION](state, permission) {
        state.project.role.permissions = (
            state.project.role.permissions ? state.project.role.permissions : []
        ).filter((o) => o != permission);
        state.project.roles = state.project.roles.map((o) =>
            o.id == state.project.role.id ? { ...o, ...state.project.role } : o
        );
    },
};

/**
 * Role permission Store Getters
 */
export const getters = {
    /**
     * Get list of role permissions
     *
     * @param {*} state
     * @returns
     */
    rolePermissions: (state) =>
        state.project &&
        state.project.role &&
        state.project.role.permissions &&
        Array.isArray(state.project.role.permissions)
            ? state.project.role.permissions
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
