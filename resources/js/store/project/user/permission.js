import UserPermissionService from "@/apis/project/user/permission";

import {
    FETCH_USER_PERMISSIONS,
    SET_USER_PERMISSIONS,
    ADD_USER_PERMISSION,
    REMOVE_USER_PERMISSION,
} from "@/actions/project/user/permission";

/**
 * UserPermission Store state
 */
export const state = {
    permissions: [],
};

/**
 * UserPermission Store Actions
 */
export const actions = {
    /**
     * Fetch user permissions
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_USER_PERMISSIONS](context, params) {
        const { data } = await UserPermissionService.get(
            context.state.project.slug,
            context.state.project.user.id,
            params
        );
        context.commit(SET_USER_PERMISSIONS, data);
        return data;
    },

    /**
     * Add user permission
     *
     * @param {*} context
     * @param {Object} params
     * @returns userPermission
     */
    async [ADD_USER_PERMISSION](context, permission) {
        context.commit(ADD_USER_PERMISSION, permission);
        await UserPermissionService.update(
            context.state.project.slug,
            context.state.project.user.id,
            permission
        );
    },

    /**
     * Remove user permission
     *
     * @param {*} context
     * @param {Number} params user permission id
     * @returns userPermission
     */
    async [REMOVE_USER_PERMISSION](context, permission) {
        context.commit(REMOVE_USER_PERMISSION, permission);
        await UserPermissionService.destroy(
            context.state.project.slug,
            context.state.project.user.id,
            permission
        );
    },
};

/**
 * User permission Store Mutations
 */
export const mutations = {
    /**
     * Set user permissions
     *
     * @param {*} state
     * @returns
     */
    [SET_USER_PERMISSIONS](state, permissions) {
        state.project.user.permissions = permissions;
    },

    /**
     * Add user permission
     *
     * @param {*} state
     * @param {Number} permission to append to user permissions list
     */
    [ADD_USER_PERMISSION](state, permission) {
        state.project.user.permissions = [
            ...(state.project.user.permissions
                ? state.project.user.permissions
                : []),
            permission,
        ];
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },

    /**
     * Remove user permission
     *
     * @param {*} context
     * @param {Number} params user permission id
     */
    [REMOVE_USER_PERMISSION](state, permission) {
        state.project.user.permissions = (
            state.project.user.permissions ? state.project.user.permissions : []
        ).filter((o) => o != permission);
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },
};

/**
 * User permission Store Getters
 */
export const getters = {
    /**
     * Get list of user permissions
     *
     * @param {*} state
     * @returns
     */
    userPermissions: (state) =>
        state.project &&
        state.project.user &&
        state.project.user.permissions &&
        Array.isArray(state.project.user.permissions)
            ? state.project.user.permissions
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
