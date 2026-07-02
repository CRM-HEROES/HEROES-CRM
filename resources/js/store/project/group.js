import groupService from "@/apis/project/group";

import {
    FETCH_GROUPS,
    SET_GROUPS,
    SET_GROUP,
    ADD_GROUP,
    SHOW_GROUP,
    UPDATE_GROUP,
    REMOVE_GROUP,
} from "@/actions/project/group";

/**
 * group Store Actions
 */
const actions = {
    /**
     * Fetch groups
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_GROUPS](context, params) {
        const { data } = await groupService.get(
            context.state.project.slug,
            params
        );
        context.commit(SET_GROUPS, data);
        return data;
    },

    /**
     * Show group
     *
     * @param {*} context
     * @param {Number} slug group id
     * @returns group
     */
    async [SHOW_GROUP](context, slug) {
        const { data } = await groupService.show(
            context.state.project.slug,
            slug
        );
        // context.commit(SET_GROUP, data);
        return data;
    },

    /**
     * Add group
     *
     * @param {*} context
     * @param {Object} params group group values
     * @returns group
     */
    async [ADD_GROUP](context, params) {
        const { data } = await groupService.create(
            context.state.project.slug,
            params
        );
        context.commit(ADD_GROUP, data);
        return data;
    },

    /**
     * update group
     *
     * @param {*} context
     * @param {Object} params new group group values
     */
    async [UPDATE_GROUP](context, params) {
        await groupService.update(
            context.state.project.slug,
            params.id,
            params
        );
        context.commit(UPDATE_GROUP, params);
    },

    /**
     * remove group
     *
     * @param {*} context
     * @param {Number} params group id
     * @returns group
     */
    async [REMOVE_GROUP](context, slug) {
        await groupService.destroy(context.state.project.slug, slug);
        context.commit(REMOVE_GROUP, slug);
    },
};

/**
 * group Store Mutations
 */
const mutations = {
    /**
     * Set groups
     *
     * @param {*} state
     * @returns
     */
    [SET_GROUPS](state, groups) {
        state.project.groups = [...groups];
    },

    /**
     * Set current group
     *
     * @param {*} state
     * @param {Object} group
     */
    [SET_GROUP](state, group) {
        state.project.group = group;
    },

    /**
     * Add group
     *
     * @param {*} state
     * @param {Number} group to append to groups list
     */
    [ADD_GROUP](state, group) {
        state.project.groups = [...(state.project.groups ?? []), group];
    },

    /**
     * Update group
     *
     * @param {*} state
     */
    [UPDATE_GROUP](state, params) {
        state.project.groups = state.project.groups.map((o) =>
            o.id == params.id ? { ...o, ...params } : o
        );
    },

    /**
     * remove group
     *
     * @param {*} context
     * @param {Number} params group id
     */
    [REMOVE_GROUP](state, slug) {
        state.project.groups = state.project.groups.filter((o) => o.id != slug);
    },
};

/**
 * group Store Getters
 */
const getters = {
    /**
     * Get list of groups
     *
     * @param {*} state
     * @returns
     */
    groups: (state) =>
        state.project && state.project.groups ? state.project.groups : [],

    /**
     * Get current group
     *
     * @param {*} state
     * @returns
     */
    group: (state) =>
        state.project && state.project.group ? state.project.group : null,
};

/**
 * Store
 */
export default {
    actions,
    mutations,
    getters,
};
