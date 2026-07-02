import ProspectSmsService from "@/apis/project/prospect/sms";

import {
    FETCH_PROSPECT_SMSS,
    SET_PROSPECT_SMSS,
    ADD_PROSPECT_SMS,
    BULK_PROSPECT_SMS,
} from "@/actions/project/prospect/sms";

/**
 * ProspectSms Store state
 */
export const state = {
    sms: [],
};

/**
 * ProspectSms Store Actions
 */
export const actions = {
    /**
     * Fetch prospect sms
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PROSPECT_SMSS](context, params) {
        const { data } = await ProspectSmsService.get(
            context.state.project.slug,
            context.state.project.prospect.id,
            params
        );
        context.commit(SET_PROSPECT_SMSS, data);
        return data;
    },

    /**
     * Add prospect sms
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectSms
     */
    async [ADD_PROSPECT_SMS](context, params) {
        const { data } = await ProspectSmsService.create(
            context.state.project.slug,
            context.state.project.prospect.id,
            params
        );
        context.commit(ADD_PROSPECT_SMS, data);
        return data;
    },

    /**
     * Bulk prospect sms
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectSms
     */
    async [BULK_PROSPECT_SMS](context, params) {
        const { data } = await ProspectSmsService.bulk(
            context.state.project.slug,
            params
        );
    },
};

/**
 * Prospect sms Store Mutations
 */
export const mutations = {
    /**
     * Set prospect sms
     *
     * @param {*} state
     * @returns
     */
    [SET_PROSPECT_SMSS](state, sms) {
        state.project.prospect.sms = sms;
    },

    /**
     * Add prospect sms
     *
     * @param {*} state
     * @param {Number} sms to append to prospect sms list
     */
    [ADD_PROSPECT_SMS](state, sms) {
        state.project.prospect.sms = [
            ...(state.project.prospect.sms ? state.project.prospect.sms : []),
            sms,
        ];
    },
};

/**
 * Prospect sms Store Getters
 */
export const getters = {
    /**
     * Get list of prospect sms
     *
     * @param {*} state
     * @returns
     */
    prospectSms: (state) =>
        state.project && state.project.prospect && state.project.prospect.sms
            ? state.project.prospect.sms
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
