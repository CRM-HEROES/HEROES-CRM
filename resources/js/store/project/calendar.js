import CalendarService from "@/apis/project/calendar";

import {
    FETCH_CALENDARS,
    SET_CALENDARS,
    SET_CALENDAR,
    ADD_CALENDAR,
    SHOW_CALENDAR,
    UPDATE_CALENDAR,
    REMOVE_CALENDAR,
} from "@/actions/project/calendar";

/**
 * Calendar Store state
 */
export const state = {
    calendars: [],
    calendar: null,
};

/**
 * Calendar Store Actions
 */
const actions = {
    /**
     * Fetch calendars
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_CALENDARS](context, params) {
        const { data } = await CalendarService.get(
            context.state.project.slug,
            params
        );
        context.commit(SET_CALENDARS, data);
        return data;
    },

    /**
     * Show calendar
     *
     * @param {*} context
     * @param {Number} slug calendar id
     * @returns calendar
     */
    async [SHOW_CALENDAR](context, slug) {
        const { data } = await CalendarService.show(
            context.state.project.slug,
            slug
        );
        context.commit(SET_CALENDAR, data);
        return data;
    },

    /**
     * Add calendar
     *
     * @param {*} context
     * @param {Object} params calendar calendar values
     * @returns calendar
     */
    async [ADD_CALENDAR](context, params) {
        const { data } = await CalendarService.create(
            context.state.project.slug,
            params
        );
        context.commit(ADD_CALENDAR, data);
        return data;
    },

    /**
     * update calendar
     *
     * @param {*} context
     * @param {Object} params new calendar calendar values
     */
    async [UPDATE_CALENDAR](context, params) {
        await CalendarService.update(
            context.state.project.slug,
            params.id,
            params
        );
        context.commit(UPDATE_CALENDAR, params);
    },

    /**
     * remove calendar
     *
     * @param {*} context
     * @param {Number} params calendar id
     * @returns calendar
     */
    async [REMOVE_CALENDAR](context, { calendar, params }) {
        await CalendarService.destroy(
            context.state.project.slug,
            calendar.id,
            params
        );
        context.commit(REMOVE_CALENDAR, calendar);
    },
};

/**
 * Calendar Store Mutations
 */
const mutations = {
    /**
     * Set calendars
     *
     * @param {*} state
     * @returns
     */
    [SET_CALENDARS](state, calendars) {
        state.project.calendars = [...calendars];
    },

    /**
     * Set current calendar
     *
     * @param {*} state
     * @param {Object} calendar
     */
    [SET_CALENDAR](state, calendar) {
        state.project.calendar = calendar;
    },

    /**
     * Add calendar
     *
     * @param {*} state
     * @param {Number} calendar to append to calendars list
     */
    [ADD_CALENDAR](state, calendar) {
        state.project.calendars = [
            ...(state.project.calendars ?? []),
            calendar,
        ];
    },

    /**
     * Update calendar
     *
     * @param {*} state
     */
    [UPDATE_CALENDAR](state, params) {
        if (state.project.calendars) {
            state.project.calendars = state.project.calendars.map((o) =>
                o.id == params.id ? { ...o, ...params } : o
            );
        }
    },

    /**
     * remove calendar
     *
     * @param {*} context
     * @param {Number} params calendar id
     */
    [REMOVE_CALENDAR](state, calendar) {
        if (state.project.calendars) {
            state.project.calendars = state.project.calendars.filter(
                (o) => o.id != calendar.id
            );
        }
    },
};

/**
 * Calendar Store Getters
 */
const getters = {
    /**
     * Get list of calendars
     *
     * @param {*} state
     * @returns
     */
    calendars: (state) => {
        return state.project && state.project.calendars
            ? state.project.calendars
            : [];
    },

    /**
     * Get current calendar
     *
     * @param {*} state
     * @returns
     */
    calendar: (state) =>
        state.project && state.project.calendar ? state.project.calendar : null,
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
