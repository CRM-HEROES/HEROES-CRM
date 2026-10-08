import lineService from "@/apis/project/line";

import {
    FETCH_LINES,
    SET_LINES,
    SET_LINE,
    ADD_LINE,
    SHOW_LINE,
    UPDATE_LINE,
    REMOVE_LINE,
    FETCH_LINE_AVAILABLE_USERS,
    MAKE_CLOUDTALK_CALL,
    LOOKUP_CLOUDTALK_CALL,
    FETCH_CLOUDTALK_CALL_HISTORY,
} from "@/actions/project/line";

/**
 * line Store Actions
 */
const actions = {
    /**
     * Fetch lines
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_LINES](context, params) {
        const { data } = await lineService.get(
            context.state.project.slug,
            params
        );
        context.commit(SET_LINES, data);
        return data;
    },

    /**
     * Show line
     *
     * @param {*} context
     * @param {Number} slug line id
     * @returns line
     */
    async [SHOW_LINE](context, slug) {
        const { data } = await lineService.show(
            context.state.project.slug,
            slug
        );
        return data;
    },

    /**
     * Fetch users that do not already have a line config for an operator.
     *
     * @param {*} context
     * @param {Object} params query params
     * @returns users
     */
    async [FETCH_LINE_AVAILABLE_USERS](context, params) {
        const { data } = await lineService.availableUsers(
            context.state.project.slug,
            params
        );
        return data;
    },

    /**
     * Add line
     *
     * @param {*} context
     * @param {Object} params line values
     * @returns line
     */
    async [ADD_LINE](context, params) {
        const { data } = await lineService.create(
            context.state.project.slug,
            params
        );
        context.commit(ADD_LINE, data);
        return data;
    },

    /**
     * update line
     *
     * @param {*} context
     * @param {Object} params new line values
     */
    async [UPDATE_LINE](context, params) {
        await lineService.update(
            context.state.project.slug,
            params.id,
            params
        );
        context.commit(UPDATE_LINE, params);
    },

    /**
     * Make an outbound CloudTalk call.
     */
    async [MAKE_CLOUDTALK_CALL](context, params) {
        const { data } = await lineService.makeCloudtalkCall(
            context.state.project.slug,
            params
        );
        return data;
    },

    /**
     * Resolve a CloudTalk call number to the current user's prospect context.
     */
    async [LOOKUP_CLOUDTALK_CALL](context, params) {
        const { data } = await lineService.lookupCloudtalkCall(
            context.state.project.slug,
            params
        );
        return data;
    },

    /**
     * Fetch recent CloudTalk call history for the current agent.
     */
    async [FETCH_CLOUDTALK_CALL_HISTORY](context, params) {
        const { data } = await lineService.fetchCloudtalkCallHistory(
            context.state.project.slug,
            params
        );
        return data;
    },

    /**
     * remove line
     *
     * @param {*} context
     * @param {Number} params line id
     * @returns line
     */
    async [REMOVE_LINE](context, slug) {
        await lineService.destroy(context.state.project.slug, slug);
        context.commit(REMOVE_LINE, slug);
    },
};

/**
 * line Store Mutations
 */
const mutations = {
    /**
     * Set lines
     *
     * @param {*} state
     * @returns
     */
    [SET_LINES](state, lines) {
        state.project.lines = [...lines];
    },

    /**
     * Set current line
     *
     * @param {*} state
     * @param {Object} line
     */
    [SET_LINE](state, line) {
        state.project.line = line;
    },

    /**
     * Add line
     *
     * @param {*} state
     * @param {Number} line to append to lines list
     */
    [ADD_LINE](state, line) {
        state.project.lines = [...(state.project.lines ?? []), line];
    },

    /**
     * Update line
     *
     * @param {*} state
     */
    [UPDATE_LINE](state, params) {
        state.project.lines = state.project.lines.map((o) =>
            o.id == params.id ? { ...o, ...params } : o
        );
    },

    /**
     * remove line
     *
     * @param {*} context
     * @param {Number} params line id
     */
    [REMOVE_LINE](state, slug) {
        state.project.lines = state.project.lines.filter((o) => o.id != slug);
    },
};

/**
 * line Store Getters
 */
const getters = {
    /**
     * Get list of lines
     *
     * @param {*} state
     * @returns
     */
    lines: (state) =>
        state.project && state.project.lines ? state.project.lines : [],

    /**
     * Get current line
     *
     * @param {*} state
     * @returns
     */
    line: (state) =>
        state.project && state.project.line ? state.project.line : null,
};

/**
 * Store
 */
export default {
    actions,
    mutations,
    getters,
};
