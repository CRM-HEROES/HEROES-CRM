import ProspectFileService from "@/apis/project/prospect/file";
import ProspectFolderService from "@/apis/project/prospect/folder";

import {
    FETCH_PROSPECT_FILES,
    FETCH_PROSPECT_FOLDERS,
    SET_PROSPECT_FILES,
    SET_PROSPECT_FOLDERS,
    ADD_PROSPECT_FILE,
    REMOVE_PROSPECT_FILE,
    SET_PROSPECT_FILE_FOLDER,
    SET_PROSPECT_FILE,
    BULK_ADD_USER_FILE,
    BULK_REMOVE_USER_FILE,
} from "@/actions/project/prospect/file";

/**
 * ProspectFile Store state
 */
export const state = {
    files: [],
};

/**
 * ProspectFile Store Actions
 */
export const actions = {
    /**
     * Fetch prospect files
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PROSPECT_FILES](context, folder) {
        const { data } = await ProspectFileService.get(
            context.state.project.slug,
            context.state.project.prospect.id,
            folder
        );
        context.commit(SET_PROSPECT_FILES, data);
        return data;
    },

    /**
     * Fetch prospect messages folders
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PROSPECT_FOLDERS](context) {
        const { data } = await ProspectFolderService.get(
            context.state.project.slug,
            context.state.project.prospect.id
        );
        context.commit(SET_PROSPECT_FOLDERS, data);
        return data;
    },

    /**
     * Add prospect file
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectFile
     */
    async [ADD_PROSPECT_FILE](context, { folder, params, settings }) {
        const { data } = await ProspectFileService.create(
            context.state.project.slug,
            context.state.project.prospect.id,
            folder,
            params,
            settings
        );
        context.commit(ADD_PROSPECT_FILE, data);
        return data;
    },

    /**
     * Remove prospect file
     *
     * @param {*} context
     * @param {Number} params prospect file id
     * @returns prospectFile
     */
    async [REMOVE_PROSPECT_FILE](context, params) {
        await ProspectFileService.destroy(
            context.state.project.slug,
            context.state.project.prospect.id,
            params.folder_id,
            params.id
        );
        context.commit(REMOVE_PROSPECT_FILE, params.id);
    },

    /**
     * Add user on file
     *
     * @param {*} context
     * @param {Object} params
     * @returns userFolder
     */
    async [BULK_ADD_USER_FILE](context, { users, files }) {
        await ProspectFileService.bulkUpdate(
            context.state.project.slug,
            context.state.project.prospect.id,
            context.state.project.folder.id,
            users,
            files
        );
    },

    /**
     * Remove user on file
     *
     * @param {*} context
     * @param {Number} params user folder id
     * @returns userFolder
     */
    async [BULK_REMOVE_USER_FILE](context, { users, files }) {
        console.log(context.state)
        await ProspectFileService.bulkDestroy(
            context.state.project.slug,
            context.state.project.prospect.id,
            context.state.project.folder.id,
            users,
            files
        );
    },
};

/**
 * Prospect file Store Mutations
 */
export const mutations = {
    /**
     * Set prospect files
     *
     * @param {*} state
     * @returns
     */
    [SET_PROSPECT_FILES](state, files) {
        state.project.prospect.files = files;
    },

    /**
     * Set prospect folders
     *
     * @param {*} state
     * @returns
     */
    [SET_PROSPECT_FOLDERS](state, folders) {
        state.project.prospect.folders = folders;
    },

    /**
     * Add prospect file
     *
     * @param {*} state
     * @param {Number} file to append to prospect files list
     */
    [ADD_PROSPECT_FILE](state, file) {
        state.project.prospect.files = [
            ...(state.project.prospect.files
                ? state.project.prospect.files
                : []),
            file,
        ];
    },

    /**
     * Remove prospect file
     *
     * @param {*} context
     * @param {Number} params prospect file id
     */
    [REMOVE_PROSPECT_FILE](state, slug) {
        state.project.prospect.files = (
            state.project.prospect.files ? state.project.prospect.files : []
        ).filter((o) => o.id != slug);
    },

    /**
     * Set the current file
     *
     * @param {*} state
     * @param {Object} folder
     */
    [SET_PROSPECT_FILE](state, file) {
        state.project.prospect.file = file;
        console.log(state)
    },

    /**
     * Set prospect file folder
     *
     * @param {*} context
     * @param {Number} params prospect file folder id
     */
    async [SET_PROSPECT_FILE_FOLDER](state, folder) {
        state.project.fileFolder = folder;
    },

};

/**
 * Prospect file Store Getters
 */
export const getters = {
    /**
     * Get list of prospect files
     *
     * @param {*} state
     * @returns
     */
    prospectFiles: (state) =>
        state.project && state.project.prospect && state.project.prospect.files
            ? state.project.prospect.files
            : [],

    /**
     * Get list of prospect folders
     *
     * @param {*} state
     * @returns
     */
    prospectFolders: (state) =>
        state.project &&
        state.project.prospect &&
        state.project.prospect.folders
            ? state.project.prospect.folders
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

    /**
     * Get the current file
     *
     * @param {*} state
     * @returns
     */
    file: (state) =>
        state.project &&
        state.project.prospect &&
        state.project.prospect.file
            ? state.project.prospect.file
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
