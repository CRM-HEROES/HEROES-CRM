import ProspectResponseService from "@/apis/project/prospect/question";

import {
    FETCH_PROSPECT_RESPONSES,
    SET_PROSPECT_RESPONSES,
    ADD_PROSPECT_RESPONSE,
    REMOVE_PROSPECT_RESPONSE,
} from "@/actions/project/prospect/question";

/**
 * ProspectResponse Store state
 */
export const state = {
    responses: [],
};

/**
 * ProspectResponse Store Actions
 */
export const actions = {
    /**
     * Fetch prospect responses
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PROSPECT_RESPONSES](context, params) {
        const { data } = await ProspectResponseService.get(
            context.state.project.slug,
            context.state.project.prospect.id,
            params
        );
        context.commit(SET_PROSPECT_RESPONSES, data);
        return data;
    },

    /**
     * Add prospect response
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectResponse
     */
    async [ADD_PROSPECT_RESPONSE](context, { question, params }) {
        const { data } = await ProspectResponseService.create(
            context.state.project.slug,
            context.state.project.prospect.id,
            question,
            params
        );
        // context.commit(ADD_PROSPECT_RESPONSE, params);
        return data;
    },

    /**
     * Remove prospect response
     *
     * @param {*} context
     * @param {Number} params prospect response id
     * @returns prospectResponse
     */
    async [REMOVE_PROSPECT_RESPONSE](context, slug) {
        await ProspectResponseService.destroy(
            context.state.project.slug,
            context.state.project.prospect.id,
            slug
        );
        context.commit(REMOVE_PROSPECT_RESPONSE, slug);
    },
};

/**
 * Prospect response Store Mutations
 */
export const mutations = {
    /**
     * Set prospect responses
     *
     * @param {*} state
     * @returns
     */
    [SET_PROSPECT_RESPONSES](state, responses) {
        state.project.prospect.questionnaireResults = responses;
    },

    /**
     * Add prospect response
     *
     * @param {*} state
     * @param {Number} response to append to prospect responses list
     */
    [ADD_PROSPECT_RESPONSE](state, response) {
        state.project.prospect.questionnaireResults = [
            ...(state.project.prospect.questionnaireResults
                ? state.project.prospect.questionnaireResults
                : []),
            response,
        ];
    },

    /**
     * Remove prospect response
     *
     * @param {*} context
     * @param {Number} params prospect response id
     */
    [REMOVE_PROSPECT_RESPONSE](state, slug) {
        state.project.prospect.questionnaireResults = (
            state.project.prospect.questionnaireResults
                ? state.project.prospect.questionnaireResults
                : []
        ).filter((o) => o.question_id != slug);
    },
};

/**
 * Prospect response Store Getters
 */
export const getters = {
    /**
     * Get list of prospect responses
     *
     * @param {*} state
     * @returns
     */
    prospectResponses: (state) =>
        state.project &&
        state.project.prospect &&
        state.project.prospect.questionnaireResults
            ? state.project.prospect.questionnaireResults
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
