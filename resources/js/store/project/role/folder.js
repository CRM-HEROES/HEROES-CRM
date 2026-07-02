import RoleFolderService from "@/apis/project/role/folder";

import {
    FETCH_ROLE_FOLDERS,
    SET_ROLE_FOLDERS,
    ADD_ROLE_FOLDER,
    REMOVE_ROLE_FOLDER,
    BULK_ADD_ROLE_FOLDER,
    BULK_REMOVE_ROLE_FOLDER,
} from "@/actions/project/role/folder";

/**
 * RoleFolder Store state
 */
export const state = {
    folders: [],
};

/**
 * RoleFolder Store Actions
 */
export const actions = {
    /**
     * Fetch role folders
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_ROLE_FOLDERS](context, params) {
        const { data } = await RoleFolderService.get(
            context.state.project.slug,
            context.state.project.role.id,
            params
        );
        context.commit(SET_ROLE_FOLDERS, data);
        return data;
    },

    /**
     * Add role folder
     *
     * @param {*} context
     * @param {Object} params
     * @returns roleFolder
     */
    async [ADD_ROLE_FOLDER](context, folder) {
        context.commit(ADD_ROLE_FOLDER, folder);
        await RoleFolderService.update(
            context.state.project.slug,
            context.state.project.role.id,
            folder.id
        );
    },

    /**
     * Remove role folder
     *
     * @param {*} context
     * @param {Number} params role folder id
     * @returns roleFolder
     */
    async [REMOVE_ROLE_FOLDER](context, folder) {
        context.commit(REMOVE_ROLE_FOLDER, folder);
        await RoleFolderService.destroy(
            context.state.project.slug,
            context.state.project.role.id,
            folder.id
        );
    },

    /**
     * Add role folder
     *
     * @param {*} context
     * @param {Object} params
     * @returns roleFolder
     */
    async [BULK_ADD_ROLE_FOLDER](context, { roles, folders }) {
        await RoleFolderService.bulkUpdate(
            context.state.project.slug,
            roles,
            folders
        );
    },

    /**
     * Remove role folder
     *
     * @param {*} context
     * @param {Number} params role folder id
     * @returns roleFolder
     */
    async [BULK_REMOVE_ROLE_FOLDER](context, { roles, folders }) {
        await RoleFolderService.bulkDestroy(
            context.state.project.slug,
            roles,
            folders
        );
    },
};

/**
 * Role folder Store Mutations
 */
export const mutations = {
    /**
     * Set role folders
     *
     * @param {*} state
     * @returns
     */
    [SET_ROLE_FOLDERS](state, folders) {
        state.project.role.folders = folders;
    },

    /**
     * Add role folder
     *
     * @param {*} state
     * @param {Number} folder to append to role folders list
     */
    [ADD_ROLE_FOLDER](state, folder) {
        state.project.role.folders = [
            ...(state.project.role.folders ? state.project.role.folders : []),
            folder,
        ];
        state.project.roles = state.project.roles.map((o) =>
            o.id == state.project.role.id ? { ...o, ...state.project.role } : o
        );
    },

    /**
     * Remove role folder
     *
     * @param {*} context
     * @param {Number} params role folder id
     */
    [REMOVE_ROLE_FOLDER](state, folder) {
        state.project.role.folders = (
            state.project.role.folders ? state.project.role.folders : []
        ).filter((o) => o.id != folder.id);
        state.project.roles = state.project.roles.map((o) =>
            o.id == state.project.role.id ? { ...o, ...state.project.role } : o
        );
    },
};

/**
 * Role folder Store Getters
 */
export const getters = {
    /**
     * Get list of role folders
     *
     * @param {*} state
     * @returns
     */
    roleFolders: (state) =>
        state.project &&
        state.project.role &&
        state.project.role.folders &&
        Array.isArray(state.project.role.folders)
            ? state.project.role.folders
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
