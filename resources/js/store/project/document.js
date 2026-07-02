import DocumentService from "@/apis/project/document";

import {
    FETCH_DOCUMENTS,
    SET_DOCUMENTS,
    SET_DOCUMENT,
    ADD_DOCUMENT,
    SHOW_DOCUMENT,
    UPDATE_DOCUMENT,
    REMOVE_DOCUMENT,
    UPDATE_DOCUMENT_ZOOM,
} from "@/actions/project/document";

import fieldStore from "./document/field";
import fileStore from "./document/file";
import fontStore from "./document/font";
import pageStore from "./document/page";

/**
 * Document Store state
 */
const state = {
    document: null,
    documentZoom: 1,
};

/**
 * Document Store Actions
 */
const actions = {
    /**
     * Fetch documents
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_DOCUMENTS](context, params) {
        const { data } = await DocumentService.get(
            context.state.project.slug,
            params
        );
        context.commit(SET_DOCUMENTS, data);
        return data;
    },

    /**
     * Show document
     *
     * @param {*} context
     * @param {Number} slug document id
     * @returns document
     */
    async [SHOW_DOCUMENT](context, slug) {
        context.commit(SET_DOCUMENT, {
            id: slug,
        });
        const { data } = await DocumentService.show(
            context.state.project.slug,
            slug
        );
        context.commit(SET_DOCUMENT, data);
        return data;
    },

    /**
     * Add document
     *
     * @param {*} context
     * @param {Object} params document field values
     * @returns document
     */
    async [ADD_DOCUMENT](context, params) {
        const { data } = await DocumentService.create(
            context.state.project.slug,
            params
        );
        context.commit(ADD_DOCUMENT, data);
        return data;
    },

    /**
     * update document
     *
     * @param {*} context
     * @param {Object} params new document field values
     */
    async [UPDATE_DOCUMENT](context, params) {
        await DocumentService.update(
            context.state.project.slug,
            params.id,
            params
        );
        context.commit(UPDATE_DOCUMENT, params);
    },

    /**
     * remove document
     *
     * @param {*} context
     * @param {Number} params document page id
     * @returns document
     */
    async [REMOVE_DOCUMENT](context, slug) {
        await DocumentService.destroy(context.state.project.slug, slug);
        context.commit(REMOVE_DOCUMENT, slug);
    },

    ...fieldStore.actions,
    ...fileStore.actions,
    ...fontStore.actions,
    ...pageStore.actions,
};

/**
 * Document Store Mutations
 */
const mutations = {
    /**
     * Set documents
     *
     * @param {*} state
     * @returns
     */
    [SET_DOCUMENTS](state, documents) {
        state.project.documents = [...documents];
    },

    /**
     * Set current document
     *
     * @param {*} state
     * @param {Object} document
     */
    [SET_DOCUMENT](state, document) {
        state.project.document = document;
    },

    /**
     * Add document
     *
     * @param {*} state
     * @param {Number} document to append to documents list
     */
    [ADD_DOCUMENT](state, document) {
        state.project.documents = [...state.project.documents, document];
    },

    /**
     * Update document
     *
     * @param {*} state
     */
    [UPDATE_DOCUMENT](state, params) {
        state.project.documents = state.project.documents.map((o) =>
            o.id == params.id ? { ...o, ...params } : o
        );
    },

    /**
     * remove document
     *
     * @param {*} context
     * @param {Number} params document id
     */
    [REMOVE_DOCUMENT](state, slug) {
        state.project.documents = state.project.documents.filter(
            (o) => o.id != slug
        );
    },

    /**
     * Zoom in document
     *
     * @param {*} state
     */
    [UPDATE_DOCUMENT_ZOOM](state, value) {
        state.project.documentZoom = value;
    },

    ...fieldStore.mutations,
    ...fileStore.mutations,
    ...fontStore.mutations,
    ...pageStore.mutations,
};

/**
 * Document Store Getters
 */
const getters = {
    /**
     * Get list of documents
     *
     * @param {*} state
     * @returns
     */
    documents: (state) =>
        state.project && state.project.documents ? state.project.documents : [],

    /**
     * Get current document
     *
     * @param {*} state
     * @returns
     */
    document: (state) =>
        state.project && state.project.document ? state.project.document : null,

    /**
     * Get document zoom
     *
     * @param {*} state
     * @returns
     */
    documentZoom: (state) =>
        state.project && state.project.documentZoom
            ? state.project.documentZoom
            : 1,

    ...fieldStore.getters,
    ...fileStore.getters,
    ...fontStore.getters,
    ...pageStore.getters,
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
