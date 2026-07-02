import UserLabelService from "@/apis/project/user/label";

import {
    FETCH_USER_LABELS,
    SET_USER_LABELS,
    ADD_USER_LABEL,
    REMOVE_USER_LABEL,
    BULK_ADD_USER_LABEL,
    BULK_REMOVE_USER_LABEL,
} from "@/actions/project/user/label";

/**
 * UserLabel Store state
 */
export const state = {
    labels: [],
};

/**
 * UserLabel Store Actions
 */
export const actions = {
    /**
     * Fetch user labels
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_USER_LABELS](context, params) {
        const { data } = await UserLabelService.get(
            context.state.project.slug,
            context.state.project.user.id,
            params
        );
        context.commit(SET_USER_LABELS, data);
        return data;
    },

    /**
     * Add user label
     *
     * @param {*} context
     * @param {Object} params
     * @returns userLabel
     */
    async [ADD_USER_LABEL](context, label) {
        context.commit(ADD_USER_LABEL, label);
        await UserLabelService.update(
            context.state.project.slug,
            context.state.project.user.id,
            label.id
        );
    },

    /**
     * Remove user label
     *
     * @param {*} context
     * @param {Number} params user label id
     * @returns userLabel
     */
    async [REMOVE_USER_LABEL](context, label) {
        context.commit(REMOVE_USER_LABEL, label);
        await UserLabelService.destroy(
            context.state.project.slug,
            context.state.project.user.id,
            label.id
        );
    },

    /**
     * Add user label
     *
     * @param {*} context
     * @param {Object} params
     * @returns userLabel
     */
    async [BULK_ADD_USER_LABEL](context, { users, labels }) {
        await UserLabelService.bulkUpdate(
            context.state.project.slug,
            users,
            labels
        );
    },

    /**
     * Remove user label
     *
     * @param {*} context
     * @param {Number} params user label id
     * @returns userLabel
     */
    async [BULK_REMOVE_USER_LABEL](context, { users, labels }) {
        await UserLabelService.bulkDestroy(
            context.state.project.slug,
            users,
            labels
        );
    },
};

/**
 * User label Store Mutations
 */
export const mutations = {
    /**
     * Set user labels
     *
     * @param {*} state
     * @returns
     */
    [SET_USER_LABELS](state, labels) {
        state.project.user.labels = labels;
    },

    /**
     * Add user label
     *
     * @param {*} state
     * @param {Number} label to append to user labels list
     */
    [ADD_USER_LABEL](state, label) {
        state.project.user.labels = [
            ...(state.project.user.labels ? state.project.user.labels : []),
            label,
        ];
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },

    /**
     * Remove user label
     *
     * @param {*} context
     * @param {Number} params user label id
     */
    [REMOVE_USER_LABEL](state, label) {
        state.project.user.labels = (
            state.project.user.labels ? state.project.user.labels : []
        ).filter((o) => o.id != label.id);
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },
};

/**
 * User label Store Getters
 */
export const getters = {
    /**
     * Get list of user labels
     *
     * @param {*} state
     * @returns
     */
    userLabels: (state) =>
        state.project &&
        state.project.user &&
        state.project.user.labels &&
        Array.isArray(state.project.user.labels)
            ? state.project.user.labels
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
