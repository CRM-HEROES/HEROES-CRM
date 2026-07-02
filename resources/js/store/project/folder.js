import folderService from "@/apis/project/folder";

import {
    FETCH_FOLDERS,
    SET_FOLDERS,
    SET_FOLDER,
    ADD_FOLDER,
    SHOW_FOLDER,
    UPDATE_FOLDER,
    REMOVE_FOLDER,
} from "@/actions/project/folder";

/**
 * folder Store state
 */
export const state = {
    folders: [],
    folder: null,
};

/**
 * folder Store Actions
 */
const actions = {
    /**
     * Fetch folders
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_FOLDERS](context, params) {
        const { data } = await folderService.get(
            context.state.project.slug,
            params
        );
        context.commit(SET_FOLDERS, data);
        return data;
    },

    /**
     * Show folder
     *
     * @param {*} context
     * @param {Number} slug folder id
     * @returns folder
     */
    async [SHOW_FOLDER](context, slug) {
        const { data } = await folderService.show(
            context.state.project.slug,
            slug
        );
        context.commit(SET_FOLDER, data);
        return data;
    },

    /**
     * Add folder
     *
     * @param {*} context
     * @param {Object} params folder folder values
     * @returns folder
     */
    async [ADD_FOLDER](context, params) {
        const { data } = await folderService.create(
            context.state.project.slug,
            params
        );
        context.commit(ADD_FOLDER, data);
        return data;
    },

    /**
     * update folder
     *
     * @param {*} context
     * @param {Object} params new folder folder values
     */
    async [UPDATE_FOLDER](context, params) {
        await folderService.update(
            context.state.project.slug,
            params.id,
            params
        );
        context.commit(UPDATE_FOLDER, params);
    },

    /**
     * remove folder
     *
     * @param {*} context
     * @param {Number} params folder id
     * @returns folder
     */
    async [REMOVE_FOLDER](context, slug) {
        await folderService.destroy(context.state.project.slug, slug);
        context.commit(REMOVE_FOLDER, slug);
    },
};

/**
 * folder Store Mutations
 */
const mutations = {
    /**
     * Set folders
     *
     * @param {*} state
     * @returns
     */
    [SET_FOLDERS](state, folders) {
        state.project.folders = [...folders];
    },

    /**
     * Set current folder
     *
     * @param {*} state
     * @param {Object} folder
     */
    [SET_FOLDER](state, folder) {
        state.project.folder = folder;
    },

    /**
     * Add folder
     *
     * @param {*} state
     * @param {Number} folder to append to folders list
     */
    [ADD_FOLDER](state, folder) {
        state.project.folders = [...state.project.folders, folder];
    },

    /**
     * Update folder
     *
     * @param {*} state
     */
    [UPDATE_FOLDER](state, params) {
        state.project.folders = state.project.folders.map((o) =>
            o.id == params.id ? { ...o, ...params } : o
        );
    },

    /**
     * remove folder
     *
     * @param {*} context
     * @param {Number} params folder id
     */
    [REMOVE_FOLDER](state, slug) {
        state.project.folders = state.project.folders.filter(
            (o) => o.id != slug
        );
    },
};

/**
 * folder Store Getters
 */
const getters = {
    /**
     * Get list of folders
     *
     * @param {*} state
     * @returns
     */
    folders: (state) =>
        state.project && state.project.folders ? state.project.folders : [],

    /**
     * Get current folder
     *
     * @param {*} state
     * @returns
     */
    folder: (state) =>
        state.project && state.project.folder ? state.project.folder : null,
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
