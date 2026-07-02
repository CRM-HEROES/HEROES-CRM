import threadService from "@/apis/project/thread";

import {
    FETCH_THREADS,
    SET_THREADS,
    SET_THREAD,
    ADD_THREAD,
    SHOW_THREAD,
    UPDATE_THREAD,
    REMOVE_THREAD,
} from "@/actions/project/thread";

/**
 * thread Store state
 */
export const state = {
    threads: [],
    thread: null,
};

/**
 * thread Store Actions
 */
const actions = {
    /**
     * Fetch threads
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_THREADS](context, params) {
        const { data } = await threadService.get(
            context.state.project.slug,
            params
        );
        context.commit(SET_THREADS, data);
        return data;
    },

    /**
     * Show thread
     *
     * @param {*} context
     * @param {Number} slug thread id
     * @returns thread
     */
    async [SHOW_THREAD](context, slug) {
        const { data } = await threadService.show(
            context.state.project.slug,
            slug
        );
        context.commit(SET_THREAD, data);
        return data;
    },

    /**
     * Add thread
     *
     * @param {*} context
     * @param {Object} params thread thread values
     * @returns thread
     */
    async [ADD_THREAD](context, params) {
        const { data } = await threadService.create(
            context.state.project.slug,
            params
        );
        context.commit(ADD_THREAD, data);
        return data;
    },

    /**
     * update thread
     *
     * @param {*} context
     * @param {Object} params new thread thread values
     */
    async [UPDATE_THREAD](context, params) {
        await threadService.update(
            context.state.project.slug,
            params.id,
            params
        );
        context.commit(UPDATE_THREAD, params);
    },

    /**
     * remove thread
     *
     * @param {*} context
     * @param {Number} params thread id
     * @returns thread
     */
    async [REMOVE_THREAD](context, slug) {
        await threadService.destroy(context.state.project.slug, slug);
        context.commit(REMOVE_THREAD, slug);
    },
};

/**
 * thread Store Mutations
 */
const mutations = {
    /**
     * Set threads
     *
     * @param {*} state
     * @returns
     */
    [SET_THREADS](state, threads) {
        state.project.threads = [...threads];
    },

    /**
     * Set current thread
     *
     * @param {*} state
     * @param {Object} thread
     */
    [SET_THREAD](state, thread) {
        state.project.thread = thread;
    },

    /**
     * Add thread
     *
     * @param {*} state
     * @param {Number} thread to append to threads list
     */
    [ADD_THREAD](state, thread) {
        state.project.threads = [...state.project.threads, thread];
    },

    /**
     * Update thread
     *
     * @param {*} state
     */
    [UPDATE_THREAD](state, params) {
        state.project.threads = state.project.threads.map((o) =>
            o.id == params.id ? { ...o, ...params } : o
        );
    },

    /**
     * remove thread
     *
     * @param {*} context
     * @param {Number} params thread id
     */
    [REMOVE_THREAD](state, slug) {
        state.project.threads = state.project.threads.filter(
            (o) => o.id != slug
        );
    },
};

/**
 * thread Store Getters
 */
const getters = {
    /**
     * Get list of threads
     *
     * @param {*} state
     * @returns
     */
    threads: (state) =>
        state.project && state.project.threads ? state.project.threads : [],

    /**
     * Get current thread
     *
     * @param {*} state
     * @returns
     */
    thread: (state) =>
        state.project && state.project.thread ? state.project.thread : null,
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
