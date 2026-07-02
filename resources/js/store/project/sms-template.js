import smsTemplateService from "@/apis/project/sms-template";

import {
    FETCH_SMS_TEMPLATES,
    SET_SMS_TEMPLATES,
    SET_SMS_TEMPLATE,
    ADD_SMS_TEMPLATE,
    SHOW_SMS_TEMPLATE,
    UPDATE_SMS_TEMPLATE,
    REMOVE_SMS_TEMPLATE,
} from "@/actions/project/sms-template";

/**
 * Sms Template Store Actions
 */
const actions = {
    /**
     * Fetch sms Templates
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_SMS_TEMPLATES](context, params) {
        const { data } = await smsTemplateService.get(
            context.state.project.slug,
            params
        );
        context.commit(SET_SMS_TEMPLATES, data);
        return data;
    },

    /**
     * Show smsTemplate
     *
     * @param {*} context
     * @param {Number} slug smsTemplate id
     * @returns smsTemplate
     */
    async [SHOW_SMS_TEMPLATE](context, slug) {
        const { data } = await smsTemplateService.show(
            context.state.project.slug,
            slug
        );
        // context.commit(SET_SMS_TEMPLATE, data);
        return data;
    },

    /**
     * Add smsTemplate
     *
     * @param {*} context
     * @param {Object} params smsTemplate smsTemplate values
     * @returns smsTemplate
     */
    async [ADD_SMS_TEMPLATE](context, params) {
        const { data } = await smsTemplateService.create(
            context.state.project.slug,
            params
        );
        context.commit(ADD_SMS_TEMPLATE, data);
        return data;
    },

    /**
     * update smsTemplate
     *
     * @param {*} context
     * @param {Object} params new smsTemplate smsTemplate values
     */
    async [UPDATE_SMS_TEMPLATE](context, params) {
        await smsTemplateService.update(
            context.state.project.slug,
            params.id,
            params
        );
        context.commit(UPDATE_SMS_TEMPLATE, params);
    },

    /**
     * remove smsTemplate
     *
     * @param {*} context
     * @param {Number} params smsTemplate id
     * @returns smsTemplate
     */
    async [REMOVE_SMS_TEMPLATE](context, slug) {
        await smsTemplateService.destroy(context.state.project.slug, slug);
        context.commit(REMOVE_SMS_TEMPLATE, slug);
    },
};

/**
 * smsTemplate Store Mutations
 */
const mutations = {
    /**
     * Set smsTemplates
     *
     * @param {*} state
     * @returns
     */
    [SET_SMS_TEMPLATES](state, smsTemplates) {
        state.project.smsTemplates = [...smsTemplates];
    },

    /**
     * Set current smsTemplate
     *
     * @param {*} state
     * @param {Object} smsTemplate
     */
    [SET_SMS_TEMPLATE](state, smsTemplate) {
        state.project.smsTemplate = smsTemplate;
    },

    /**
     * Add smsTemplate
     *
     * @param {*} state
     * @param {Number} smsTemplate to append to smsTemplates list
     */
    [ADD_SMS_TEMPLATE](state, smsTemplate) {
        state.project.smsTemplates = [
            ...(state.project.smsTemplates ?? []),
            smsTemplate,
        ];
    },

    /**
     * Update smsTemplate
     *
     * @param {*} state
     */
    [UPDATE_SMS_TEMPLATE](state, params) {
        state.project.smsTemplates = state.project.smsTemplates.map((o) =>
            o.id == params.id ? { ...o, ...params } : o
        );
    },

    /**
     * remove smsTemplate
     *
     * @param {*} context
     * @param {Number} params smsTemplate id
     */
    [REMOVE_SMS_TEMPLATE](state, slug) {
        state.project.smsTemplates = state.project.smsTemplates.filter(
            (o) => o.id != slug
        );
    },
};

/**
 * smsTemplate Store Getters
 */
const getters = {
    /**
     * Get list of smsTemplates
     *
     * @param {*} state
     * @returns
     */
    smsTemplates: (state) =>
        state.project && state.project.smsTemplates
            ? state.project.smsTemplates
            : [],

    /**
     * Get current smsTemplate
     *
     * @param {*} state
     * @returns
     */
    smsTemplate: (state) =>
        state.project && state.project.smsTemplate
            ? state.project.smsTemplate
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
