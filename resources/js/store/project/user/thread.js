import UserThreadService from "@/apis/project/user/thread";

import {
    FETCH_USER_THREADS,
    SET_USER_THREADS,
    ADD_USER_THREAD,
    REMOVE_USER_THREAD,
    BULK_ADD_USER_THREAD,
    BULK_REMOVE_USER_THREAD,
} from "@/actions/project/user/thread";

/**
 * UserThread Store state
 */
export const state = {
    threads: [],
};

/**
 * UserThread Store Actions
 */
export const actions = {
    /**
     * Fetch user threads
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_USER_THREADS](context, params) {
        const { data } = await UserThreadService.get(
            context.state.project.slug,
            context.state.project.user.id,
            params
        );
        context.commit(SET_USER_THREADS, data);
        return data;
    },

    /**
     * Add user thread
     *
     * @param {*} context
     * @param {Object} params
     * @returns userThread
     */
    async [ADD_USER_THREAD](context, thread) {
        context.commit(ADD_USER_THREAD, thread);
        await UserThreadService.update(
            context.state.project.slug,
            context.state.project.user.id,
            thread.id
        );
    },

    /**
     * Remove user thread
     *
     * @param {*} context
     * @param {Number} params user thread id
     * @returns userThread
     */
    async [REMOVE_USER_THREAD](context, thread) {
        context.commit(REMOVE_USER_THREAD, thread);
        await UserThreadService.destroy(
            context.state.project.slug,
            context.state.project.user.id,
            thread.id
        );
    },

    /**
     * Add user thread
     *
     * @param {*} context
     * @param {Object} params
     * @returns userThread
     */
    async [BULK_ADD_USER_THREAD](context, { users, threads }) {
        await UserThreadService.bulkUpdate(
            context.state.project.slug,
            users,
            threads
        );
    },

    /**
     * Remove user thread
     *
     * @param {*} context
     * @param {Number} params user thread id
     * @returns userThread
     */
    async [BULK_REMOVE_USER_THREAD](context, { users, threads }) {
        await UserThreadService.bulkDestroy(
            context.state.project.slug,
            users,
            threads
        );
    },
};

/**
 * User thread Store Mutations
 */
export const mutations = {
    /**
     * Set user threads
     *
     * @param {*} state
     * @returns
     */
    [SET_USER_THREADS](state, threads) {
        state.project.user.threads = threads;
    },

    /**
     * Add user thread
     *
     * @param {*} state
     * @param {Number} thread to append to user threads list
     */
    [ADD_USER_THREAD](state, thread) {
        state.project.user.threads = [
            ...(state.project.user.threads ? state.project.user.threads : []),
            thread,
        ];

        if (Array.isArray(state.project.users)) {
            state.project.users = state.project.users.map((o) =>
                o.id == state.project.user.id
                    ? { ...o, ...state.project.user }
                    : o
            );
        }
    },

    /**
     * Remove user thread
     *
     * @param {*} context
     * @param {Number} params user thread id
     */
    [REMOVE_USER_THREAD](state, thread) {
        state.project.user.threads = (
            state.project.user.threads ? state.project.user.threads : []
        ).filter((o) => o.id != thread.id);
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },
};

/**
 * User thread Store Getters
 */
export const getters = {
    /**
     * Get list of user threads
     *
     * @param {*} state
     * @returns
     */
    userThreads: (state) =>
        state.project &&
        state.project.user &&
        state.project.user.threads &&
        Array.isArray(state.project.user.threads)
            ? state.project.user.threads
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
