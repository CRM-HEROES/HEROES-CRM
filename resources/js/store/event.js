import EventService from "@/apis/event";

import {
    FETCH_GLOBAL_EVENTS,
    SET_GLOBAL_EVENTS,
    ADD_GLOBAL_EVENT_PARAMS,
    SET_GLOBAL_EVENT_PARAMS,
    REMOVE_GLOBAL_EVENT_PARAMS,
    INIT_GLOBAL_EVENT_PARAMS,
    SET_GLOBAL_EVENTS_COUNT,
} from "@/actions/event";

/**
 * event Store state
 */
export const state = {
    globalEvents: [],
    globalEventsParams: {},
};

/**
 * event Store Actions
 */
const actions = {
    /**
     * Fetch events
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_GLOBAL_EVENTS](context) {
        let params = {};

        if (
            context.state.globalEventsParams &&
            Object.keys(context.state.globalEventsParams).length > 0
        ) {
            params.filters = JSON.stringify(
                context.state.globalEventsParams
                    ? context.state.globalEventsParams
                    : {}
            );
        }

        const { data } = await EventService.get({
            params: params,
        });
        context.commit(SET_GLOBAL_EVENTS, data);
        return data;
    },
};

/**
 * event Store Mutations
 */
const mutations = {
    /**
     * Set events
     *
     * @param {*} state
     * @returns
     */
    [SET_GLOBAL_EVENTS](state, events) {
        state.globalEvents = [...events];
    },

    /**
     * @param {*} state
     */
    [ADD_GLOBAL_EVENT_PARAMS](state, { key, value, multiple }) {
        state.prospectsPage = 1;

        if (!state.globalEventsParams) {
            state.globalEventsParams = {};
        }

        if (value == undefined) {
            value = "";
        }

        if (multiple) {
            if (state.globalEventsParams[key] === undefined) {
                state.globalEventsParams[key] = [];
            }

            let values = "" + value;
            if (state.globalEventsParams[key] !== "") {
                values = [...state.globalEventsParams[key], value];
            }

            state.globalEventsParams = Object.fromEntries(
                Object.entries(state.globalEventsParams).map(([k, v]) => [
                    k,
                    k == key ? values : v,
                ])
            );
        } else {
            state.globalEventsParams = {
                ...state.globalEventsParams,
                [key]: value,
            };
        }
    },

    /**
     * @param {*} state
     */
    [REMOVE_GLOBAL_EVENT_PARAMS](state, { key, value }) {
        state.prospectsPage = 1;

        if (!state.globalEventsParams) {
            state.globalEventsParams = {};
        }

        if (value !== undefined) {
            if (state.globalEventsParams[key]) {
                state.globalEventsParams = Object.fromEntries(
                    Object.entries(state.globalEventsParams).map(([k, v]) => [
                        k,
                        k == key
                            ? state.globalEventsParams[k].filter(
                                  (val) => val != value
                              )
                            : v,
                    ])
                );

                if (state.globalEventsParams[key].length == 0) {
                    state.globalEventsParams = Object.fromEntries(
                        Object.entries(state.globalEventsParams).filter(
                            ([k]) => k != key
                        )
                    );
                }
            }
        } else {
            state.globalEventsParams = Object.fromEntries(
                Object.entries(state.globalEventsParams).filter(
                    ([k]) => k != key
                )
            );
        }
    },

    /**
     * @param {*} state
     */
    [SET_GLOBAL_EVENT_PARAMS](state, params) {
        if (typeof params === "object") {
            state.globalEventsParams = params;
        }
    },

    /**
     * @param {*} state
     */
    [INIT_GLOBAL_EVENT_PARAMS](state) {
        state.prospectsPage = 1;

        state.globalEventsParams = {}; /*Object.fromEntries(
            Object.entries(state.globalEventsParams).filter(([k]) => k == "count")
        );*/
    },

    /**
     * @param {*} state
     */
    [SET_GLOBAL_EVENTS_COUNT](state, count) {
        state.globalEventsCount = count;
    },
};

/**
 * event Store Getters
 */
const getters = {
    /**
     * Get list of events
     *
     * @param {*} state
     * @returns
     */
    globalEvents: (state) => (state.globalEvents ? state.globalEvents : []),

    /**
     * @param {*} state
     * @returns
     */
    globalEventsParamsUrl: (state) =>
        state.globalEventsParams &&
        Object.keys(state.globalEventsParams).length > 0
            ? encodeURI(JSON.stringify(state.globalEventsParams))
            : "",

    /**
     * @param {*} state
     * @returns
     */
    globalEventsParamsValue: (state) => (param) =>
        state.globalEventsParams ? state.globalEventsParams[param] : undefined,

    /**
     * @param {*} state
     * @returns
     */
    globalEventsParamExists: (state) => (param, value) => {
        if (!state || !state.globalEventsParams) {
            return false;
        }

        if (param === undefined) {
            return Object.keys(state.globalEventsParams).length > 0;
        }

        if (value === undefined) {
            return state.globalEventsParams[param] !== undefined;
        }

        return (
            Array.isArray(state.globalEventsParams[param]) &&
            state.globalEventsParams[param].findIndex((p) => p == value) >= 0
        );
    },

    /**
     * @param {*} state
     * @returns
     */
    globalEventsParamsCount: (state) => (param) => {
        if (
            !state ||
            !state.globalEventsParams ||
            state.globalEventsParams[param] === undefined
        ) {
            return 0;
        }

        if (!Array.isArray(state.globalEventsParams[param])) {
            return 1;
        }

        return state.globalEventsParams[param].length;
    },
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
