import messageTemplateService from "@/apis/project/message-template";

import {
    FETCH_MESSAGE_TEMPLATES,
    SET_MESSAGE_TEMPLATES,
    SET_MESSAGE_TEMPLATE,
    ADD_MESSAGE_TEMPLATE,
    SHOW_MESSAGE_TEMPLATE,
    UPDATE_MESSAGE_TEMPLATE,
    REMOVE_MESSAGE_TEMPLATE,
} from "@/actions/project/message-template";

/**
 * Message Template Store Actions
 */
const actions = {
    /**
     * Fetch message Templates
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_MESSAGE_TEMPLATES](context, params) {
        const { data } = await messageTemplateService.get(
            context.state.project.slug,
            params
        );
        context.commit(SET_MESSAGE_TEMPLATES, data);
        return data;
    },

    /**
     * Show messageTemplate
     *
     * @param {*} context
     * @param {Number} slug messageTemplate id
     * @returns messageTemplate
     */
    async [SHOW_MESSAGE_TEMPLATE](context, slug) {
        const { data } = await messageTemplateService.show(
            context.state.project.slug,
            slug
        );
        // context.commit(SET_MESSAGE_TEMPLATE, data);
        return data;
    },

    /**
     * Add messageTemplate
     *
     * @param {*} context
     * @param {Object} params messageTemplate messageTemplate values
     * @returns messageTemplate
     */
    async [ADD_MESSAGE_TEMPLATE](context, params) {
        const { data } = await messageTemplateService.create(
            context.state.project.slug,
            params
        );
        context.commit(ADD_MESSAGE_TEMPLATE, data);
        return data;
    },

    /**
     * update messageTemplate
     *
     * @param {*} context
     * @param {Object} params new messageTemplate messageTemplate values
     */
    async [UPDATE_MESSAGE_TEMPLATE](context, params) {
        await messageTemplateService.update(
            context.state.project.slug,
            params.id,
            params
        );
        context.commit(UPDATE_MESSAGE_TEMPLATE, params);
    },

    /**
     * remove messageTemplate
     *
     * @param {*} context
     * @param {Number} params messageTemplate id
     * @returns messageTemplate
     */
    async [REMOVE_MESSAGE_TEMPLATE](context, slug) {
        await messageTemplateService.destroy(context.state.project.slug, slug);
        context.commit(REMOVE_MESSAGE_TEMPLATE, slug);
    },
};

/**
 * messageTemplate Store Mutations
 */
const mutations = {
    /**
     * Set messageTemplates
     *
     * @param {*} state
     * @returns
     */
    [SET_MESSAGE_TEMPLATES](state, messageTemplates) {
        state.project.messageTemplates = [...messageTemplates];
    },

    /**
     * Set current messageTemplate
     *
     * @param {*} state
     * @param {Object} messageTemplate
     */
    [SET_MESSAGE_TEMPLATE](state, messageTemplate) {
        state.project.messageTemplate = messageTemplate;
    },

    /**
     * Add messageTemplate
     *
     * @param {*} state
     * @param {Number} messageTemplate to append to messageTemplates list
     */
    [ADD_MESSAGE_TEMPLATE](state, messageTemplate) {
        state.project.messageTemplates = [
            ...(state.project.messageTemplates ?? []),
            messageTemplate,
        ];
    },

    /**
     * Update messageTemplate
     *
     * @param {*} state
     */
    [UPDATE_MESSAGE_TEMPLATE](state, params) {
        state.project.messageTemplates = state.project.messageTemplates.map(
            (o) => (o.id == params.id ? { ...o, ...params } : o)
        );
    },

    /**
     * remove messageTemplate
     *
     * @param {*} context
     * @param {Number} params messageTemplate id
     */
    [REMOVE_MESSAGE_TEMPLATE](state, slug) {
        state.project.messageTemplates = state.project.messageTemplates.filter(
            (o) => o.id != slug
        );
    },
};

/**
 * messageTemplate Store Getters
 */
const getters = {
    /**
     * Get list of messageTemplates
     *
     * @param {*} state
     * @returns
     */
    messageTemplates: (state) =>
        state.project && state.project.messageTemplates
            ? state.project.messageTemplates
            : [],

    /**
     * Get current messageTemplate
     *
     * @param {*} state
     * @returns
     */
    messageTemplate: (state) =>
        state.project && state.project.messageTemplate
            ? state.project.messageTemplate
            : null,
};

/**
 * Store
 */
export default {
    actions,
    mutations,
    getters,
};
