import questionService from "@/apis/project/questionnaire/question";

import {
    FETCH_QUESTIONNAIRE_QUESTIONS,
    SET_QUESTIONNAIRE_QUESTIONS,
    SET_QUESTIONNAIRE_QUESTION,
    SHOW_QUESTIONNAIRE_QUESTION,
    ADD_QUESTIONNAIRE_QUESTION,
    UPDATE_QUESTIONNAIRE_QUESTION,
    REMOVE_QUESTIONNAIRE_QUESTION,
} from "@/actions/project/questionnaire/question";

/**
 * group Store state
 */
export const state = {
    question: null,
};

/**
 * question Store Actions
 */
export const actions = {
    /**
     * Fetch questions
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_QUESTIONNAIRE_QUESTIONS](context, { questionnaire, section }) {
        const { data } = await questionService.get(
            context.state.project.slug,
            questionnaire,
            section
        );
        context.commit(SET_QUESTIONNAIRE_QUESTIONS, {
            questionnaire,
            section,
            data,
        });
        return data;
    },

    /**
     * Fetch questions
     *
     * @param {*} context
     * @returns
     */
    async [SHOW_QUESTIONNAIRE_QUESTION](
        context,
        { questionnaire, section, slug }
    ) {
        const { data } = await questionService.show(
            context.state.project.slug,
            questionnaire,
            section,
            slug
        );
        return data;
    },

    /**
     * Add question
     *
     * @param {*} context
     * @param {Object} params question section values
     * @returns question
     */
    async [ADD_QUESTIONNAIRE_QUESTION](
        context,
        { questionnaire, section, params }
    ) {
        const { data } = await questionService.create(
            context.state.project.slug,
            questionnaire,
            section,
            params
        );
        context.commit(ADD_QUESTIONNAIRE_QUESTION, {
            questionnaire,
            section,
            data,
        });
        return data;
    },

    /**
     * update question
     *
     * @param {*} context
     * @param {Object} params new question question values
     */
    async [UPDATE_QUESTIONNAIRE_QUESTION](
        context,
        { questionnaire, section, params }
    ) {
        await questionService.update(
            context.state.project.slug,
            questionnaire,
            section,
            params.id,
            params
        );
        context.commit(UPDATE_QUESTIONNAIRE_QUESTION, {
            questionnaire,
            section,
            params,
        });
    },

    /**
     * remove question
     *
     * @param {*} context
     * @param {Number} params question id
     * @returns question
     */
    async [REMOVE_QUESTIONNAIRE_QUESTION](
        context,
        { questionnaire, section, slug }
    ) {
        await questionService.destroy(
            context.state.project.slug,
            questionnaire,
            section,
            slug
        );
        context.commit(REMOVE_QUESTIONNAIRE_QUESTION, {
            questionnaire,
            section,
            slug,
        });
    },
};

/**
 * question Store Mutations
 */
export const mutations = {
    /**
     * Set questions
     *
     * @param {*} state
     * @returns
     */
    [SET_QUESTIONNAIRE_QUESTIONS](state, { questionnaire, section, data }) {
        const q = state.project.questionnaires.find(
            (q) => q.id == questionnaire
        );

        if (state.project.questionnaire) {
            const s = q.sections
                ? q.sections.find((s) => s.id == section)
                : null;

            if (s) {
                s.questions = data;
            }
        }
    },

    /**
     * Set questions
     *
     * @param {*} state
     * @returns
     */
    [SET_QUESTIONNAIRE_QUESTION](state, question) {
        state.project.question = question;
    },

    /**
     * Add question
     *
     * @param {*} state
     * @param {Number} question to append to questions list
     */
    [ADD_QUESTIONNAIRE_QUESTION](state, { questionnaire, section, data }) {
        const q = state.project.questionnaires.find(
            (q) => q.id == questionnaire
        );

        if (q) {
            const s = q.sections
                ? q.sections.find((s) => s.id == section)
                : null;

            if (s) {
                s.questions = [...(s.questions ? s.questions : []), data];
            }
        }
    },

    /**
     * Update question
     *
     * @param {*} state
     */
    [UPDATE_QUESTIONNAIRE_QUESTION](state, { questionnaire, section, params }) {
        const q = state.project.questionnaires.find(
            (q) => q.id == questionnaire
        );

        if (q) {
            const s = q.sections.find((s) => s.id == section);

            if (s) {
                s.questions = s.questions.map((question) =>
                    question.id == params.id
                        ? { ...question, ...params }
                        : question
                );
            }
        }
    },

    /**
     * remove question
     *
     * @param {*} context
     * @param {Number} params question id
     */
    [REMOVE_QUESTIONNAIRE_QUESTION](state, { questionnaire, section, slug }) {
        const q = state.project.questionnaires.find(
            (q) => q.id == questionnaire
        );

        if (q) {
            const s = q.sections.find((s) => s.id == section);

            if (s) {
                s.questions = s.questions.filter(
                    (question) => question.id != slug
                );
            }
        }
    },
};

/**
 * question Store Getters
 */
export const getters = {
    /**
     * Get current question
     *
     * @param {*} state
     * @returns
     */
    questionnaireQuestion: (state) =>
        state.project && state.project.question ? state.project.question : null,
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
