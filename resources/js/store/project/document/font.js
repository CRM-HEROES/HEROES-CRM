import DocumentFontService from "@/apis/project/document/font";

import {
    FETCH_DOCUMENT_FONTS,
    SET_DOCUMENT_FONTS,
    ADD_DOCUMENT_FONT,
    UPDATE_DOCUMENT_FONT,
    REMOVE_DOCUMENT_FONT,
} from "@/actions/project/document";

/**
 * Document Store state
 */
const state = {
    fonts: [],
    font: null,
};

/**
 * Document Store Actions
 */
const actions = {
    /**
     * Fetch document fonts
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_DOCUMENT_FONTS](context, params) {
        const { data } = await DocumentFontService.get(
            context.state.project.slug,
            context.state.project.document.id,
            params
        );
        context.commit(SET_DOCUMENT_FONTS, data);
        return data;
    },

    /**
     * Add document font
     *
     * @param {*} context
     * @param {Object} params document font values
     * @returns document
     */
    async [ADD_DOCUMENT_FONT](context, params) {
        const { data } = await DocumentFontService.create(
            context.state.project.slug,
            context.state.project.document.id,
            params
        );
        context.commit(ADD_DOCUMENT_FONT, data);
        return data;
    },

    /**
     * update document
     *
     * @param {*} context
     * @param {Object} params new document font values
     */
    async [UPDATE_DOCUMENT_FONT](context, { slug, params }) {
        await DocumentFontService.update(
            context.state.project.slug,
            context.state.project.document.id,
            slug,
            params
        );
        context.commit(UPDATE_DOCUMENT_FONT, { slug, params });
    },

    /**
     * remove document font
     *
     * @param {*} context
     * @param {Number} params document font id
     * @returns document
     */
    async [REMOVE_DOCUMENT_FONT](context, slug) {
        await DocumentFontService.destroy(
            context.state.project.slug,
            context.state.project.document.id,
            slug
        );
        context.commit(REMOVE_DOCUMENT_FONT, slug);
    },
};

/**
 * Document Store Mutations
 */
const mutations = {
    /**
     * Set fonts
     *
     * @param {*} state
     * @returns
     */
    [SET_DOCUMENT_FONTS](state, fonts) {
        state.project.document.fonts = [...fonts];
    },

    /**
     * Add font
     *
     * @param {*} state
     * @param {Number} document to append to documents list
     */
    [ADD_DOCUMENT_FONT](state, font) {
        state.project.document.fonts = [...state.project.document.fonts, font];
    },

    /**
     * Update font
     *
     * @param {*} state
     */
    [UPDATE_DOCUMENT_FONT](state, { slug, params }) {
        state.project.document.fonts = state.project.document.fonts.map((o) =>
            o.id == slug ? { ...o, ...params } : o
        );
    },

    /**
     * remove font
     *
     * @param {*} context
     * @param {Number} params font id
     */
    [REMOVE_DOCUMENT_FONT](state, slug) {
        state.project.document.fonts = state.project.document.fonts.filter(
            (o) => o.id != slug
        );
    },
};

/**
 * Document Store Getters
 */
const getters = {
    /**
     * Get list of fonts
     *
     * @param {*} state
     * @returns
     */
    documentFonts: (state) => {
        return state.project &&
            state.project.document &&
            state.project.document.fonts
            ? state.project.document.fonts
            : [];
    },

    /**
     * Get current font
     *
     * @param {*} state
     * @returns
     */
    documentFont: (state) =>
        state.project && state.project.document && state.project.document.font
            ? state.project.document.font
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
