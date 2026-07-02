import ProspectLogService from "@/apis/project/prospect/log";

import {
    FETCH_PROSPECT_LOGS,
    SET_PROSPECT_LOGS,
} from "@/actions/project/prospect/log";

/**
 * ProspectLog Store state
 */
export const state = {
    logs: [],
};

/**
 * ProspectLog Store Actions
 */
export const actions = {
    /**
     * Fetch prospect logs
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PROSPECT_LOGS](context, params) {
        const { data } = await ProspectLogService.get(
            context.state.project.slug,
            context.state.project.prospect.id,
            params
        );
        context.commit(SET_PROSPECT_LOGS, data);
        return data;
    },
};

/**
 * Prospect log Store Mutations
 */
export const mutations = {
    /**
     * Set prospect logs
     *
     * @param {*} state
     * @returns
     */
    [SET_PROSPECT_LOGS](state, logs) {
        state.project.prospect.logs = logs;
    },
};

/**
 * Prospect log Store Getters
 */
export const getters = {
    /**
     * Get list of prospect logs
     *
     * @param {*} state
     * @returns
     */
    prospectLogs: (state) =>
        state.project && state.project.prospect && state.project.prospect.logs
            ? state.project.prospect.logs
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
