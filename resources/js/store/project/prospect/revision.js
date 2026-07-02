import ProspectRevisionService from "@/apis/project/prospect/revision";

import {
    FETCH_PROSPECT_REVISIONS,
    SET_PROSPECT_REVISIONS,
} from "@/actions/project/prospect/revision";

/**
 * ProspectRevision Store state
 */
export const state = {
    revisions: [],
};

/**
 * ProspectRevision Store Actions
 */
export const actions = {
    /**
     * Fetch prospect revisions
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PROSPECT_REVISIONS](context, params) {
        const { data } = await ProspectRevisionService.get(
            context.state.project.slug,
            context.state.project.prospect.id,
            params
        );
        context.commit(SET_PROSPECT_REVISIONS, data);
        return data;
    },
};

/**
 * Prospect revision Store Mutations
 */
export const mutations = {
    /**
     * Set prospect revisions
     *
     * @param {*} state
     * @returns
     */
    [SET_PROSPECT_REVISIONS](state, revisions) {
        state.project.prospect.revisions = revisions;
    },
};

/**
 * Prospect revision Store Getters
 */
export const getters = {
    /**
     * Get list of prospect revisions
     *
     * @param {*} state
     * @returns
     */
    prospectRevisions: (state) =>
        state.project &&
        state.project.prospect &&
        state.project.prospect.revisions
            ? state.project.prospect.revisions
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
