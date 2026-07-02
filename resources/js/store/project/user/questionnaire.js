import UserQuestionnaireService from "@/apis/project/user/questionnaire";

import {
    FETCH_USER_QUESTIONNAIRES,
    SET_USER_QUESTIONNAIRES,
    ADD_USER_QUESTIONNAIRE,
    REMOVE_USER_QUESTIONNAIRE,
    BULK_ADD_USER_QUESTIONNAIRE,
    BULK_REMOVE_USER_QUESTIONNAIRE,
} from "@/actions/project/user/questionnaire";

/**
 * UserQuestionnaire Store state
 */
export const state = {
    questionnaires: [],
};

/**
 * UserQuestionnaire Store Actions
 */
export const actions = {
    /**
     * Fetch user questionnaires
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_USER_QUESTIONNAIRES](context, params) {
        const { data } = await UserQuestionnaireService.get(
            context.state.project.slug,
            context.state.project.user.id,
            params
        );
        context.commit(SET_USER_QUESTIONNAIRES, data);
        return data;
    },

    /**
     * Add user questionnaire
     *
     * @param {*} context
     * @param {Object} params
     * @returns userQuestionnaire
     */
    async [ADD_USER_QUESTIONNAIRE](context, questionnaire) {
        context.commit(ADD_USER_QUESTIONNAIRE, questionnaire);
        await UserQuestionnaireService.update(
            context.state.project.slug,
            context.state.project.user.id,
            questionnaire.id
        );
    },

    /**
     * Remove user questionnaire
     *
     * @param {*} context
     * @param {Number} params user questionnaire id
     * @returns userQuestionnaire
     */
    async [REMOVE_USER_QUESTIONNAIRE](context, questionnaire) {
        context.commit(REMOVE_USER_QUESTIONNAIRE, questionnaire);
        await UserQuestionnaireService.destroy(
            context.state.project.slug,
            context.state.project.user.id,
            questionnaire.id
        );
    },

    /**
     * Add user questionnaire
     *
     * @param {*} context
     * @param {Object} params
     * @returns userQuestionnaire
     */
    async [BULK_ADD_USER_QUESTIONNAIRE](context, { users, questionnaires }) {
        await UserQuestionnaireService.bulkUpdate(
            context.state.project.slug,
            users,
            questionnaires
        );
    },

    /**
     * Remove user questionnaire
     *
     * @param {*} context
     * @param {Number} params user questionnaire id
     * @returns userQuestionnaire
     */
    async [BULK_REMOVE_USER_QUESTIONNAIRE](context, { users, questionnaires }) {
        await UserQuestionnaireService.bulkDestroy(
            context.state.project.slug,
            users,
            questionnaires
        );
    },
};

/**
 * User questionnaire Store Mutations
 */
export const mutations = {
    /**
     * Set user questionnaires
     *
     * @param {*} state
     * @returns
     */
    [SET_USER_QUESTIONNAIRES](state, questionnaires) {
        state.project.user.questionnaires = questionnaires;
    },

    /**
     * Add user questionnaire
     *
     * @param {*} state
     * @param {Number} questionnaire to append to user questionnaires list
     */
    [ADD_USER_QUESTIONNAIRE](state, questionnaire) {
        state.project.user.questionnaires = [
            ...(state.project.user.questionnaires
                ? state.project.user.questionnaires
                : []),
            questionnaire,
        ];
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },

    /**
     * Remove user questionnaire
     *
     * @param {*} context
     * @param {Number} params user questionnaire id
     */
    [REMOVE_USER_QUESTIONNAIRE](state, questionnaire) {
        state.project.user.questionnaires = (
            state.project.user.questionnaires
                ? state.project.user.questionnaires
                : []
        ).filter((o) => o.id != questionnaire.id);
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },
};

/**
 * User questionnaire Store Getters
 */
export const getters = {
    /**
     * Get list of user questionnaires
     *
     * @param {*} state
     * @returns
     */
    userQuestionnaires: (state) =>
        state.project &&
        state.project.user &&
        state.project.user.questionnaires &&
        Array.isArray(state.project.user.questionnaires)
            ? state.project.user.questionnaires
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
