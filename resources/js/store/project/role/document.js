import RoleDocumentService from "@/apis/project/role/document";

import {
    FETCH_ROLE_DOCUMENTS,
    SET_ROLE_DOCUMENTS,
    ADD_ROLE_DOCUMENT,
    REMOVE_ROLE_DOCUMENT,
    BULK_ADD_ROLE_DOCUMENT,
    BULK_REMOVE_ROLE_DOCUMENT,
} from "@/actions/project/role/document";

/**
 * RoleDocument Store state
 */
export const state = {
    documents: [],
};

/**
 * RoleDocument Store Actions
 */
export const actions = {
    /**
     * Fetch role documents
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_ROLE_DOCUMENTS](context, params) {
        const { data } = await RoleDocumentService.get(
            context.state.project.slug,
            context.state.project.role.id,
            params
        );
        context.commit(SET_ROLE_DOCUMENTS, data);
        return data;
    },

    /**
     * Add role document
     *
     * @param {*} context
     * @param {Object} params
     * @returns roleDocument
     */
    async [ADD_ROLE_DOCUMENT](context, document) {
        context.commit(ADD_ROLE_DOCUMENT, document);
        await RoleDocumentService.update(
            context.state.project.slug,
            context.state.project.role.id,
            document.id
        );
    },

    /**
     * Remove role document
     *
     * @param {*} context
     * @param {Number} params role document id
     * @returns roleDocument
     */
    async [REMOVE_ROLE_DOCUMENT](context, document) {
        context.commit(REMOVE_ROLE_DOCUMENT, document);
        await RoleDocumentService.destroy(
            context.state.project.slug,
            context.state.project.role.id,
            document.id
        );
    },

    /**
     * Add role document
     *
     * @param {*} context
     * @param {Object} params
     * @returns roleDocument
     */
    async [BULK_ADD_ROLE_DOCUMENT](context, { roles, documents }) {
        await RoleDocumentService.bulkUpdate(
            context.state.project.slug,
            roles,
            documents
        );
    },

    /**
     * Remove role document
     *
     * @param {*} context
     * @param {Number} params role document id
     * @returns roleDocument
     */
    async [BULK_REMOVE_ROLE_DOCUMENT](context, { roles, documents }) {
        await RoleDocumentService.bulkDestroy(
            context.state.project.slug,
            roles,
            documents
        );
    },
};

/**
 * Role document Store Mutations
 */
export const mutations = {
    /**
     * Set role documents
     *
     * @param {*} state
     * @returns
     */
    [SET_ROLE_DOCUMENTS](state, documents) {
        state.project.role.documents = documents;
    },

    /**
     * Add role document
     *
     * @param {*} state
     * @param {Number} document to append to role documents list
     */
    [ADD_ROLE_DOCUMENT](state, document) {
        state.project.role.documents = [
            ...(state.project.role.documents
                ? state.project.role.documents
                : []),
            document,
        ];
        state.project.roles = state.project.roles.map((o) =>
            o.id == state.project.role.id ? { ...o, ...state.project.role } : o
        );
    },

    /**
     * Remove role document
     *
     * @param {*} context
     * @param {Number} params role document id
     */
    [REMOVE_ROLE_DOCUMENT](state, document) {
        state.project.role.documents = (
            state.project.role.documents ? state.project.role.documents : []
        ).filter((o) => o.id != document.id);
        state.project.roles = state.project.roles.map((o) =>
            o.id == state.project.role.id ? { ...o, ...state.project.role } : o
        );
    },
};

/**
 * Role document Store Getters
 */
export const getters = {
    /**
     * Get list of role documents
     *
     * @param {*} state
     * @returns
     */
    roleDocuments: (state) =>
        state.project &&
        state.project.role &&
        state.project.role.documents &&
        Array.isArray(state.project.role.documents)
            ? state.project.role.documents
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
