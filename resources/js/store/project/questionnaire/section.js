import sectionService from "@/apis/project/questionnaire/section";

import {
    FETCH_QUESTIONNAIRE_SECTIONS,
    SET_QUESTIONNAIRE_SECTIONS,
    SET_QUESTIONNAIRE_SECTION,
    SHOW_QUESTIONNAIRE_SECTION,
    ADD_QUESTIONNAIRE_SECTION,
    UPDATE_QUESTIONNAIRE_SECTION,
    REMOVE_QUESTIONNAIRE_SECTION,
} from "@/actions/project/questionnaire/section";

/**
 * group Store state
 */
export const state = {
    section: null,
};

/**
 * section Store Actions
 */
export const actions = {
    /**
     * Fetch sections
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_QUESTIONNAIRE_SECTIONS](context, questionnaire) {
        const { data } = await sectionService.get(
            context.state.project.slug,
            questionnaire
        );
        context.commit(SET_QUESTIONNAIRE_SECTIONS, { questionnaire, data });
        return data;
    },

    /**
     * Fetch sections
     *
     * @param {*} context
     * @returns
     */
    async [SHOW_QUESTIONNAIRE_SECTION](context, { questionnaire, slug }) {
        const { data } = await sectionService.show(
            context.state.project.slug,
            questionnaire,
            slug
        );
        return data;
    },

    /**
     * Add section
     *
     * @param {*} context
     * @param {Object} params section section values
     * @returns section
     */
    async [ADD_QUESTIONNAIRE_SECTION](context, { questionnaire, params }) {
        const { data } = await sectionService.create(
            context.state.project.slug,
            questionnaire,
            params
        );
        context.commit(ADD_QUESTIONNAIRE_SECTION, { questionnaire, data });
        return data;
    },

    /**
     * update section
     *
     * @param {*} context
     * @param {Object} params new section section values
     */
    async [UPDATE_QUESTIONNAIRE_SECTION](context, { questionnaire, params }) {
        await sectionService.update(
            context.state.project.slug,
            questionnaire,
            params.id,
            params
        );
        context.commit(UPDATE_QUESTIONNAIRE_SECTION, { questionnaire, params });
    },

    /**
     * remove section
     *
     * @param {*} context
     * @param {Number} params section id
     * @returns section
     */
    async [REMOVE_QUESTIONNAIRE_SECTION](context, { questionnaire, slug }) {
        await sectionService.destroy(
            context.state.project.slug,
            questionnaire,
            slug
        );
        context.commit(REMOVE_QUESTIONNAIRE_SECTION, { questionnaire, slug });
    },
};

/**
 * section Store Mutations
 */
export const mutations = {
    /**
     * Set sections
     *
     * @param {*} state
     * @returns
     */
    [SET_QUESTIONNAIRE_SECTIONS](state, { questionnaire, data }) {
        const cat = state.project.questionnaires.find(
            (c) => c.id == questionnaire
        );

        if (cat) {
            cat.sections = data;
        }
    },

    /**
     * Set sections
     *
     * @param {*} state
     * @returns
     */
    [SET_QUESTIONNAIRE_SECTION](state, section) {
        state.project.section = section;
    },

    /**
     * Add section
     *
     * @param {*} state
     * @param {Number} section to append to sections list
     */
    [ADD_QUESTIONNAIRE_SECTION](state, { questionnaire, data }) {
        const cat = state.project.questionnaires.find(
            (c) => c.id == questionnaire
        );

        if (cat) {
            cat.sections = [...(cat.sections ? cat.sections : []), data];
        }
    },

    /**
     * Update section
     *
     * @param {*} state
     */
    [UPDATE_QUESTIONNAIRE_SECTION](state, { questionnaire, params }) {
        const cat = state.project.questionnaires.find(
            (c) => c.id == questionnaire
        );

        if (cat) {
            cat.sections = cat.sections.map((section) =>
                section.id == params.id ? { ...section, ...params } : section
            );
        }
    },

    /**
     * remove section
     *
     * @param {*} context
     * @param {Number} params section id
     */
    [REMOVE_QUESTIONNAIRE_SECTION](state, { questionnaire, slug }) {
        const cat = state.project.questionnaires.find(
            (c) => c.id == questionnaire
        );

        if (cat) {
            cat.sections = cat.sections.filter((section) => section.id != slug);
        }
    },
};

/**
 * section Store Getters
 */
export const getters = {
    /**
     * Get current section
     *
     * @param {*} state
     * @returns
     */
    questionnaireSection: (state) =>
        state.project && state.project.section ? state.project.section : null,
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
