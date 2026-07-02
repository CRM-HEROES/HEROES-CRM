import UserFolderService from "@/apis/project/user/folder";

import {
    FETCH_USER_FOLDERS,
    SET_USER_FOLDERS,
    ADD_USER_FOLDER,
    REMOVE_USER_FOLDER,
    BULK_ADD_USER_FOLDER,
    BULK_REMOVE_USER_FOLDER,
} from "@/actions/project/user/folder";

/**
 * UserFolder Store state
 */
export const state = {
    folders: [],
};

/**
 * UserFolder Store Actions
 */
export const actions = {
    /**
     * Fetch user folders
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_USER_FOLDERS](context, params) {
        const { data } = await UserFolderService.get(
            context.state.project.slug,
            context.state.project.user.id,
            params
        );
        context.commit(SET_USER_FOLDERS, data);
        return data;
    },

    /**
     * Add user folder
     *
     * @param {*} context
     * @param {Object} params
     * @returns userFolder
     */
    async [ADD_USER_FOLDER](context, folder) {
        context.commit(ADD_USER_FOLDER, folder);
        await UserFolderService.update(
            context.state.project.slug,
            context.state.project.user.id,
            folder.id
        );
    },

    /**
     * Remove user folder
     *
     * @param {*} context
     * @param {Number} params user folder id
     * @returns userFolder
     */
    async [REMOVE_USER_FOLDER](context, folder) {
        context.commit(REMOVE_USER_FOLDER, folder);
        await UserFolderService.destroy(
            context.state.project.slug,
            context.state.project.user.id,
            folder.id
        );
    },

    /**
     * Add user folder
     *
     * @param {*} context
     * @param {Object} params
     * @returns userFolder
     */
    async [BULK_ADD_USER_FOLDER](context, { users, folders }) {
        await UserFolderService.bulkUpdate(
            context.state.project.slug,
            users,
            folders
        );
    },

    /**
     * Remove user folder
     *
     * @param {*} context
     * @param {Number} params user folder id
     * @returns userFolder
     */
    async [BULK_REMOVE_USER_FOLDER](context, { users, folders }) {
        await UserFolderService.bulkDestroy(
            context.state.project.slug,
            users,
            folders
        );
    },
};

/**
 * User folder Store Mutations
 */
export const mutations = {
    /**
     * Set user folders
     *
     * @param {*} state
     * @returns
     */
    [SET_USER_FOLDERS](state, folders) {
        state.project.user.folders = folders;
    },

    /**
     * Add user folder
     *
     * @param {*} state
     * @param {Number} folder to append to user folders list
     */
    [ADD_USER_FOLDER](state, folder) {
        state.project.user.folders = [
            ...(state.project.user.folders ? state.project.user.folders : []),
            folder,
        ];
        if (Array.isArray(state.project.users)) {
            state.project.users = state.project.users.map((o) =>
                o.id == state.project.user.id
                    ? { ...o, ...state.project.user }
                    : o
            );
        }
    },

    /**
     * Remove user folder
     *
     * @param {*} context
     * @param {Number} params user folder id
     */
    [REMOVE_USER_FOLDER](state, folder) {
        state.project.user.folders = (
            state.project.user.folders ? state.project.user.folders : []
        ).filter((o) => o.id != folder.id);
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },
};

/**
 * User folder Store Getters
 */
export const getters = {
    /**
     * Get list of user folders
     *
     * @param {*} state
     * @returns
     */
    userFolders: (state) =>
        state.project &&
        state.project.user &&
        state.project.user.folders &&
        Array.isArray(state.project.user.folders)
            ? state.project.user.folders
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
