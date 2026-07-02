import ProspectGroupService from "@/apis/project/prospect/group";

import {
    FETCH_PROSPECT_GROUPS,
    SET_PROSPECT_GROUPS,
    ADD_PROSPECT_GROUP,
    REMOVE_PROSPECT_GROUP,
    BULK_ADD_PROSPECT_GROUP,
    BULK_REMOVE_PROSPECT_GROUP,
} from "@/actions/project/prospect/group";

/**
 * ProspectGroup Store state
 */
export const state = {
    groups: [],
};

/**
 * ProspectGroup Store Actions
 */
export const actions = {
    /**
     * Fetch prospect groups
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PROSPECT_GROUPS](context, params) {
        const { data } = await ProspectGroupService.get(
            context.state.project.slug,
            context.state.project.prospect.id,
            params
        );
        context.commit(SET_PROSPECT_GROUPS, data);
        return data;
    },

    /**
     * Add prospect group
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectGroup
     */
    async [ADD_PROSPECT_GROUP](context, group) {
        context.commit(ADD_PROSPECT_GROUP, group);
        await ProspectGroupService.update(
            context.state.project.slug,
            context.state.project.prospect.id,
            group.id
        );
    },

    /**
     * Remove prospect group
     *
     * @param {*} context
     * @param {Number} params prospect group id
     * @returns prospectGroup
     */
    async [REMOVE_PROSPECT_GROUP](context, group) {
        context.commit(REMOVE_PROSPECT_GROUP, group);
        await ProspectGroupService.destroy(
            context.state.project.slug,
            context.state.project.prospect.id,
            group.id
        );
    },

    /**
     * Add prospect group
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectGroup
     */
    async [BULK_ADD_PROSPECT_GROUP](context, { prospects, groups }) {
        await ProspectGroupService.bulkUpdate(
            context.state.project.slug,
            prospects,
            groups
        );
    },

    /**
     * Remove prospect group
     *
     * @param {*} context
     * @param {Number} params prospect group id
     * @returns prospectGroup
     */
    async [BULK_REMOVE_PROSPECT_GROUP](context, { prospects, groups }) {
        await ProspectGroupService.bulkDestroy(
            context.state.project.slug,
            prospects,
            groups
        );
    },
};

/**
 * Prospect group Store Mutations
 */
export const mutations = {
    /**
     * Set prospect groups
     *
     * @param {*} state
     * @returns
     */
    [SET_PROSPECT_GROUPS](state, groups) {
        state.project.prospect.groups = groups;
    },

    /**
     * Add prospect group
     *
     * @param {*} state
     * @param {Number} group to append to prospect groups list
     */
    [ADD_PROSPECT_GROUP](state, group) {
        const groups = state.project.prospect.groups
            ? state.project.prospect.groups
            : [];

        if (!groups.find((g) => g.id == group.id)) {
            state.project.prospect.groups = [...groups, group];
            state.project.prospects = state.project.prospects.map((o) =>
                o.id == state.project.prospect.id
                    ? { ...o, ...state.project.prospect }
                    : o
            );
        }
    },

    /**
     * Remove prospect group
     *
     * @param {*} context
     * @param {Number} params prospect group id
     */
    [REMOVE_PROSPECT_GROUP](state, group) {
        state.project.prospect.groups = (
            state.project.prospect.groups ? state.project.prospect.groups : []
        ).filter((o) => o.id != group.id);
        state.project.prospects = state.project.prospects.map((o) =>
            o.id == state.project.prospect.id
                ? { ...o, ...state.project.prospect }
                : o
        );
    },
};

/**
 * Prospect group Store Getters
 */
export const getters = {
    /**
     * Get list of prospect groups
     *
     * @param {*} state
     * @returns
     */
    prospectGroups: (state) =>
        state.project && state.project.prospect && state.project.prospect.groups
            ? state.project.prospect.groups
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
