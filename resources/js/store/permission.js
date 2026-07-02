import PermissionService from "@/apis/permission";

import { FETCH_PERMISSIONS, SET_PERMISSIONS } from "@/actions/permission";

/**
 * Permission Store state
 */
const state = {
    permissions: [],
};

/**
 * Permission Store Actions
 */
const actions = {
    /**
     * Fetch documents
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PERMISSIONS](context, project) {
        const { data } = await PermissionService.get(
            project ? project.slug : null
        );
        context.commit(SET_PERMISSIONS, data);
        return data;
    },
};

/**
 * Permission Store Mutations
 */
const mutations = {
    /**
     * Set current user permissions
     *
     * @param {*} state
     * @returns
     */
    [SET_PERMISSIONS](state, permissions) {
        state.permissions = permissions;
    },
};

/**
 * Permission Store Getters
 */
const getters = {
    /**
     * Get permissions
     *
     * @param {*} state
     * @returns
     */
    permissions: (state) =>
        state.permissions && Array.isArray(state.permissions)
            ? state.permissions
            : [],

    superAdmin: (state, getters, rootState) => {
        return (
            rootState.auth &&
            rootState.auth.user &&
            rootState.auth.user.is_super_admin
        );
    },

    canAll: (state, getters, rootState) => {
        return (
            rootState.auth &&
            rootState.auth.user &&
            (rootState.auth.user.is_super_admin ||
                (rootState.project &&
                    rootState.project.project &&
                    rootState.project.project.creator_id ==
                        rootState.auth.user.id))
        );
    },

    can: (state, getters, rootState) => (permission) => {
        if (getters.canAll) {
            return true;
        }

        while (true) {
            if (getters.permissions.indexOf(permission) >= 0) {
                return true;
            }

            const lastDotIndex = permission.lastIndexOf(".");

            if (lastDotIndex < 0) {
                return false;
            }

            permission = permission.substring(0, lastDotIndex);
        }
    },

    canMessage: (state, getters, rootState) => {
        if (rootState.auth && rootState.auth.user) {
            if (
                rootState.auth.user.is_super_admin ||
                rootState.auth.user.is_project_admin
            ) {
                return true;
            }

            if (getters.threads.length > 0) {
                return true;
            }
        }

        return false;
    },

    canFile: (state, getters, rootState) => {
        if (rootState.auth && rootState.auth.user) {
            if (
                rootState.auth.user.is_super_admin ||
                rootState.auth.user.is_project_admin
            ) {
                return true;
            }

            if (getters.folders.length > 0) {
                return true;
            }
        }

        return false;
    },

    canEvent: (state, getters, rootState) => {
        if (rootState.auth && rootState.auth.user) {
            if (
                rootState.auth.user.is_super_admin ||
                rootState.auth.user.is_project_admin
            ) {
                return true;
            }

            if (getters.calendars.length > 0) {
                return true;
            }
        }

        return false;
    },

    canGroup: (state, getters, rootState) => {
        if (rootState.auth && rootState.auth.user) {
            if (
                rootState.auth.user.is_super_admin ||
                rootState.auth.user.is_project_admin
            ) {
                return true;
            }

            if (getters.groups.length > 0) {
                return true;
            }
        }

        return false;
    },

    canUser: (state, getters, rootState) => {
        if (rootState.auth && rootState.auth.user) {
            if (
                rootState.auth.user.is_super_admin ||
                rootState.auth.user.is_project_admin
            ) {
                return true;
            }

            if (getters.users.length > 0) {
                return true;
            }
        }

        return false;
    },

    canCategory: (state, getters, rootState) => (category) => {
        if (rootState.auth && rootState.auth.user) {
            if (
                rootState.auth.user.is_super_admin ||
                rootState.auth.user.is_project_admin
            ) {
                return true;
            }

            if (getters.categories.find((c) => c.id == category.id)) {
                return true;
            }
        }

        return false;
    },
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
