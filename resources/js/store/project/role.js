import roleService from "@/apis/project/role";

import {
    FETCH_ROLES,
    SET_ROLES,
    SET_ROLE,
    ADD_ROLE,
    SHOW_ROLE,
    UPDATE_ROLE,
    REMOVE_ROLE,
} from "@/actions/project/role";

import calendarStore from "@/store/project/role/calendar";
import categoryStore from "@/store/project/role/category";
import documentStore from "@/store/project/role/document";
import folderStore from "@/store/project/role/folder";
import labelStore from "@/store/project/role/label";
import orderActionStore from "@/store/project/role/order-action";
import orderStepStore from "@/store/project/role/order-step";
import permissionStore from "@/store/project/role/permission";
import questionnaireStore from "@/store/project/role/questionnaire";
import threadStore from "@/store/project/role/thread";
import userStore from "@/store/project/role/user";

/**
 * Role Store state
 */
export const state = {
    role: {
        ...calendarStore.state,
        ...categoryStore.state,
        ...documentStore.state,
        ...folderStore.state,
        ...labelStore.state,
        ...orderActionStore.state,
        ...orderStepStore.state,
        ...permissionStore.state,
        ...questionnaireStore.state,
        ...threadStore.state,
        ...userStore.state,
    },
};

/**
 * role Store Actions
 */
const actions = {
    /**
     * Fetch roles
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_ROLES](context, params) {
        const { data } = await roleService.get(
            context.state.project.slug,
            params
        );
        context.commit(SET_ROLES, data);
        return data;
    },

    /**
     * Show role
     *
     * @param {*} context
     * @param {Number} slug role id
     * @returns role
     */
    async [SHOW_ROLE](context, slug) {
        const { data } = await roleService.show(
            context.state.project.slug,
            slug
        );
        // context.commit(SET_ROLE, data);
        return data;
    },

    /**
     * Add role
     *
     * @param {*} context
     * @param {Object} params role role values
     * @returns role
     */
    async [ADD_ROLE](context, params) {
        const { data } = await roleService.create(
            context.state.project.slug,
            params
        );
        context.commit(ADD_ROLE, data);
        return data;
    },

    /**
     * update role
     *
     * @param {*} context
     * @param {Object} params new role role values
     */
    async [UPDATE_ROLE](context, params) {
        await roleService.update(context.state.project.slug, params.id, params);
        context.commit(UPDATE_ROLE, params);
    },

    /**
     * remove role
     *
     * @param {*} context
     * @param {Number} params role id
     * @returns role
     */
    async [REMOVE_ROLE](context, slug) {
        await roleService.destroy(context.state.project.slug, slug);
        context.commit(REMOVE_ROLE, slug);
    },

    ...calendarStore.actions,
    ...categoryStore.actions,
    ...documentStore.actions,
    ...folderStore.actions,
    ...labelStore.actions,
    ...orderActionStore.actions,
    ...orderStepStore.actions,
    ...permissionStore.actions,
    ...questionnaireStore.actions,
    ...threadStore.actions,
    ...userStore.actions,
};

/**
 * role Store Mutations
 */
const mutations = {
    /**
     * Set roles
     *
     * @param {*} state
     * @returns
     */
    [SET_ROLES](state, roles) {
        state.project.roles = [...roles];
    },

    /**
     * Set current role
     *
     * @param {*} state
     * @param {Object} role
     */
    [SET_ROLE](state, role) {
        state.project.role = role;
    },

    /**
     * Add role
     *
     * @param {*} state
     * @param {Number} role to append to roles list
     */
    [ADD_ROLE](state, role) {
        state.project.roles = [...(state.project.roles ?? []), role];
    },

    /**
     * Update role
     *
     * @param {*} state
     */
    [UPDATE_ROLE](state, params) {
        state.project.roles = state.project.roles.map((o) =>
            o.id == params.id ? { ...o, ...params } : o
        );
    },

    /**
     * remove role
     *
     * @param {*} context
     * @param {Number} params role id
     */
    [REMOVE_ROLE](state, slug) {
        state.project.roles = state.project.roles.filter((o) => o.id != slug);
    },

    ...calendarStore.mutations,
    ...categoryStore.mutations,
    ...documentStore.mutations,
    ...folderStore.mutations,
    ...labelStore.mutations,
    ...orderActionStore.mutations,
    ...orderStepStore.mutations,
    ...permissionStore.mutations,
    ...questionnaireStore.mutations,
    ...threadStore.mutations,
    ...userStore.mutations,
};

/**
 * role Store Getters
 */
const getters = {
    /**
     * Get list of roles
     *
     * @param {*} state
     * @returns
     */
    roles: (state) =>
        state.project && state.project.roles ? state.project.roles : [],

    /**
     * Get current role
     *
     * @param {*} state
     * @returns
     */
    role: (state) =>
        state.project && state.project.role ? state.project.role : null,

    /**
     *
     * @param {*} state
     * @returns
     */
    roleFullName: (state) =>
        state.project && state.project.role
            ? [
                  state.project.role.name,
                  // state.project.role.last_name,
              ]
                  .filter((n) => n)
                  .join(" ")
            : "",

    ...calendarStore.getters,
    ...categoryStore.getters,
    ...documentStore.getters,
    ...folderStore.getters,
    ...labelStore.getters,
    ...orderActionStore.getters,
    ...orderStepStore.getters,
    ...permissionStore.getters,
    ...questionnaireStore.getters,
    ...threadStore.getters,
    ...userStore.getters,
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
