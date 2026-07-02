import DocumentPageService from "@/apis/project/document/page";

import {
    FETCH_DOCUMENT_PAGES,
    SET_DOCUMENT_PAGES,
    SET_DOCUMENT_NEW_PAGE,
    ADD_DOCUMENT_PAGE,
    SHOW_DOCUMENT_PAGE,
    UPDATE_DOCUMENT_PAGE,
    REMOVE_DOCUMENT_PAGE,
} from "@/actions/project/document";

/**
 * Document Store state
 */
const state = {
    pages: [],
    page: null,
};

/**
 * Document Store Actions
 */
const actions = {
    /**
     * Fetch document pages
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_DOCUMENT_PAGES](context, params) {
        const { data } = await DocumentPageService.get(
            context.state.project.slug,
            context.state.project.document.id,
            params
        );
        context.commit(SET_DOCUMENT_PAGES, data);
        return data;
    },

    /**
     * Add document page
     *
     * @param {*} context
     * @param {Object} params document page values
     * @returns document
     */
    async [ADD_DOCUMENT_PAGE](context, params) {
        const { data } = await DocumentPageService.create(
            context.state.project.slug,
            context.state.project.document.id,
            params
        );
        context.commit(ADD_DOCUMENT_PAGE, data);
        return data;
    },

    /**
     * update document
     *
     * @param {*} context
     * @param {Object} params new document page values
     */
    async [UPDATE_DOCUMENT_PAGE](context, params) {
        await DocumentPageService.update(
            context.state.project.slug,
            context.state.project.document.id,
            params.id,
            params
        );
        context.commit(UPDATE_DOCUMENT_PAGE, params);
    },

    /**
     * remove document page
     *
     * @param {*} context
     * @param {Number} params document page id
     * @returns document
     */
    async [REMOVE_DOCUMENT_PAGE](context, slug) {
        context.commit(REMOVE_DOCUMENT_PAGE, slug);
        await DocumentPageService.destroy(
            context.state.project.slug,
            context.state.project.document.id,
            slug
        );
    },
};

/**
 * Document Store Mutations
 */
const mutations = {
    /**
     * Set pages
     *
     * @param {*} state
     * @returns
     */
    [SET_DOCUMENT_PAGES](state, pages) {
        state.project.document.pages = [...pages];
    },

    /**
     * Add page
     *
     * @param {*} state
     * @param {Number} document to append to documents list
     */
    [ADD_DOCUMENT_PAGE](state, page) {
        state.project.document.pages = [...state.project.document.pages, page];
    },

    /**
     * New page
     *
     * @param {*} state
     * @param {Number} document to append to documents list
     */
    [SET_DOCUMENT_NEW_PAGE](state, page) {
        state.project.document.newPage = page;
    },

    /**
     * Show page
     *
     * @param {*} state
     */
    [SHOW_DOCUMENT_PAGE](state, page) {
        state.project.document.page = page;
    },

    /**
     * Update page
     *
     * @param {*} state
     */
    [UPDATE_DOCUMENT_PAGE](state, params) {
        state.project.document.pages = state.project.document.pages.map((o) =>
            o.id == params.id ? { ...o, ...params } : o
        );
    },

    /**
     * remove page
     *
     * @param {*} context
     * @param {Number} params page id
     */
    [REMOVE_DOCUMENT_PAGE](state, slug) {
        state.project.document.pages = state.project.document.pages.filter(
            (o) => o.id != slug
        );
    },
};

/**
 * Document Store Getters
 */
const getters = {
    /**
     * Get list of pages
     *
     * @param {*} state
     * @returns
     */
    documentPages: (state) => {
        return state.project &&
            state.project.document &&
            state.project.document.pages
            ? state.project.document.pages
            : [];
    },

    /**
     * Get current page
     *
     * @param {*} state
     * @returns
     */
    documentPage: (state) =>
        state.project && state.project.document && state.project.document.page
            ? state.project.document.page
            : null,

    /**
     * Get current new page
     *
     * @param {*} state
     * @returns
     */
    documentNewPage: (state) =>
        state.project &&
        state.project.document &&
        state.project.document.newPage
            ? state.project.document.newPage
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
