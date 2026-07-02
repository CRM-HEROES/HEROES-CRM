import RoleCategoryService from "@/apis/project/role/category";

import {
    FETCH_ROLE_CATEGORIES,
    SET_ROLE_CATEGORIES,
    ADD_ROLE_CATEGORY,
    REMOVE_ROLE_CATEGORY,
    BULK_ADD_ROLE_CATEGORY,
    BULK_REMOVE_ROLE_CATEGORY,
} from "@/actions/project/role/category";

/**
 * RoleCategory Store state
 */
export const state = {
    categories: [],
};

/**
 * RoleCategory Store Actions
 */
export const actions = {
    /**
     * Fetch role categories
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_ROLE_CATEGORIES](context, params) {
        const { data } = await RoleCategoryService.get(
            context.state.project.slug,
            context.state.project.role.id,
            params
        );
        context.commit(SET_ROLE_CATEGORIES, data);
        return data;
    },

    /**
     * Add role category
     *
     * @param {*} context
     * @param {Object} params
     * @returns roleCategory
     */
    async [ADD_ROLE_CATEGORY](context, category) {
        context.commit(ADD_ROLE_CATEGORY, category);
        await RoleCategoryService.update(
            context.state.project.slug,
            context.state.project.role.id,
            category.id
        );
    },

    /**
     * Remove role category
     *
     * @param {*} context
     * @param {Number} params role category id
     * @returns roleCategory
     */
    async [REMOVE_ROLE_CATEGORY](context, category) {
        context.commit(REMOVE_ROLE_CATEGORY, category);
        await RoleCategoryService.destroy(
            context.state.project.slug,
            context.state.project.role.id,
            category.id
        );
    },

    /**
     * Add role category
     *
     * @param {*} context
     * @param {Object} params
     * @returns roleCategory
     */
    async [BULK_ADD_ROLE_CATEGORY](context, { roles, categories }) {
        await RoleCategoryService.bulkUpdate(
            context.state.project.slug,
            roles,
            categories
        );
    },

    /**
     * Remove role category
     *
     * @param {*} context
     * @param {Number} params role category id
     * @returns roleCategory
     */
    async [BULK_REMOVE_ROLE_CATEGORY](context, { roles, categories }) {
        await RoleCategoryService.bulkDestroy(
            context.state.project.slug,
            roles,
            categories
        );
    },
};

/**
 * Role category Store Mutations
 */
export const mutations = {
    /**
     * Set role categories
     *
     * @param {*} state
     * @returns
     */
    [SET_ROLE_CATEGORIES](state, categories) {
        state.project.role.categories = categories;
    },

    /**
     * Add role category
     *
     * @param {*} state
     * @param {Number} category to append to role categories list
     */
    [ADD_ROLE_CATEGORY](state, category) {
        state.project.role.categories = [
            ...(state.project.role.categories
                ? state.project.role.categories
                : []),
            category,
        ];
        state.project.roles = state.project.roles.map((o) =>
            o.id == state.project.role.id ? { ...o, ...state.project.role } : o
        );
    },

    /**
     * Remove role category
     *
     * @param {*} context
     * @param {Number} params role category id
     */
    [REMOVE_ROLE_CATEGORY](state, category) {
        state.project.role.categories = (
            state.project.role.categories ? state.project.role.categories : []
        ).filter((o) => o.id != category.id);
        state.project.roles = state.project.roles.map((o) =>
            o.id == state.project.role.id ? { ...o, ...state.project.role } : o
        );
    },
};

/**
 * Role category Store Getters
 */
export const getters = {
    /**
     * Get list of role categories
     *
     * @param {*} state
     * @returns
     */
    roleCategories: (state) =>
        state.project &&
        state.project.role &&
        state.project.role.categories &&
        Array.isArray(state.project.role.categories)
            ? state.project.role.categories
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
