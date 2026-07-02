import DefaultFieldService from "@/apis/default-field";

import {
    FETCH_DEFAULT_FIELDS,
    SET_DEFAULT_FIELDS,
} from "@/actions/default-field";

/**
 * Campaign Store state
 */
const state = {
    defaultFields: [],
};

/**
 * Defautl Field Store Actions
 */
const actions = {
    /**
     * Fetch documents
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_DEFAULT_FIELDS](context) {
        const { data } = await DefaultFieldService.get();
        context.commit(SET_DEFAULT_FIELDS, data);
        return data;
    },
};

/**
 * Campaign Store Mutations
 */
const mutations = {
    /**
     * Set default fields
     *
     * @param {*} state
     * @returns
     */
    [SET_DEFAULT_FIELDS](state, fields) {
        state.defaultFields = fields;
    },
};

/**
 * Campaign Store Getters
 */
const getters = {
    /**
     * Get modal
     *
     * @param {*} state
     * @returns
     */
    defaultFields: (state) => state.defaultFields,
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
