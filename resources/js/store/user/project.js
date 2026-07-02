import UserProjectService from "@/apis/user/project";

import {
    FETCH_USER_PROJECTS,
    SET_USER_PROJECTS,
    ADD_USER_PROJECT,
    REMOVE_USER_PROJECT,
} from "@/actions/user/project";

/**
 * UserProject Store state
 */
export const state = {};

/**
 * UserProject Store Actions
 */
export const actions = {
    /**
     * Fetch user projects
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_USER_PROJECTS](context, params) {
        const { data } = await UserProjectService.get(
            context.state.globalUser.id,
            params
        );
        context.commit(SET_USER_PROJECTS, data);
        return data;
    },

    /**
     * Add user project
     *
     * @param {*} context
     * @param {Object} params
     * @returns userProject
     */
    async [ADD_USER_PROJECT](context, project) {
        context.commit(ADD_USER_PROJECT, project);
        await UserProjectService.update(
            context.state.globalUser.email,
            project.slug
        );
    },

    /**
     * Remove user project
     *
     * @param {*} context
     * @param {Number} params user project id
     * @returns userProject
     */
    async [REMOVE_USER_PROJECT](context, project) {
        context.commit(REMOVE_USER_PROJECT, project);
        await UserProjectService.destroy(
            context.state.globalUser.id,
            project.slug
        );
    },
};

/**
 * User project Store Mutations
 */
export const mutations = {
    /**
     * Set user projects
     *
     * @param {*} state
     * @returns
     */
    [SET_USER_PROJECTS](state, projects) {
        state.globalUser.projects = projects;
    },

    /**
     * Add user project
     *
     * @param {*} state
     * @param {Number} project to append to user projects list
     */
    [ADD_USER_PROJECT](state, project) {
        state.globalUser.projects = [
            ...(state.globalUser.projects ? state.globalUser.projects : []),
            project,
        ];
        state.globalUsers = state.globalUsers.map((o) =>
            o.id == state.globalUser.id ? { ...o, ...state.globalUser } : o
        );
    },

    /**
     * Remove user project
     *
     * @param {*} context
     * @param {Number} params user project id
     */
    [REMOVE_USER_PROJECT](state, project) {
        state.globalUser.projects = (
            state.globalUser.projects ? state.globalUser.projects : []
        ).filter((o) => o.slug != project.slug);
        state.globalUsers = state.globalUsers.map((o) =>
            o.id == state.globalUser.id ? { ...o, ...state.globalUser } : o
        );
    },
};

/**
 * User project Store Getters
 */
export const getters = {
    /**
     * Get list of user projects
     *
     * @param {*} state
     * @returns
     */
    userProjects: (state) =>
        state.globalUser && Array.isArray(state.globalUser.projects)
            ? state.globalUser.projects
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
