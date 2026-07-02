import ProspectEventService from "@/apis/project/prospect/event";

import {
    FETCH_PROSPECT_EVENTS,
    SET_PROSPECT_EVENTS,
    ADD_PROSPECT_EVENT,
    UPDATE_PROSPECT_EVENT,
    REMOVE_PROSPECT_EVENT,
} from "@/actions/project/prospect/event";

/**
 * ProspectEvent Store state
 */
export const state = {
    events: [],
};

/**
 * ProspectEvent Store Actions
 */
export const actions = {
    /**
     * Fetch prospect events
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PROSPECT_EVENTS](context, params) {
        const { data } = await ProspectEventService.get(
            context.state.project.slug,
            context.state.project.prospect.id,
            params
        );
        context.commit(SET_PROSPECT_EVENTS, data);
        return data;
    },

    /**
     * Add prospect event
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectEvent
     */
    async [ADD_PROSPECT_EVENT](context, params) {
        const { data } = await ProspectEventService.create(
            context.state.project.slug,
            context.state.project.prospect.id,
            params
        );
        context.commit(ADD_PROSPECT_EVENT, data);
    },

    /**
     * Update prospect event
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectEvent
     */
    async [UPDATE_PROSPECT_EVENT](context, params) {
        await ProspectEventService.update(
            context.state.project.slug,
            context.state.project.prospect.id,
            params.id,
            params
        );
        context.commit(UPDATE_PROSPECT_EVENT, params);
    },

    /**
     * Remove prospect event
     *
     * @param {*} context
     * @param {Number} params prospect event id
     * @returns prospectEvent
     */
    async [REMOVE_PROSPECT_EVENT](context, slug) {
        context.commit(REMOVE_PROSPECT_EVENT, slug);
        await ProspectEventService.destroy(
            context.state.project.slug,
            context.state.project.prospect.id,
            slug
        );
    },
};

/**
 * Prospect event Store Mutations
 */
export const mutations = {
    /**
     * Set prospect events
     *
     * @param {*} state
     * @returns
     */
    [SET_PROSPECT_EVENTS](state, events) {
        state.project.prospect.events = events;
    },

    /**
     * Add prospect event
     *
     * @param {*} state
     * @param {Number} event to append to prospect events list
     */
    [ADD_PROSPECT_EVENT](state, event) {
        state.project.prospect.events = [
            ...(state.project.prospect.events
                ? state.project.prospect.events
                : []),
            event,
        ];
        state.project.events = [
            ...(state.project.events ? state.project.events : []),
            ,
            event,
        ];
        state.project.prospects = state.project.prospects.map((o) =>
            o.id == state.project.prospect.id ? state.project.prospect : o
        );
    },

    /**
     * Update prospect event
     *
     * @param {*} state
     * @param {Number} event to append to prospect events list
     */
    [UPDATE_PROSPECT_EVENT](state, params) {
        state.project.prospect.events = (
            state.project.prospect.events ? state.project.prospect.events : []
        ).map((o) => (o.id == params.id ? { ...o, ...params } : o));

        state.project.prospects = state.project.prospects.map((o) =>
            o.id == state.project.prospect.id
                ? { ...o, ...state.project.prospect }
                : o
        );
    },

    /**
     * Remove prospect event
     *
     * @param {*} context
     * @param {Number} params prospect event id
     */
    [REMOVE_PROSPECT_EVENT](state, slug) {
        state.project.prospect.events = (
            state.project.prospect.events ? state.project.prospect.events : []
        ).filter((o) => o.id != slug);
        state.project.prospects = state.project.prospects.map((o) =>
            o.id == state.project.prospect.id ? state.project.prospect : o
        );
    },
};

/**
 * Prospect event Store Getters
 */
export const getters = {
    /**
     * Get list of prospect events
     *
     * @param {*} state
     * @returns
     */
    prospectEvents: (state) =>
        state.project && state.project.prospect && state.project.prospect.events
            ? state.project.prospect.events
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
