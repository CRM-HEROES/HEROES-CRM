import RoleThreadService from "@/apis/project/role/thread";

import {
    FETCH_ROLE_THREADS,
    SET_ROLE_THREADS,
    ADD_ROLE_THREAD,
    REMOVE_ROLE_THREAD,
    BULK_ADD_ROLE_THREAD,
    BULK_REMOVE_ROLE_THREAD,
} from "@/actions/project/role/thread";

/**
 * RoleThread Store state
 */
export const state = {
    threads: [],
};

/**
 * RoleThread Store Actions
 */
export const actions = {
    /**
     * Fetch role threads
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_ROLE_THREADS](context, params) {
        const { data } = await RoleThreadService.get(
            context.state.project.slug,
            context.state.project.role.id,
            params
        );
        context.commit(SET_ROLE_THREADS, data);
        return data;
    },

    /**
     * Add role thread
     *
     * @param {*} context
     * @param {Object} params
     * @returns roleThread
     */
    async [ADD_ROLE_THREAD](context, thread) {
        context.commit(ADD_ROLE_THREAD, thread);
        await RoleThreadService.update(
            context.state.project.slug,
            context.state.project.role.id,
            thread.id
        );
    },

    /**
     * Remove role thread
     *
     * @param {*} context
     * @param {Number} params role thread id
     * @returns roleThread
     */
    async [REMOVE_ROLE_THREAD](context, thread) {
        context.commit(REMOVE_ROLE_THREAD, thread);
        await RoleThreadService.destroy(
            context.state.project.slug,
            context.state.project.role.id,
            thread.id
        );
    },

    /**
     * Add role thread
     *
     * @param {*} context
     * @param {Object} params
     * @returns roleThread
     */
    async [BULK_ADD_ROLE_THREAD](context, { roles, threads }) {
        await RoleThreadService.bulkUpdate(
            context.state.project.slug,
            roles,
            threads
        );
    },

    /**
     * Remove role thread
     *
     * @param {*} context
     * @param {Number} params role thread id
     * @returns roleThread
     */
    async [BULK_REMOVE_ROLE_THREAD](context, { roles, threads }) {
        await RoleThreadService.bulkDestroy(
            context.state.project.slug,
            roles,
            threads
        );
    },
};

/**
 * Role thread Store Mutations
 */
export const mutations = {
    /**
     * Set role threads
     *
     * @param {*} state
     * @returns
     */
    [SET_ROLE_THREADS](state, threads) {
        state.project.role.threads = threads;
    },

    /**
     * Add role thread
     *
     * @param {*} state
     * @param {Number} thread to append to role threads list
     */
    [ADD_ROLE_THREAD](state, thread) {
        state.project.role.threads = [
            ...(state.project.role.threads ? state.project.role.threads : []),
            thread,
        ];
        state.project.roles = state.project.roles.map((o) =>
            o.id == state.project.role.id ? { ...o, ...state.project.role } : o
        );
    },

    /**
     * Remove role thread
     *
     * @param {*} context
     * @param {Number} params role thread id
     */
    [REMOVE_ROLE_THREAD](state, thread) {
        state.project.role.threads = (
            state.project.role.threads ? state.project.role.threads : []
        ).filter((o) => o.id != thread.id);
        state.project.roles = state.project.roles.map((o) =>
            o.id == state.project.role.id ? { ...o, ...state.project.role } : o
        );
    },
};

/**
 * Role thread Store Getters
 */
export const getters = {
    /**
     * Get list of role threads
     *
     * @param {*} state
     * @returns
     */
    roleThreads: (state) =>
        state.project &&
        state.project.role &&
        state.project.role.threads &&
        Array.isArray(state.project.role.threads)
            ? state.project.role.threads
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
