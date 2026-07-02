import questionnaireService from "@/apis/project/questionnaire";

import {
    FETCH_QUESTIONNAIRES,
    SET_QUESTIONNAIRES,
    SET_QUESTIONNAIRE,
    ADD_QUESTIONNAIRE,
    SHOW_QUESTIONNAIRE,
    UPDATE_QUESTIONNAIRE,
    REMOVE_QUESTIONNAIRE,
} from "@/actions/project/questionnaire";

/**
 * questionnaire Store state
 */
export const state = {
    questionnaires: [],
    questionnaire: null,
};

/**
 * questionnaire Store Actions
 */
const actions = {
    /**
     * Fetch questionnaires
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_QUESTIONNAIRES](context, params) {
        const { data } = await questionnaireService.get(
            context.state.project.slug,
            params
        );
        context.commit(SET_QUESTIONNAIRES, data);
        return data;
    },

    /**
     * Show questionnaire
     *
     * @param {*} context
     * @param {Number} slug questionnaire id
     * @returns questionnaire
     */
    async [SHOW_QUESTIONNAIRE](context, slug) {
        const { data } = await questionnaireService.show(
            context.state.project.slug,
            slug
        );
        context.commit(SET_QUESTIONNAIRE, data);
        return data;
    },

    /**
     * Add questionnaire
     *
     * @param {*} context
     * @param {Object} params questionnaire questionnaire values
     * @returns questionnaire
     */
    async [ADD_QUESTIONNAIRE](context, params) {
        const { data } = await questionnaireService.create(
            context.state.project.slug,
            params
        );
        context.commit(ADD_QUESTIONNAIRE, data);
        return data;
    },

    /**
     * update questionnaire
     *
     * @param {*} context
     * @param {Object} params new questionnaire questionnaire values
     */
    async [UPDATE_QUESTIONNAIRE](context, { slug, params }) {
        await questionnaireService.update(
            context.state.project.slug,
            slug,
            params
        );
        context.commit(UPDATE_QUESTIONNAIRE, { slug, params });
    },

    /**
     * remove questionnaire
     *
     * @param {*} context
     * @param {Number} params questionnaire id
     * @returns questionnaire
     */
    async [REMOVE_QUESTIONNAIRE](context, slug) {
        await questionnaireService.destroy(context.state.project.slug, slug);
        context.commit(REMOVE_QUESTIONNAIRE, slug);
    },
};

/**
 * questionnaire Store Mutations
 */
const mutations = {
    /**
     * Set questionnaires
     *
     * @param {*} state
     * @returns
     */
    [SET_QUESTIONNAIRES](state, questionnaires) {
        state.project.questionnaires = [...questionnaires];
    },

    /**
     * Set current questionnaire
     *
     * @param {*} state
     * @param {Object} questionnaire
     */
    [SET_QUESTIONNAIRE](state, questionnaire) {
        state.project.questionnaire = questionnaire;

        if (state.project.questionnaires) {
            state.project.questionnaires = state.project.questionnaires.map(
                (q) => (q.id == questionnaire.id ? questionnaire : q)
            );
        }
    },

    /**
     * Add questionnaire
     *
     * @param {*} state
     * @param {Number} questionnaire to append to questionnaires list
     */
    [ADD_QUESTIONNAIRE](state, questionnaire) {
        state.project.questionnaires = [
            ...(state.project.questionnaires
                ? state.project.questionnaires
                : []),
            questionnaire,
        ];
    },

    /**
     * Update questionnaire
     *
     * @param {*} state
     */
    [UPDATE_QUESTIONNAIRE](state, { slug, params }) {
        state.project.questionnaires = state.project.questionnaires.map((o) =>
            o.id == slug ? { ...o, ...params } : o
        );
    },

    /**
     * remove questionnaire
     *
     * @param {*} context
     * @param {Number} params questionnaire id
     */
    [REMOVE_QUESTIONNAIRE](state, slug) {
        state.project.questionnaires = state.project.questionnaires.filter(
            (o) => o.id != slug
        );
    },
};

/**
 * questionnaire Store Getters
 */
const getters = {
    /**
     * Get list of questionnaires
     *
     * @param {*} state
     * @returns
     */
    questionnaires: (state) =>
        state.project && state.project.questionnaires
            ? state.project.questionnaires
            : [],

    /**
     * Get current questionnaire
     *
     * @param {*} state
     * @returns
     */
    questionnaire: (state) =>
        state.project && state.project.questionnaire
            ? state.project.questionnaire
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
