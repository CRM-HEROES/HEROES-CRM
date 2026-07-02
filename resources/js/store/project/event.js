import EventService from "@/apis/project/event";

import {
    FETCH_EVENTS,
    SET_EVENTS,
    SET_EVENT,
    ADD_EVENT,
    SHOW_EVENT,
    UPDATE_EVENT,
    REMOVE_EVENT,
    SET_EVENTS_FIELDS,
    SET_NEW_EVENT,
    SET_DRAGGED_EVENT,
    SET_RESIZED_EVENT,
    SET_AGENDA_DATE,
    SET_AGENDA_FILTER,
    SET_AGENDA_LIST,
    SHOW_AGENDA_TABLE,
    SHOW_AGENDA_WEEK_END,
    SHOW_AGENDA_OTHER_PROJECTS,
    SET_AGENDA_DISPLAY_MODE,
    ADD_EVENT_PARAMS,
    SET_EVENT_PARAMS,
    REMOVE_EVENT_PARAMS,
    INIT_EVENT_PARAMS,
    SET_EVENTS_COUNT,
    SET_EVENTS_SORT_BY,
    SET_EVENTS_SORT_ORDER,
    FETCH_EVENT_USER_DAILY_DIRECTION,
    ADD_EVENT_USER_DAILY_DIRECTION,
    ADD_EVENT_USER,
    REMOVE_EVENT_USER,
} from "@/actions/project/event";

/**
 * event Store state
 */
export const state = {
    events: [],
    event: null,
    newEvent: null,
    draggedEvent: null,
    resizedEvent: null,
    eventsParams: {},
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
    async [FETCH_EVENTS](context) {
        let params = {};

        if (
            context.state.project.eventsParams &&
            Object.keys(context.state.project.eventsParams).length > 0
        ) {
            params.filters = JSON.stringify(
                context.state.project.eventsParams
                    ? context.state.project.eventsParams
                    : {}
            );
        }

        if (context.state.project.eventsFields) {
            params.fields = context.state.project.eventsFields;
        }

        const { data } = await EventService.get(context.state.project.slug, {
            params: params,
        });
        context.commit(SET_EVENTS, data);
        return data;
    },

    /**
     * Show event
     *
     * @param {*} context
     * @param {Number} slug event id
     * @returns event
     */
    async [SHOW_EVENT](context, slug) {
        const { data } = await EventService.show(
            context.state.project.slug,
            slug
        );
        context.commit(SET_EVENT, data);
        return data;
    },

    /**
     * Add event
     *
     * @param {*} context
     * @param {Object} params event field values
     * @returns event
     */
    async [ADD_EVENT](context, params) {
        const { data } = await EventService.create(
            context.state.project.slug,
            params
        );
        context.commit(ADD_EVENT, data);
        return data;
    },

    /**
     * update event
     *
     * @param {*} context
     * @param {Object} params new event field values
     */
    async [UPDATE_EVENT](context, params) {
        await EventService.update(
            context.state.project.slug,
            params.id,
            params
        );
        context.commit(UPDATE_EVENT, params);
    },

    /**
     * remove event
     *
     * @param {*} context
     * @param {Number} params event id
     * @returns event
     */
    async [REMOVE_EVENT](context, slug) {
        await EventService.destroy(context.state.project.slug, slug);
        context.commit(REMOVE_EVENT, slug);
    },

    /**
     * remove event
     *
     * @param {*} context
     * @param {Number} params event id
     * @returns event
     */
    async [FETCH_EVENT_USER_DAILY_DIRECTION](context, { user, direction_at }) {
        let { data } = await EventService.dailyDirection(
            // context.state.project.slug,
            user,
            direction_at
        );

        data = {
            user_id: user.id,
            direction_at: direction_at,
            events: data.events ? JSON.parse(data.events) : [],
            direction: data.direction ? JSON.parse(data.direction) : null,
        };

        /*context.commit(ADD_EVENT_USER_DAILY_DIRECTION, {
            user,
            direction_at,
            data,
        });*/

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
    [SET_EVENTS](state, events) {
        state.project.events = [...events];
    },

    /**
     * Set current event
     *
     * @param {*} state
     * @param {Object} event
     */
    [SET_EVENT](state, event) {
        state.project.event = event;
    },

    /**
     * @param {*} state
     */
    [SET_EVENTS_FIELDS](state, fields) {
        state.project.eventsFields = fields;
    },

    /**
     * Add user event
     *
     * @param {*} state
     * @param {Number} event to append to user events list
     */
    [ADD_EVENT_USER](state, user) {},

    /**
     * Remove user event
     *
     * @param {*} context
     * @param {Number} params user event id
     */
    [REMOVE_EVENT_USER](state, { event, user }) {},

    /**
     * Set new event
     *
     * @param {*} state
     * @param {Object} event
     */
    [SET_NEW_EVENT](state, event) {
        state.project.newEvent = event;
    },

    /**
     * Set dragged event
     *
     * @param {*} state
     * @param {Object} event
     */
    [SET_DRAGGED_EVENT](state, event) {
        state.project.draggedEvent = event;
    },

    /**
     * Set agenda date
     *
     * @param {*} state
     * @param {Object} event
     */
    [SET_AGENDA_DATE](state, value) {
        state.project.agendaDate = value;
    },

    /**
     * Show/Hide agenda filters
     *
     * @param {*} state
     * @param {Object} event
     */
    [SET_AGENDA_FILTER](state, value) {
        state.project.agendaFilter = value;
    },

    /**
     * Toggle agenda view mode
     *
     * @param {*} state
     * @param {Object} event
     */
    [SET_AGENDA_LIST](state, value) {
        state.project.agendaList = value;
    },

    /**
     * Show agenda table
     *
     * @param {*} state
     * @param {Object} event
     */
    [SHOW_AGENDA_TABLE](state, value) {
        state.project.agendaTable = value;
    },

    /**
     * Toggle agenda show week-end
     *
     * @param {*} state
     * @param {Object} event
     */
    [SHOW_AGENDA_WEEK_END](state, value) {
        state.project.agendaWeekEnd = value;
    },

    /**
     * Toggle agenda show week-end
     *
     * @param {*} state
     * @param {Object} event
     */
    [SHOW_AGENDA_OTHER_PROJECTS](state, value) {
        state.project.agendaOtherProjects = value;
    },

    /**
     * Set agenda display mode
     *
     * @param {*} state
     * @param {Object} event
     */
    [SET_AGENDA_DISPLAY_MODE](state, value) {
        state.project.agendaDisplayMode = value;
    },

    /**
     * Set resized event
     *
     * @param {*} state
     * @param {Object} event
     */
    [SET_RESIZED_EVENT](state, event) {
        state.project.resizedEvent = event;
    },

    /**
     * Add event
     *
     * @param {*} state
     * @param {Number} event to append to events list
     */
    [ADD_EVENT](state, event) {
        state.project.events = [...state.project.events, event];
    },

    /**
     * Update event
     *
     * @param {*} state
     */
    [UPDATE_EVENT](state, params) {
        if (!state.project.events) {
            return;
        }

        state.project.events = state.project.events
            .filter((e) => e)
            .map((o) => (o.id == params.id ? { ...o, ...params } : o));
    },

    /**
     * remove event
     *
     * @param {*} context
     * @param {Number} params event id
     */
    [REMOVE_EVENT](state, slug) {
        state.project.events = state.project.events.filter((o) => o.id != slug);
    },

    /**
     *
     *
     * @param {*} context
     * @param {Number} params event id
     */
    [ADD_EVENT_USER_DAILY_DIRECTION](state, { user, direction_at, data }) {
        if (data.searching) {
            return;
        }

        if (!Array.isArray(state.project.eventUserDailyDirections)) {
            state.project.eventUserDailyDirections = [];
        }

        const search = (direction) => {
            if (
                direction.user_id != user.id ||
                direction.direction_at != direction_at ||
                direction.events.length != data.events.length
            ) {
                return false;
            }

            for (let i in direction.events) {
                if (direction.events[i] != data.events[i]) {
                    return false;
                }
            }

            return true;
        };

        if (state.project.eventUserDailyDirections.find(search)) {
            state.project.eventUserDailyDirections.map((direction) =>
                search(direction)
                    ? {
                          ...direction,
                          events: data.events,
                          direction: data.direction,
                      }
                    : direction
            );
        } else {
            state.project.eventUserDailyDirections = [
                ...state.project.eventUserDailyDirections,
                {
                    direction_at,
                    user_id: user.id,
                    events: data.events,
                    direction: data.direction,
                },
            ];
        }
    },

    /**
     * @param {*} state
     */
    [ADD_EVENT_PARAMS](state, { key, value, multiple }) {
        if (!state.project) {
            return;
        }
        state.project.prospectsPage = 1;

        if (!state.project.eventsParams) {
            state.project.eventsParams = {};
        }

        if (value == undefined) {
            value = "";
        }

        if (multiple) {
            if (state.project.eventsParams[key] === undefined) {
                state.project.eventsParams[key] = [];
            }

            let values = "" + value;
            if (state.project.eventsParams[key] !== "") {
                values = [...state.project.eventsParams[key], value];
            }

            state.project.eventsParams = Object.fromEntries(
                Object.entries(state.project.eventsParams).map(([k, v]) => [
                    k,
                    k == key ? values : v,
                ])
            );
        } else {
            state.project.eventsParams = {
                ...state.project.eventsParams,
                [key]: value,
            };
        }
    },

    /**
     * @param {*} state
     */
    [REMOVE_EVENT_PARAMS](state, { key, value }) {
        state.project.prospectsPage = 1;

        if (!state.project.eventsParams) {
            state.project.eventsParams = {};
        }

        if (value !== undefined) {
            if (state.project.eventsParams[key]) {
                state.project.eventsParams = Object.fromEntries(
                    Object.entries(state.project.eventsParams).map(([k, v]) => [
                        k,
                        k == key
                            ? state.project.eventsParams[k].filter(
                                  (val) => val != value
                              )
                            : v,
                    ])
                );

                if (state.project.eventsParams[key].length == 0) {
                    state.project.eventsParams = Object.fromEntries(
                        Object.entries(state.project.eventsParams).filter(
                            ([k]) => k != key
                        )
                    );
                }
            }
        } else {
            state.project.eventsParams = Object.fromEntries(
                Object.entries(state.project.eventsParams).filter(
                    ([k]) => k != key
                )
            );
        }
    },

    /**
     * @param {*} state
     */
    [SET_EVENT_PARAMS](state, params) {
        if (typeof params === "object") {
            state.project.eventsParams = params;
        }
    },

    /**
     * @param {*} state
     */
    [INIT_EVENT_PARAMS](state) {
        state.project.prospectsPage = 1;

        state.project.eventsParams = {}; /*Object.fromEntries(
            Object.entries(state.project.eventsParams).filter(([k]) => k == "count")
        );*/
    },

    /**
     * @param {*} state
     */
    [SET_EVENTS_COUNT](state, count) {
        state.project.eventsCount = count;
    },

    /**
     * @param {*} state
     */
    [SET_EVENTS_SORT_BY](state, column) {
        state.project.eventsSortBy = column;
    },

    /**
     * @param {*} state
     */
    [SET_EVENTS_SORT_ORDER](state, order) {
        state.project.eventsSortOrder = order;
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
    events: (state) =>
        state.project && Array.isArray(state.project.events)
            ? state.project.events
            : [],

    /**
     * Get current event
     *
     * @param {*} state
     * @returns
     */
    event: (state) =>
        state.project && state.project.event ? state.project.event : null,

    /**
     *
     * @param {*} state
     * @returns
     */
    getEventUserDailyDirection: (state) => (user, direction_at, events) => {
        return undefined;

        if (!Array.isArray(state.project.eventUserDailyDirections)) {
            return undefined;
        }

        const search = (direction) => {
            if (
                direction.user_id != user.id ||
                direction.direction_at != direction_at ||
                !Array.isArray(direction.events) ||
                direction.events.length != events.length
            ) {
                return false;
            }

            for (let i in direction.events) {
                if (direction.events[i] != events[i]) {
                    return false;
                }
            }

            return true;
        };

        return state.project.eventUserDailyDirections.find(search);
    },

    /**
     * Get new event
     *
     * @param {*} state
     * @returns
     */
    newEvent: (state) =>
        state.project && state.project.newEvent ? state.project.newEvent : null,

    /**
     * Get dragged event
     *
     * @param {*} state
     * @returns
     */
    draggedEvent: (state) =>
        state.project && state.project.draggedEvent
            ? state.project.draggedEvent
            : null,

    /**
     * Get resized event
     *
     * @param {*} state
     * @returns
     */
    resizedEvent: (state) =>
        state.project && state.project.resizedEvent
            ? state.project.resizedEvent
            : null,

    /**
     * @param {*} state
     * @returns
     */
    eventsParams: (state) =>
        state.project && state.project.eventsParams
            ? state.project.eventsParams
            : {},

    /**
     * @param {*} state
     * @returns
     */
    eventsParamsUrl: (state) =>
        state.project &&
        state.project.eventsParams &&
        Object.keys(state.project.eventsParams).length > 0
            ? encodeURI(JSON.stringify(state.project.eventsParams))
            : "",

    /**
     * @param {*} state
     * @returns
     */
    eventsParamsValue: (state) => (param) =>
        state.project && state.project.eventsParams
            ? state.project.eventsParams[param]
            : undefined,

    /**
     * @param {*} state
     * @returns
     */
    eventsParamExists: (state) => (param, value) => {
        if (!state.project || !state.project.eventsParams) {
            return false;
        }

        if (param === undefined) {
            return Object.keys(state.project.eventsParams).length > 0;
        }

        if (value === undefined) {
            return state.project.eventsParams[param] !== undefined;
        }

        return (
            Array.isArray(state.project.eventsParams[param]) &&
            state.project.eventsParams[param].findIndex((p) => p == value) >= 0
        );
    },

    /**
     * @param {*} state
     * @returns
     */
    eventsParamsCount: (state) => (param) => {
        if (
            !state.project ||
            !state.project.eventsParams ||
            state.project.eventsParams[param] === undefined
        ) {
            return 0;
        }

        if (!Array.isArray(state.project.eventsParams[param])) {
            return 1;
        }

        return state.project.eventsParams[param].length;
    },

    /**
     * Get events count pagination
     *
     * @param {*} state
     * @returns
     */
    eventsCount: (state) =>
        state.project && state.project.eventsCount
            ? state.project.eventsCount
            : 50,

    /**
     * Get events sort by
     *
     * @param {*} state
     * @returns
     */
    eventsSortBy: (state) =>
        state.project ? state.project.eventsSortBy : null,

    /**
     * Get events sort order
     *
     * @param {*} state
     * @returns
     */
    eventsSortOrder: (state) =>
        state.project ? state.project.eventsSortOrder : "desc",

    /**
     * @param {*} state
     * @returns
     */
    agendaDate: (state) =>
        state.project && state.project.agendaDate
            ? state.project.agendaDate
            : dateToString(new Date()).substring(0, 10),

    /**
     * @param {*} state
     * @returns
     */
    agendaFilter: (state) =>
        (state.project && state.project.agendaFilter) ||
        state.project.agendaFilter === undefined
            ? true
            : false,

    /**
     * @param {*} state
     * @returns
     */
    agendaList: (state) =>
        state.project && state.project.agendaList
            ? state.project.agendaList
            : false,

    /**
     * @param {*} state
     * @returns
     */
    agendaTable: (state) =>
        state.project && state.project.agendaTable !== undefined
            ? state.project.agendaTable
            : false,

    /**
     * @param {*} state
     * @returns
     */
    agendaWeekEnd: (state) =>
        state.project && state.project.agendaWeekEnd !== undefined
            ? state.project.agendaWeekEnd
            : false,

    /**
     * @param {*} state
     * @returns
     */
    agendaOtherProjects: (state) =>
        state.project && state.project.agendaOtherProjects !== undefined
            ? state.project.agendaOtherProjects
            : true,

    /**
     * @param {*} state
     * @returns
     */
    agendaDisplayMode: (state) =>
        state.project && state.project.agendaDisplayMode !== undefined
            ? state.project.agendaDisplayMode
            : "week",
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
