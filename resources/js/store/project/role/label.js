import RoleLabelService from "@/apis/project/role/label";

import {
    FETCH_ROLE_LABELS,
    SET_ROLE_LABELS,
    ADD_ROLE_LABEL,
    REMOVE_ROLE_LABEL,
    BULK_ADD_ROLE_LABEL,
    BULK_REMOVE_ROLE_LABEL,
} from "@/actions/project/role/label";

/**
 * RoleLabel Store state
 */
export const state = {
    labels: [],
};

/**
 * RoleLabel Store Actions
 */
export const actions = {
    /**
     * Fetch role labels
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_ROLE_LABELS](context, params) {
        const { data } = await RoleLabelService.get(
            context.state.project.slug,
            context.state.project.role.id,
            params
        );
        context.commit(SET_ROLE_LABELS, data);
        return data;
    },

    /**
     * Add role label
     *
     * @param {*} context
     * @param {Object} params
     * @returns roleLabel
     */
    async [ADD_ROLE_LABEL](context, label) {
        context.commit(ADD_ROLE_LABEL, label);
        await RoleLabelService.update(
            context.state.project.slug,
            context.state.project.role.id,
            label.id
        );
    },

    /**
     * Remove role label
     *
     * @param {*} context
     * @param {Number} params role label id
     * @returns roleLabel
     */
    async [REMOVE_ROLE_LABEL](context, label) {
        context.commit(REMOVE_ROLE_LABEL, label);
        await RoleLabelService.destroy(
            context.state.project.slug,
            context.state.project.role.id,
            label.id
        );
    },

    /**
     * Add role label
     *
     * @param {*} context
     * @param {Object} params
     * @returns roleLabel
     */
    async [BULK_ADD_ROLE_LABEL](context, { roles, labels }) {
        await RoleLabelService.bulkUpdate(
            context.state.project.slug,
            roles,
            labels
        );
    },

    /**
     * Remove role label
     *
     * @param {*} context
     * @param {Number} params role label id
     * @returns roleLabel
     */
    async [BULK_REMOVE_ROLE_LABEL](context, { roles, labels }) {
        await RoleLabelService.bulkDestroy(
            context.state.project.slug,
            roles,
            labels
        );
    },
};

/**
 * Role label Store Mutations
 */
export const mutations = {
    /**
     * Set role labels
     *
     * @param {*} state
     * @returns
     */
    [SET_ROLE_LABELS](state, labels) {
        state.project.role.labels = labels;
    },

    /**
     * Add role label
     *
     * @param {*} state
     * @param {Number} label to append to role labels list
     */
    [ADD_ROLE_LABEL](state, label) {
        state.project.role.labels = [
            ...(state.project.role.labels ? state.project.role.labels : []),
            label,
        ];
        state.project.roles = state.project.roles.map((o) =>
            o.id == state.project.role.id ? { ...o, ...state.project.role } : o
        );
    },

    /**
     * Remove role label
     *
     * @param {*} context
     * @param {Number} params role label id
     */
    [REMOVE_ROLE_LABEL](state, label) {
        state.project.role.labels = (
            state.project.role.labels ? state.project.role.labels : []
        ).filter((o) => o.id != label.id);
        state.project.roles = state.project.roles.map((o) =>
            o.id == state.project.role.id ? { ...o, ...state.project.role } : o
        );
    },
};

/**
 * Role label Store Getters
 */
export const getters = {
    /**
     * Get list of role labels
     *
     * @param {*} state
     * @returns
     */
    roleLabels: (state) =>
        state.project &&
        state.project.role &&
        state.project.role.labels &&
        Array.isArray(state.project.role.labels)
            ? state.project.role.labels
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
