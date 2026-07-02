import UserDocumentService from "@/apis/project/user/document";

import {
    FETCH_USER_DOCUMENTS,
    SET_USER_DOCUMENTS,
    ADD_USER_DOCUMENT,
    REMOVE_USER_DOCUMENT,
    BULK_ADD_USER_DOCUMENT,
    BULK_REMOVE_USER_DOCUMENT,
} from "@/actions/project/user/document";

/**
 * UserDocument Store state
 */
export const state = {
    documents: [],
};

/**
 * UserDocument Store Actions
 */
export const actions = {
    /**
     * Fetch user documents
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_USER_DOCUMENTS](context, params) {
        const { data } = await UserDocumentService.get(
            context.state.project.slug,
            context.state.project.user.id,
            params
        );
        context.commit(SET_USER_DOCUMENTS, data);
        return data;
    },

    /**
     * Add user document
     *
     * @param {*} context
     * @param {Object} params
     * @returns userDocument
     */
    async [ADD_USER_DOCUMENT](context, document) {
        context.commit(ADD_USER_DOCUMENT, document);
        await UserDocumentService.update(
            context.state.project.slug,
            context.state.project.user.id,
            document.id
        );
    },

    /**
     * Remove user document
     *
     * @param {*} context
     * @param {Number} params user document id
     * @returns userDocument
     */
    async [REMOVE_USER_DOCUMENT](context, document) {
        context.commit(REMOVE_USER_DOCUMENT, document);
        await UserDocumentService.destroy(
            context.state.project.slug,
            context.state.project.user.id,
            document.id
        );
    },

    /**
     * Add user document
     *
     * @param {*} context
     * @param {Object} params
     * @returns userDocument
     */
    async [BULK_ADD_USER_DOCUMENT](context, { users, documents }) {
        await UserDocumentService.bulkUpdate(
            context.state.project.slug,
            users,
            documents
        );
    },

    /**
     * Remove user document
     *
     * @param {*} context
     * @param {Number} params user document id
     * @returns userDocument
     */
    async [BULK_REMOVE_USER_DOCUMENT](context, { users, documents }) {
        await UserDocumentService.bulkDestroy(
            context.state.project.slug,
            users,
            documents
        );
    },
};

/**
 * User document Store Mutations
 */
export const mutations = {
    /**
     * Set user documents
     *
     * @param {*} state
     * @returns
     */
    [SET_USER_DOCUMENTS](state, documents) {
        state.project.user.documents = documents;
    },

    /**
     * Add user document
     *
     * @param {*} state
     * @param {Number} document to append to user documents list
     */
    [ADD_USER_DOCUMENT](state, document) {
        state.project.user.documents = [
            ...(state.project.user.documents
                ? state.project.user.documents
                : []),
            document,
        ];
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },

    /**
     * Remove user document
     *
     * @param {*} context
     * @param {Number} params user document id
     */
    [REMOVE_USER_DOCUMENT](state, document) {
        state.project.user.documents = (
            state.project.user.documents ? state.project.user.documents : []
        ).filter((o) => o.id != document.id);
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },
};

/**
 * User document Store Getters
 */
export const getters = {
    /**
     * Get list of user documents
     *
     * @param {*} state
     * @returns
     */
    userDocuments: (state) =>
        state.project &&
        state.project.user &&
        state.project.user.documents &&
        Array.isArray(state.project.user.documents)
            ? state.project.user.documents
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
