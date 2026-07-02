import ProspectMessageService from "@/apis/project/prospect/message";
import ProspectThreadService from "@/apis/project/prospect/thread";

import {
    FETCH_PROSPECT_MESSAGES,
    FETCH_PROSPECT_THREADS,
    SET_PROSPECT_MESSAGES,
    SET_PROSPECT_THREADS,
    SET_PROSPECT_WAITING_USER_MESSAGE,
    ADD_PROSPECT_MESSAGE,
    BULK_PROSPECT_MESSAGE,
    UPDATE_PROSPECT_MESSAGE,
    REMOVE_PROSPECT_MESSAGE,
    ADD_PROSPECT_MESSAGE_USER,
    REMOVE_PROSPECT_MESSAGE_USER,
    ARCHIVE_PROSPECT_MESSAGE_USER,
    SET_PROSPECT_MESSAGE_THREAD,
    SET_PROSPECT_MESSAGE_INVOICE,
} from "@/actions/project/prospect/message";

/**
 * ProspectMessage Store state
 */
export const state = {
    messages: [],
    messageThread: null,
    waitingUserMessage: null,
};

/**
 * ProspectMessage Store Actions
 */
export const actions = {
    /**
     * Fetch prospect messages
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PROSPECT_MESSAGES](context, thread) {
        const { data } = await ProspectMessageService.get(
            context.state.project.slug,
            context.state.project.prospect.id,
            thread
        );
        context.commit(SET_PROSPECT_MESSAGES, data);
        return data;
    },

    /**
     * Fetch prospect messages threads
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PROSPECT_THREADS](context) {
        const { data } = await ProspectThreadService.get(
            context.state.project.slug,
            context.state.project.prospect.id
        );
        context.commit(SET_PROSPECT_THREADS, data);
        return data;
    },

    /**
     * Add prospect message
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectMessage
     */
    async [ADD_PROSPECT_MESSAGE](context, { thread, params }) {
        const { data } = await ProspectMessageService.create(
            context.state.project.slug,
            context.state.project.prospect.id,
            thread,
            params
        );
        context.commit(ADD_PROSPECT_MESSAGE, data);
        return data;
    },

    /**
     * bulk prospect message
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectMessage
     */
    async [BULK_PROSPECT_MESSAGE](context, { thread, params }) {
        const { data } = await ProspectMessageService.bulk(
            context.state.project.slug,
            thread,
            params
        );
        return data;
    },

    /**
     * Update prospect message
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectMessage
     */
    async [UPDATE_PROSPECT_MESSAGE](context, params) {
        const { data } = await ProspectMessageService.update(
            context.state.project.slug,
            context.state.project.prospect.id,
            params.thread_id,
            params.id,
            params
        );
        context.commit(UPDATE_PROSPECT_MESSAGE, params);
        return data;
    },

    /**
     * Remove prospect message
     *
     * @param {*} context
     * @param {Number} params prospect message id
     * @returns prospectMessage
     */
    async [REMOVE_PROSPECT_MESSAGE](context, message) {
        await ProspectMessageService.destroy(
            context.state.project.slug,
            context.state.project.prospect.id,
            message.thread_id,
            message.id
        );
        context.commit(REMOVE_PROSPECT_MESSAGE, message);
    },

    /**
     * Add prospect message user
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectMessage
     */
    [ADD_PROSPECT_MESSAGE_USER](context, { message, user }) {
        context.commit(ADD_PROSPECT_MESSAGE_USER, { message, user });
        ProspectMessageService.addUser(
            context.state.project.slug,
            context.state.project.prospect.id,
            message.thread_id,
            message.id,
            user.id,
            {}
        );
    },

    /**
     * Remove prospect message user
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectMessage
     */
    [REMOVE_PROSPECT_MESSAGE_USER](context, { message, user }) {
        context.commit(REMOVE_PROSPECT_MESSAGE_USER, { message, user });
        ProspectMessageService.removeUser(
            context.state.project.slug,
            context.state.project.prospect.id,
            message.thread_id,
            message.id,
            user.id
        );
    },

    /**
     * Archive prospect message user
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectMessage
     */
    [ARCHIVE_PROSPECT_MESSAGE_USER](context, { message, user }) {
        context.commit(ARCHIVE_PROSPECT_MESSAGE_USER, { message, user });
        ProspectMessageService.addUser(
            context.state.project.slug,
            message.prospect_id,
            message.thread_id,
            message.id,
            user.id,
            {
                archived_at: dateToString(new Date()),
            }
        );
    },
};

/**
 * Prospect message Store Mutations
 */
export const mutations = {
    /**
     * Set prospect messages
     *
     * @param {*} state
     * @returns
     */
    [SET_PROSPECT_MESSAGES](state, messages) {
        state.project.prospect.messages = messages;
    },

    /**
     * Set prospect threads
     *
     * @param {*} state
     * @returns
     */
    [SET_PROSPECT_THREADS](state, threads) {
        state.project.prospect.threads = threads;
    },

    /**
     * Set waiting user message
     *
     * @param {*} context
     * @returns
     */
    async [SET_PROSPECT_WAITING_USER_MESSAGE](state, message) {
        state.project.prospect.waitingUserMessage = message;
    },

    /**
     * Add prospect message
     *
     * @param {*} state
     * @param {Number} message to append to prospect messages list
     */
    [ADD_PROSPECT_MESSAGE](state, message) {
        state.project.prospect.messages = [
            ...(state.project.prospect.messages
                ? state.project.prospect.messages
                : []),
            message,
        ];
        state.project.prospects = state.project.prospects.map((o) =>
            o.id == state.project.prospect.id ? state.project.prospect : o
        );
    },

    /**
     * Add prospect message
     *
     * @param {*} state
     * @param {Number} message to append to prospect messages list
     */
    [BULK_PROSPECT_MESSAGE](state, message) {},

    /**
     * Update prospect message
     *
     * @param {*} state
     * @param {Number} message to append to prospect messages list
     */
    [UPDATE_PROSPECT_MESSAGE](state, params) {
        state.project.prospect.messages = (
            state.project.prospect.messages
                ? state.project.prospect.messages
                : []
        ).map((o) => (o.id == params.id ? { ...o, ...params } : o));
        state.project.prospects = state.project.prospects.map((o) =>
            o.id == state.project.prospect.id ? state.project.prospect : o
        );
    },

    /**
     * Remove prospect message
     *
     * @param {*} context
     * @param {Number} params prospect message id
     */
    [REMOVE_PROSPECT_MESSAGE](state, message) {
        state.project.prospect.messages = (
            state.project.prospect.messages
                ? state.project.prospect.messages
                : []
        ).filter((o) => o.id != message.id);
        state.project.prospects = state.project.prospects.map((o) =>
            o.id == state.project.prospect.id ? state.project.prospect : o
        );
    },

    /**
     * Set prospect message thread
     *
     * @param {*} context
     * @param {Number} params prospect message thread id
     */
    async [SET_PROSPECT_MESSAGE_THREAD](state, thread) {
        state.project.messageThread = thread;
    },

    /**
     * Set prospect message invoice
     *
     * @param {*} context
     * @param {Number} params prospect message invoice
     */
    async [SET_PROSPECT_MESSAGE_INVOICE](state, invoice) {
        state.project.messageInvoice = invoice;
    },

    /**
     * Add prospect message user
     *
     * @param {*} context
     * @param {Number} params prospect message id
     */
    [ADD_PROSPECT_MESSAGE_USER](state, { message, user }) {
        state.project.prospect.messages = (
            state.project.prospect.messages
                ? state.project.prospect.messages
                : []
        ).map((m) =>
            m.id == message.id
                ? { ...m, users: [...(m.users ? m.users : []), user] }
                : m
        );
        state.project.prospects = state.project.prospects.map((o) =>
            o.id == state.project.prospect.id ? state.project.prospect : o
        );
    },

    /**
     * Remove prospect message user
     *
     * @param {*} context
     * @param {Number} params prospect message id
     */
    [REMOVE_PROSPECT_MESSAGE_USER](state, { message, user }) {
        state.project.prospect.messages = (
            state.project.prospect.messages
                ? state.project.prospect.messages
                : []
        ).map((m) =>
            m.id == message.id
                ? {
                      ...m,
                      users: (Array.isArray(m.users) ? m.users : []).filter(
                          (u) => u.id != user.id
                      ),
                  }
                : m
        );
        state.project.prospects = state.project.prospects.map((o) =>
            o.id == state.project.prospect.id ? state.project.prospect : o
        );
    },

    /**
     * Archive prospect message user
     *
     * @param {*} context
     * @param {Number} params prospect message id
     */
    [ARCHIVE_PROSPECT_MESSAGE_USER](state, { message, user }) {
        state.project.prospect.messages = (
            state.project.prospect.messages
                ? state.project.prospect.messages
                : []
        ).map((m) =>
            m.id == message.id
                ? {
                      ...m,
                      users: m.users.map((u) =>
                          u.id == user.id
                              ? { ...user, pivot: { archived_at: new Date() } }
                              : u
                      ),
                  }
                : m
        );
        state.project.prospects = state.project.prospects.map((o) =>
            o.id == state.project.prospect.id ? state.project.prospect : o
        );
    },
};

/**
 * Prospect message Store Getters
 */
export const getters = {
    /**
     * Get list of prospect messages
     *
     * @param {*} state
     * @returns
     */
    prospectMessages: (state) =>
        state.project &&
        state.project.prospect &&
        state.project.prospect.messages
            ? state.project.prospect.messages
            : [],

    /**
     * Get list of prospect threads
     *
     * @param {*} state
     * @returns
     */
    prospectThreads: (state) =>
        state.project &&
        state.project.prospect &&
        state.project.prospect.threads
            ? state.project.prospect.threads
            : [],

    /**
     *
     * @param {*} state
     * @returns
     */
    messageThread: (state) =>
        state.project && state.project.messageThread
            ? state.project.messageThread
            : null,

    /**
     *
     * @param {*} state
     * @returns
     */
    messageInvoice: (state) =>
        state.project && state.project.messageInvoice
            ? state.project.messageInvoice
            : null,

    /**
     *
     * @param {*} state
     * @returns
     */
    waitingUserMessage: (state) =>
        state.project &&
        state.project.prospect &&
        state.project.prospect.waitingUserMessage
            ? state.project.prospect.waitingUserMessage
            : null,
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
