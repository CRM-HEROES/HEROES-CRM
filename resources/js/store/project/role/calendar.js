import RoleCalendarService from "@/apis/project/role/calendar";

import {
    FETCH_ROLE_CALENDARS,
    SET_ROLE_CALENDARS,
    ADD_ROLE_CALENDAR,
    REMOVE_ROLE_CALENDAR,
    BULK_ADD_ROLE_CALENDAR,
    BULK_REMOVE_ROLE_CALENDAR,
} from "@/actions/project/role/calendar";

/**
 * RoleCalendar Store state
 */
export const state = {
    calendars: [],
};

/**
 * RoleCalendar Store Actions
 */
export const actions = {
    /**
     * Fetch role calendars
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_ROLE_CALENDARS](context, params) {
        const { data } = await RoleCalendarService.get(
            context.state.project.slug,
            context.state.project.role.id,
            params
        );
        context.commit(SET_ROLE_CALENDARS, data);
        return data;
    },

    /**
     * Add role calendar
     *
     * @param {*} context
     * @param {Object} params
     * @returns roleCalendar
     */
    async [ADD_ROLE_CALENDAR](context, calendar) {
        context.commit(ADD_ROLE_CALENDAR, calendar);
        await RoleCalendarService.update(
            context.state.project.slug,
            context.state.project.role.id,
            calendar.id
        );
    },

    /**
     * Remove role calendar
     *
     * @param {*} context
     * @param {Number} params role calendar id
     * @returns roleCalendar
     */
    async [REMOVE_ROLE_CALENDAR](context, calendar) {
        context.commit(REMOVE_ROLE_CALENDAR, calendar);
        await RoleCalendarService.destroy(
            context.state.project.slug,
            context.state.project.role.id,
            calendar.id
        );
    },

    /**
     * Add role calendar
     *
     * @param {*} context
     * @param {Object} params
     * @returns roleCalendar
     */
    async [BULK_ADD_ROLE_CALENDAR](context, { roles, calendars }) {
        await RoleCalendarService.bulkUpdate(
            context.state.project.slug,
            roles,
            calendars
        );
    },

    /**
     * Remove role calendar
     *
     * @param {*} context
     * @param {Number} params role calendar id
     * @returns roleCalendar
     */
    async [BULK_REMOVE_ROLE_CALENDAR](context, { roles, calendars }) {
        await RoleCalendarService.bulkDestroy(
            context.state.project.slug,
            roles,
            calendars
        );
    },
};

/**
 * Role calendar Store Mutations
 */
export const mutations = {
    /**
     * Set role calendars
     *
     * @param {*} state
     * @returns
     */
    [SET_ROLE_CALENDARS](state, calendars) {
        state.project.role.calendars = calendars;
    },

    /**
     * Add role calendar
     *
     * @param {*} state
     * @param {Number} calendar to append to role calendars list
     */
    [ADD_ROLE_CALENDAR](state, calendar) {
        state.project.role.calendars = [
            ...(state.project.role.calendars
                ? state.project.role.calendars
                : []),
            calendar,
        ];
        state.project.roles = state.project.roles.map((o) =>
            o.id == state.project.role.id ? { ...o, ...state.project.role } : o
        );
    },

    /**
     * Remove role calendar
     *
     * @param {*} context
     * @param {Number} params role calendar id
     */
    [REMOVE_ROLE_CALENDAR](state, calendar) {
        state.project.role.calendars = (
            state.project.role.calendars ? state.project.role.calendars : []
        ).filter((o) => o.id != calendar.id);
        state.project.roles = state.project.roles.map((o) =>
            o.id == state.project.role.id ? { ...o, ...state.project.role } : o
        );
    },
};

/**
 * Role calendar Store Getters
 */
export const getters = {
    /**
     * Get list of role calendars
     *
     * @param {*} state
     * @returns
     */
    roleCalendars: (state) =>
        state.project &&
        state.project.role &&
        state.project.role.calendars &&
        Array.isArray(state.project.role.calendars)
            ? state.project.role.calendars
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
