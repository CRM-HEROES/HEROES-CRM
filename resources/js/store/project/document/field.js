import DocumentFieldService from "@/apis/project/document/field";

import {
    FETCH_DOCUMENT_FIELDS,
    SET_DOCUMENT_FIELDS,
    SET_DOCUMENT_FIELD,
    ADD_DOCUMENT_FIELD,
    UPDATE_DOCUMENT_FIELD,
    REMOVE_DOCUMENT_FIELD,
    SET_DOCUMENT_FIELD_EDIT,
} from "@/actions/project/document";

/**
 * Document Store state
 */
const state = {
    fields: [],
    field: null,
};

/**
 * Document Store Actions
 */
const actions = {
    /**
     * Fetch document fields
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_DOCUMENT_FIELDS](context, params) {
        const { data } = await DocumentFieldService.get(
            context.state.project.slug,
            context.state.project.document.id,
            params
        );
        context.commit(SET_DOCUMENT_FIELDS, data);
        return data;
    },

    /**
     * Add document field
     *
     * @param {*} context
     * @param {Object} params document field values
     * @returns document
     */
    async [ADD_DOCUMENT_FIELD](context, params) {
        const { data } = await DocumentFieldService.create(
            context.state.project.slug,
            context.state.project.document.id,
            params
        );
        context.commit(ADD_DOCUMENT_FIELD, data);
        return data;
    },

    /**
     * update document
     *
     * @param {*} context
     * @param {Object} params new document field values
     */
    [UPDATE_DOCUMENT_FIELD](context, params) {
        context.commit(UPDATE_DOCUMENT_FIELD, params);
        DocumentFieldService.update(
            context.state.project.slug,
            context.state.project.document.id,
            params.id,
            params
        );
    },

    /**
     * remove document field
     *
     * @param {*} context
     * @param {Number} params document field id
     * @returns document
     */
    async [REMOVE_DOCUMENT_FIELD](context, slug) {
        await DocumentFieldService.destroy(
            context.state.project.slug,
            context.state.project.document.id,
            slug
        );
        context.commit(REMOVE_DOCUMENT_FIELD, slug);
    },
};

/**
 * Document Store Mutations
 */
const mutations = {
    /**
     * Set fields
     *
     * @param {*} state
     * @returns
     */
    [SET_DOCUMENT_FIELDS](state, fields) {
        state.project.document.fields = [...fields];
    },

    /**
     * Set field
     *
     * @param {*} state
     * @returns
     */
    [SET_DOCUMENT_FIELD](state, field) {
        state.project.document.field = field;
    },

    /**
     * Add field
     *
     * @param {*} state
     * @param {Number} document to append to documents list
     */
    [ADD_DOCUMENT_FIELD](state, field) {
        state.project.document.fields = [
            ...state.project.document.fields,
            field,
        ];
    },

    /**
     * Update field
     *
     * @param {*} state
     */
    [UPDATE_DOCUMENT_FIELD](state, params) {
        state.project.document.fields = state.project.document.fields.map((o) =>
            o.id == params.id ? { ...o, ...params } : o
        );
    },

    /**
     * remove field
     *
     * @param {*} context
     * @param {Number} params field id
     */
    [REMOVE_DOCUMENT_FIELD](state, slug) {
        state.project.document.fields = state.project.document.fields.filter(
            (o) => o.id != slug
        );
    },

    /**
     * Set  field
     *
     * @param {*} context
     * @param {Number} params field id
     */
    [SET_DOCUMENT_FIELD_EDIT](state, fieldEdit) {
        state.project.document.fieldEdit = fieldEdit;
    },
};

/**
 * Document Store Getters
 */
const getters = {
    /**
     * Get list of fields
     *
     * @param {*} state
     * @returns
     */
    documentFields: (state) => {
        return state.project &&
            state.project.document &&
            state.project.document.fields
            ? state.project.document.fields
            : [];
    },

    /**
     * Get current field
     *
     * @param {*} state
     * @returns
     */
    documentField: (state) =>
        state.project && state.project.document && state.project.document.field
            ? state.project.document.field
            : null,

    /**
     * Get current field to edit
     *
     * @param {*} state
     * @returns
     */
    documentFieldEdit: (state) =>
        state.project &&
        state.project.document &&
        state.project.document.fieldEdit
            ? state.project.document.fieldEdit
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
