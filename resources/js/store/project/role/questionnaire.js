import RoleQuestionnaireService from "@/apis/project/role/questionnaire";

import {
    FETCH_ROLE_QUESTIONNAIRES,
    SET_ROLE_QUESTIONNAIRES,
    ADD_ROLE_QUESTIONNAIRE,
    REMOVE_ROLE_QUESTIONNAIRE,
    BULK_ADD_ROLE_QUESTIONNAIRE,
    BULK_REMOVE_ROLE_QUESTIONNAIRE,
} from "@/actions/project/role/questionnaire";

/**
 * RoleQuestionnaire Store state
 */
export const state = {
    questionnaires: [],
};

/**
 * RoleQuestionnaire Store Actions
 */
export const actions = {
    /**
     * Fetch role questionnaires
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_ROLE_QUESTIONNAIRES](context, params) {
        const { data } = await RoleQuestionnaireService.get(
            context.state.project.slug,
            context.state.project.role.id,
            params
        );
        context.commit(SET_ROLE_QUESTIONNAIRES, data);
        return data;
    },

    /**
     * Add role questionnaire
     *
     * @param {*} context
     * @param {Object} params
     * @returns roleQuestionnaire
     */
    async [ADD_ROLE_QUESTIONNAIRE](context, questionnaire) {
        context.commit(ADD_ROLE_QUESTIONNAIRE, questionnaire);
        await RoleQuestionnaireService.update(
            context.state.project.slug,
            context.state.project.role.id,
            questionnaire.id
        );
    },

    /**
     * Remove role questionnaire
     *
     * @param {*} context
     * @param {Number} params role questionnaire id
     * @returns roleQuestionnaire
     */
    async [REMOVE_ROLE_QUESTIONNAIRE](context, questionnaire) {
        context.commit(REMOVE_ROLE_QUESTIONNAIRE, questionnaire);
        await RoleQuestionnaireService.destroy(
            context.state.project.slug,
            context.state.project.role.id,
            questionnaire.id
        );
    },

    /**
     * Add role questionnaire
     *
     * @param {*} context
     * @param {Object} params
     * @returns roleQuestionnaire
     */
    async [BULK_ADD_ROLE_QUESTIONNAIRE](context, { roles, questionnaires }) {
        await RoleQuestionnaireService.bulkUpdate(
            context.state.project.slug,
            roles,
            questionnaires
        );
    },

    /**
     * Remove role questionnaire
     *
     * @param {*} context
     * @param {Number} params role questionnaire id
     * @returns roleQuestionnaire
     */
    async [BULK_REMOVE_ROLE_QUESTIONNAIRE](context, { roles, questionnaires }) {
        await RoleQuestionnaireService.bulkDestroy(
            context.state.project.slug,
            roles,
            questionnaires
        );
    },
};

/**
 * Role questionnaire Store Mutations
 */
export const mutations = {
    /**
     * Set role questionnaires
     *
     * @param {*} state
     * @returns
     */
    [SET_ROLE_QUESTIONNAIRES](state, questionnaires) {
        state.project.role.questionnaires = questionnaires;
    },

    /**
     * Add role questionnaire
     *
     * @param {*} state
     * @param {Number} questionnaire to append to role questionnaires list
     */
    [ADD_ROLE_QUESTIONNAIRE](state, questionnaire) {
        state.project.role.questionnaires = [
            ...(state.project.role.questionnaires
                ? state.project.role.questionnaires
                : []),
            questionnaire,
        ];
        state.project.roles = state.project.roles.map((o) =>
            o.id == state.project.role.id ? { ...o, ...state.project.role } : o
        );
    },

    /**
     * Remove role questionnaire
     *
     * @param {*} context
     * @param {Number} params role questionnaire id
     */
    [REMOVE_ROLE_QUESTIONNAIRE](state, questionnaire) {
        state.project.role.questionnaires = (
            state.project.role.questionnaires
                ? state.project.role.questionnaires
                : []
        ).filter((o) => o.id != questionnaire.id);
        state.project.roles = state.project.roles.map((o) =>
            o.id == state.project.role.id ? { ...o, ...state.project.role } : o
        );
    },
};

/**
 * Role questionnaire Store Getters
 */
export const getters = {
    /**
     * Get list of role questionnaires
     *
     * @param {*} state
     * @returns
     */
    roleQuestionnaires: (state) =>
        state.project &&
        state.project.role &&
        state.project.role.questionnaires &&
        Array.isArray(state.project.role.questionnaires)
            ? state.project.role.questionnaires
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
