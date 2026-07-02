import questionService from "@/apis/project/questionnaire/option";

import {
    FETCH_QUESTIONNAIRE_OPTIONS,
    SET_QUESTIONNAIRE_OPTIONS,
    SET_QUESTIONNAIRE_OPTION,
    SHOW_QUESTIONNAIRE_OPTION,
    ADD_QUESTIONNAIRE_OPTION,
    UPDATE_QUESTIONNAIRE_OPTION,
    REMOVE_QUESTIONNAIRE_OPTION,
} from "@/actions/project/questionnaire/option";

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
    async [FETCH_QUESTIONNAIRE_OPTIONS](
        context,
        { questionnaire, section, question }
    ) {
        const { data } = await questionService.get(
            context.state.project.slug,
            questionnaire,
            section,
            question
        );
        context.commit(SET_QUESTIONNAIRE_OPTIONS, {
            questionnaire,
            section,
            question,
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
    async [SHOW_QUESTIONNAIRE_OPTION](
        context,
        { questionnaire, section, question, slug }
    ) {
        const { data } = await questionService.show(
            context.state.project.slug,
            questionnaire,
            section,
            question,
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
    async [ADD_QUESTIONNAIRE_OPTION](
        context,
        { questionnaire, section, question, params }
    ) {
        const { data } = await questionService.create(
            context.state.project.slug,
            questionnaire,
            section,
            question,
            params
        );
        context.commit(ADD_QUESTIONNAIRE_OPTION, {
            questionnaire,
            section,
            question,
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
    async [UPDATE_QUESTIONNAIRE_OPTION](
        context,
        { questionnaire, section, question, params }
    ) {
        await questionService.update(
            context.state.project.slug,
            questionnaire,
            question,
            question,
            params.id,
            params
        );
        context.commit(UPDATE_QUESTIONNAIRE_OPTION, {
            questionnaire,
            section,
            question,
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
    async [REMOVE_QUESTIONNAIRE_OPTION](
        context,
        { questionnaire, section, question, slug }
    ) {
        await questionService.destroy(
            context.state.project.slug,
            questionnaire,
            section,
            question,
            slug
        );
        context.commit(REMOVE_QUESTIONNAIRE_OPTION, {
            questionnaire,
            section,
            question,
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
    [SET_QUESTIONNAIRE_OPTIONS](
        state,
        { questionnaire, section, question, data }
    ) {
        const q = state.project.questionnaires.find(
            (q) => q.id == questionnaire
        );

        if (q) {
            const s = q.sections
                ? q.sections.find((s) => s.id == section)
                : null;

            if (s) {
                const qs = s.questions
                    ? s.questions.find((qs) => qs.id == question)
                    : null;

                if (qs) {
                    qs.options = data;
                }
            }
        }
    },

    /**
     * Set questions
     *
     * @param {*} state
     * @returns
     */
    [SET_QUESTIONNAIRE_OPTION](state, option) {
        state.project.option = option;
    },

    /**
     * Add question
     *
     * @param {*} state
     * @param {Number} question to append to questions list
     */
    [ADD_QUESTIONNAIRE_OPTION](
        state,
        { questionnaire, section, question, data }
    ) {
        const q = state.project.questionnaires.find(
            (q) => q.id == questionnaire
        );

        if (q) {
            const s = q.sections.find((s) => s.id == section);

            if (s) {
                const qs = s.questions.find((qs) => qs.id == question);

                if (qs) {
                    qs.options = [...(qs.options ? qs.options : []), data];
                }
            }
        }
    },

    /**
     * Update question
     *
     * @param {*} state
     */
    [UPDATE_QUESTIONNAIRE_OPTION](
        state,
        { questionnaire, section, question, params }
    ) {
        const q = state.project.questionnaires.find(
            (q) => q.id == questionnaire
        );

        if (q) {
            const s = q.sections.find((s) => s.id == section);

            if (s) {
                const qs = s.questions.find((qs) => qs.id == question);

                if (qs) {
                    qs.options = qs.options.map((option) =>
                        option.id == params.id
                            ? { ...option, ...params }
                            : option
                    );
                }
            }
        }
    },

    /**
     * remove question
     *
     * @param {*} context
     * @param {Number} params question id
     */
    [REMOVE_QUESTIONNAIRE_OPTION](
        state,
        { questionnaire, section, question, slug }
    ) {
        const q = state.project.questionnaires.find(
            (q) => q.id == questionnaire
        );

        if (q) {
            const s = q.sections.find((s) => s.id == section);

            if (s) {
                const qs = s.questions.find((qs) => qs.id == question);

                if (qs) {
                    qs.options = qs.options.filter(
                        (option) => option.id != slug
                    );
                }
            }
        }
    },
};

/**
 * option Store Getters
 */
export const getters = {
    /**
     * Get current option
     *
     * @param {*} state
     * @returns
     */
    questionnaireOption: (state) =>
        state.project && state.project.option ? state.project.option : null,
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
