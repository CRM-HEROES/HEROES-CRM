import DocumentTemplateService from "@/apis/project/document-template";

import {
    FETCH_DOCUMENT_TEMPLATES,
    SET_DOCUMENT_TEMPLATES,
    ADD_DOCUMENT_TEMPLATE,
} from "@/actions/project/document-template";

/**
 * Document template Store state
 */
const state = {};

/**
 * Document Template Store Actions
 */
const actions = {
    /**
     * Fetch document templates
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_DOCUMENT_TEMPLATES](context, params) {
        const { data } = await DocumentTemplateService.get(
            context.state.project.slug,
            params
        );
        context.commit(SET_DOCUMENT_TEMPLATES, data);
        return data;
    },

    /**
     * Add document template
     *
     * @param {*} context
     * @param {Object} params document field values
     * @returns document
     */
    async [ADD_DOCUMENT_TEMPLATE](context, template) {
        const { data } = await DocumentTemplateService.create(
            context.state.project.slug,
            template
        );
        return data;
    },
};

/**
 * Document Template Store Mutations
 */
const mutations = {
    /**
     * Set document templates
     *
     * @param {*} state
     * @returns
     */
    [SET_DOCUMENT_TEMPLATES](state, documentTemplates) {
        state.project.documentTemplates = [...documentTemplates];
    },
};

/**
 * DocumentTemplate Store Getters
 */
const getters = {
    /**
     * Get list of documentTemplates
     *
     * @param {*} state
     * @returns
     */
    documentTemplates: (state) =>
        state.project && state.project.documentTemplates
            ? state.project.documentTemplates
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
