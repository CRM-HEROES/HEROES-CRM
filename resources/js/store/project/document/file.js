import DocumentFileService from "@/apis/project/document/file";

import {
    FETCH_DOCUMENT_FILES,
    SET_DOCUMENT_FILES,
    ADD_DOCUMENT_FILE,
    UPDATE_DOCUMENT_FILE,
    REMOVE_DOCUMENT_FILE,
} from "@/actions/project/document";

/**
 * Document Store state
 */
const state = {
    files: [],
    file: null,
};

/**
 * Document Store Actions
 */
const actions = {
    /**
     * Fetch document files
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_DOCUMENT_FILES](context, params) {
        const { data } = await DocumentFileService.get(
            context.state.project.slug,
            context.state.project.document.id,
            params
        );
        context.commit(SET_DOCUMENT_FILES, data);
        return data;
    },

    /**
     * Add document file
     *
     * @param {*} context
     * @param {Object} params document file values
     * @returns document
     */
    async [ADD_DOCUMENT_FILE](context, params) {
        const { data } = await DocumentFileService.create(
            context.state.project.slug,
            context.state.project.document.id,
            params
        );
        context.commit(ADD_DOCUMENT_FILE, data);
        return data;
    },

    /**
     * update document
     *
     * @param {*} context
     * @param {Object} params new document file values
     */
    async [UPDATE_DOCUMENT_FILE](context, { slug, params }) {
        await DocumentFileService.update(
            context.state.project.slug,
            context.state.project.document.id,
            slug,
            params
        );
        context.commit(UPDATE_DOCUMENT_FILE, { slug, params });
    },

    /**
     * remove document file
     *
     * @param {*} context
     * @param {Number} params document file id
     * @returns document
     */
    async [REMOVE_DOCUMENT_FILE](context, slug) {
        await DocumentFileService.destroy(
            context.state.project.slug,
            context.state.project.document.id,
            slug
        );
        context.commit(REMOVE_DOCUMENT_FILE, slug);
    },
};

/**
 * Document Store Mutations
 */
const mutations = {
    /**
     * Set files
     *
     * @param {*} state
     * @returns
     */
    [SET_DOCUMENT_FILES](state, files) {
        state.project.document.files = [...files];
    },

    /**
     * Add file
     *
     * @param {*} state
     * @param {Number} document to append to documents list
     */
    [ADD_DOCUMENT_FILE](state, file) {
        state.project.document.files = [...state.project.document.files, file];
    },

    /**
     * Update file
     *
     * @param {*} state
     */
    [UPDATE_DOCUMENT_FILE](state, { slug, params }) {
        state.project.document.files = state.project.document.files.map((o) =>
            o.id == slug ? { ...o, ...params } : o
        );
    },

    /**
     * remove file
     *
     * @param {*} context
     * @param {Number} params file id
     */
    [REMOVE_DOCUMENT_FILE](state, slug) {
        state.project.document.files = state.project.document.files.filter(
            (o) => o.id != slug
        );
    },
};

/**
 * Document Store Getters
 */
const getters = {
    /**
     * Get list of files
     *
     * @param {*} state
     * @returns
     */
    documentFiles: (state) => {
        return state.project &&
            state.project.document &&
            state.project.document.files
            ? state.project.document.files
            : [];
    },

    /**
     * Get current file
     *
     * @param {*} state
     * @returns
     */
    documentFile: (state) =>
        state.project && state.project.document && state.project.document.file
            ? state.project.document.file
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
