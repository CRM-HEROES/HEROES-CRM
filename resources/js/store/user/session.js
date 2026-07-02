import SessionService from "@/apis/user/session";

import {
    FETCH_USER_SESSIONS,
    SET_USER_SESSIONS,
    FETCH_USER_GEOIPS,
    SET_USER_GEOIPS,
    BAN_USER_DEVICE,
    CONFIRM_BAN_USER_DEVICE,
    CANCEL_BAN_USER_DEVICE,
} from "@/actions/user/session";

/**
 * Session Store state
 */
export const state = {
    userSessions: [],
};

/**
 * Session Store Actions
 */
const actions = {
    /**
     * Show session
     *
     * @param {*} context
     * @returns session
     */
    async [FETCH_USER_SESSIONS](context) {
        const { data } = await SessionService.get(
            context.rootState.auth.user.id
        );
        context.commit(SET_USER_SESSIONS, data);
        return data;
    },

    /**
     * Show session
     *
     * @param {*} context
     * @returns session
     */
    async [FETCH_USER_GEOIPS](context) {
        const { data } = await SessionService.geoips(
            context.rootState.auth.user.id
        );
        context.commit(SET_USER_GEOIPS, data);
        return data;
    },

    /**
     * Ban session
     *
     * @param {*} context
     * @returns session
     */
    async [BAN_USER_DEVICE](context, session) {
        const { data } = await SessionService.ban(
            context.rootState.auth.user.id,
            session.id
        );
        return data;
    },

    /**
     * Confirm ban session
     *
     * @param {*} context
     * @returns session
     */
    async [CONFIRM_BAN_USER_DEVICE](context, { session, params }) {
        const { data } = await SessionService.confirmBan(
            context.rootState.auth.user.id,
            session.id,
            params
        );
        return data;
    },

    /**
     * Cancel ban session
     *
     * @param {*} context
     * @returns session
     */
    async [CANCEL_BAN_USER_DEVICE](context, session) {
        const { data } = await SessionService.cancelBan(
            context.rootState.auth.user.id,
            session.id
        );
        return data;
    },
};

/**
 * Session Store Mutations
 */
const mutations = {
    /**
     * Update sessions list
     *
     * @param {*} state
     */
    [SET_USER_SESSIONS](state, data) {
        state.userSessions = data;
    },

    /**
     * Update geoips list
     *
     * @param {*} state
     */
    [SET_USER_GEOIPS](state, data) {
        state.userGeoips = data;
    },
};

/**
 * Session Store Getters
 */
const getters = {
    /**
     * Get list of sessions
     *
     * @param {*} state
     * @returns
     */
    userSessions(state) {
        return Array.isArray(state.userSessions) ? state.userSessions : [];
    },

    /**
     * Get list of geoips
     *
     * @param {*} state
     * @returns
     */
    userGeoips(state) {
        return Array.isArray(state.userGeoips) ? state.userGeoips : [];
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
