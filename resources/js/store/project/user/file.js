import UserFileService from "@/apis/project/user/file";

import {
    FETCH_USER_FILES,
    SET_USER_FILES,
    ADD_USER_FILE,
    REMOVE_USER_FILE,
    SET_USER_FILE_FOLDER,
} from "@/actions/project/user/file";

/**
 * UserFile Store state
 */
export const state = {
    files: [],
};

/**
 * UserFile Store Actions
 */
export const actions = {
    /**
     * Fetch user files
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_USER_FILES](context, folder) {
        const { data } = await UserFileService.get(
            context.state.project.slug,
            context.state.project.user.id,
            folder
        );
        context.commit(SET_USER_FILES, data);
        return data;
    },

    /**
     * Add user file
     *
     * @param {*} context
     * @param {Object} params
     * @returns userFile
     */
    async [ADD_USER_FILE](context, { folder, params, settings }) {
        const { data } = await UserFileService.create(
            context.state.project.slug,
            context.state.project.user.id,
            folder,
            params,
            settings
        );
        context.commit(ADD_USER_FILE, data);
        return data;
    },

    /**
     * Remove user file
     *
     * @param {*} context
     * @param {Number} params user file id
     * @returns userFile
     */
    async [REMOVE_USER_FILE](context, params) {
        await UserFileService.destroy(
            context.state.project.slug,
            context.state.project.user.id,
            params.folder_id,
            params.id
        );
        context.commit(REMOVE_USER_FILE, params.id);
    },
};

/**
 * User file Store Mutations
 */
export const mutations = {
    /**
     * Set user files
     *
     * @param {*} state
     * @returns
     */
    [SET_USER_FILES](state, files) {
        state.project.user.files = files;
    },

    /**
     * Add user file
     *
     * @param {*} state
     * @param {Number} file to append to user files list
     */
    [ADD_USER_FILE](state, file) {
        state.project.user.files = [
            ...(state.project.user.files ? state.project.user.files : []),
            file,
        ];
    },

    /**
     * Remove user file
     *
     * @param {*} context
     * @param {Number} params user file id
     */
    [REMOVE_USER_FILE](state, slug) {
        state.project.user.files = (
            state.project.user.files ? state.project.user.files : []
        ).filter((o) => o.id != slug);
    },

    /**
     * Set user file folder
     *
     * @param {*} context
     * @param {Number} params user file folder id
     */
    async [SET_USER_FILE_FOLDER](state, folder) {
        state.project.fileFolder = folder;
    },
};

/**
 * User file Store Getters
 */
export const getters = {
    /**
     * Get list of user files
     *
     * @param {*} state
     * @returns
     */
    userFiles: (state) =>
        state.project && state.project.user && state.project.user.files
            ? state.project.user.files
            : [],

    /**
     *
     * @param {*} state
     * @returns
     */
    fileFolder: (state) =>
        state.project && state.project.fileFolder
            ? state.project.fileFolder
            : null,
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
