import ProspectQuestionnaireService from "@/apis/project/prospect/questionnaire";

import {
    FETCH_PROSPECT_QUESTIONNAIRES,
    SET_PROSPECT_QUESTIONNAIRES,
    SET_PROSPECT_QUESTIONNAIRE,
    ADD_PROSPECT_QUESTIONNAIRE,
    SHOW_PROSPECT_QUESTIONNAIRE,
    REMOVE_PROSPECT_QUESTIONNAIRE,
} from "@/actions/project/prospect/questionnaire";

/**
 * ProspectQuestionnaire Store state
 */
export const state = {
    questionnaires: [],
    questionnaire: null,
};

/**
 * ProspectQuestionnaire Store Actions
 */
export const actions = {
    /**
     * Fetch prospect questionnaires
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PROSPECT_QUESTIONNAIRES](context, params) {
        const { data } = await ProspectQuestionnaireService.get(
            context.state.project.slug,
            context.state.project.prospect.id,
            params
        );
        context.commit(SET_PROSPECT_QUESTIONNAIRES, data);
        return data;
    },

    /**
     * Add prospect questionnaire
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectQuestionnaire
     */
    async [ADD_PROSPECT_QUESTIONNAIRE](context, questionnaire) {
        const { data } = await ProspectQuestionnaireService.update(
            context.state.project.slug,
            context.state.project.prospect.id,
            questionnaire.id
        );
        context.commit(ADD_PROSPECT_QUESTIONNAIRE, questionnaire);
        return data;
    },

    /**
     * Show prospect questionnaire
     *
     * @param {*} context
     * @param {Number} slug folder id
     * @returns folder
     */
    async [SHOW_PROSPECT_QUESTIONNAIRE](context, slug) {
        const { data } = await ProspectQuestionnaireService.show(
            context.state.project.slug,
            context.state.project.prospect.id,
            slug
        );
        context.commit(SET_PROSPECT_QUESTIONNAIRE, data);
        return data;
    },

    /**
     * Remove prospect questionnaire
     *
     * @param {*} context
     * @param {Number} params prospect questionnaire id
     * @returns prospectQuestionnaire
     */
    async [REMOVE_PROSPECT_QUESTIONNAIRE](context, slug) {
        await ProspectQuestionnaireService.destroy(
            context.state.project.slug,
            context.state.project.prospect.id,
            slug
        );
        context.commit(REMOVE_PROSPECT_QUESTIONNAIRE, slug);
    },
};

/**
 * Prospect questionnaire Store Mutations
 */
export const mutations = {
    /**
     * Set prospect questionnaires
     *
     * @param {*} state
     * @returns
     */
    [SET_PROSPECT_QUESTIONNAIRES](state, questionnaires) {
        state.project.prospect.questionnaires = questionnaires;
    },

    /**
     * Set prospect questionnaire results
     *
     * @param {*} state
     * @returns
     */
    [SET_PROSPECT_QUESTIONNAIRE](state, questionnaireResults) {
        state.project.prospect.questionnaireResults = questionnaireResults;
    },

    /**
     * Add prospect questionnaire
     *
     * @param {*} state
     * @param {Number} questionnaire to append to prospect questionnaires list
     */
    [ADD_PROSPECT_QUESTIONNAIRE](state, questionnaire) {
        state.project.prospect.questionnaires = [
            ...(state.project.prospect.questionnaires
                ? state.project.prospect.questionnaires
                : []),
            questionnaire,
        ];
    },

    /**
     * Remove prospect questionnaire
     *
     * @param {*} context
     * @param {Number} params prospect questionnaire id
     */
    [REMOVE_PROSPECT_QUESTIONNAIRE](state, slug) {
        state.project.prospect.questionnaires = (
            state.project.prospect.questionnaires
                ? state.project.prospect.questionnaires
                : []
        ).filter((o) => o.id != slug);
    },
};

/**
 * Prospect questionnaire Store Getters
 */
export const getters = {
    /**
     * Get list of prospect questionnaires
     *
     * @param {*} state
     * @returns
     */
    prospectQuestionnaireResults: (state) =>
        state.project &&
        state.project.prospect &&
        state.project.prospect.questionnaireResults &&
        Array.isArray(state.project.prospect.questionnaireResults)
            ? state.project.prospect.questionnaireResults
            : [],

    /**
     * Get prospect questionnaires
     *
     * @param {*} state
     * @returns
     */
    prospectQuestionnaires: (state) =>
        state.project &&
        state.project.prospect &&
        state.project.prospect.questionnaires &&
        Array.isArray(state.project.prospect.questionnaires)
            ? state.project.prospect.questionnaires
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
