import UserCalendarService from "@/apis/project/user/calendar";

import {
    FETCH_USER_CALENDARS,
    SET_USER_CALENDARS,
    ADD_USER_CALENDAR,
    REMOVE_USER_CALENDAR,
    BULK_ADD_USER_CALENDAR,
    BULK_REMOVE_USER_CALENDAR,
} from "@/actions/project/user/calendar";

/**
 * UserCalendar Store state
 */
export const state = {
    calendars: [],
};

/**
 * UserCalendar Store Actions
 */
export const actions = {
    /**
     * Fetch user calendars
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_USER_CALENDARS](context, params) {
        const { data } = await UserCalendarService.get(
            context.state.project.slug,
            context.state.project.user.id,
            params
        );
        context.commit(SET_USER_CALENDARS, data);
        return data;
    },

    /**
     * Add user calendar
     *
     * @param {*} context
     * @param {Object} params
     * @returns userCalendar
     */
    async [ADD_USER_CALENDAR](context, calendar) {
        context.commit(ADD_USER_CALENDAR, calendar);
        await UserCalendarService.update(
            context.state.project.slug,
            context.state.project.user.id,
            calendar.id
        );
    },

    /**
     * Remove user calendar
     *
     * @param {*} context
     * @param {Number} params user calendar id
     * @returns userCalendar
     */
    async [REMOVE_USER_CALENDAR](context, calendar) {
        context.commit(REMOVE_USER_CALENDAR, calendar);
        await UserCalendarService.destroy(
            context.state.project.slug,
            context.state.project.user.id,
            calendar.id
        );
    },

    /**
     * Add user calendar
     *
     * @param {*} context
     * @param {Object} params
     * @returns userCalendar
     */
    async [BULK_ADD_USER_CALENDAR](context, { users, calendars }) {
        await UserCalendarService.bulkUpdate(
            context.state.project.slug,
            users,
            calendars
        );
    },

    /**
     * Remove user calendar
     *
     * @param {*} context
     * @param {Number} params user calendar id
     * @returns userCalendar
     */
    async [BULK_REMOVE_USER_CALENDAR](context, { users, calendars }) {
        await UserCalendarService.bulkDestroy(
            context.state.project.slug,
            users,
            calendars
        );
    },
};

/**
 * User calendar Store Mutations
 */
export const mutations = {
    /**
     * Set user calendars
     *
     * @param {*} state
     * @returns
     */
    [SET_USER_CALENDARS](state, calendars) {
        state.project.user.calendars = calendars;
    },

    /**
     * Add user calendar
     *
     * @param {*} state
     * @param {Number} calendar to append to user calendars list
     */
    [ADD_USER_CALENDAR](state, calendar) {
        state.project.user.calendars = [
            ...(state.project.user.calendars
                ? state.project.user.calendars
                : []),
            calendar,
        ];
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },

    /**
     * Remove user calendar
     *
     * @param {*} context
     * @param {Number} params user calendar id
     */
    [REMOVE_USER_CALENDAR](state, calendar) {
        state.project.user.calendars = (
            state.project.user.calendars ? state.project.user.calendars : []
        ).filter((o) => o.id != calendar.id);
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },
};

/**
 * User calendar Store Getters
 */
export const getters = {
    /**
     * Get list of user calendars
     *
     * @param {*} state
     * @returns
     */
    userCalendars: (state) =>
        state.project &&
        state.project.user &&
        state.project.user.calendars &&
        Array.isArray(state.project.user.calendars)
            ? state.project.user.calendars
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
