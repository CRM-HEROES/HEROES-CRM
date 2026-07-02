import UserCategoryService from "@/apis/project/user/category";

import {
    FETCH_USER_CATEGORIES,
    SET_USER_CATEGORIES,
    ADD_USER_CATEGORY,
    REMOVE_USER_CATEGORY,
    BULK_ADD_USER_CATEGORY,
    BULK_REMOVE_USER_CATEGORY,
} from "@/actions/project/user/category";

/**
 * UserCategory Store state
 */
export const state = {
    categories: [],
};

/**
 * UserCategory Store Actions
 */
export const actions = {
    /**
     * Fetch user categories
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_USER_CATEGORIES](context, params) {
        const { data } = await UserCategoryService.get(
            context.state.project.slug,
            context.state.project.user.id,
            params
        );
        context.commit(SET_USER_CATEGORIES, data);
        return data;
    },

    /**
     * Add user category
     *
     * @param {*} context
     * @param {Object} params
     * @returns userCategory
     */
    async [ADD_USER_CATEGORY](context, category) {
        context.commit(ADD_USER_CATEGORY, category);
        await UserCategoryService.update(
            context.state.project.slug,
            context.state.project.user.id,
            category.id
        );
    },

    /**
     * Remove user category
     *
     * @param {*} context
     * @param {Number} params user category id
     * @returns userCategory
     */
    async [REMOVE_USER_CATEGORY](context, category) {
        context.commit(REMOVE_USER_CATEGORY, category);
        await UserCategoryService.destroy(
            context.state.project.slug,
            context.state.project.user.id,
            category.id
        );
    },

    /**
     * Add user category
     *
     * @param {*} context
     * @param {Object} params
     * @returns userCategory
     */
    async [BULK_ADD_USER_CATEGORY](context, { users, categories }) {
        await UserCategoryService.bulkUpdate(
            context.state.project.slug,
            users,
            categories
        );
    },

    /**
     * Remove user category
     *
     * @param {*} context
     * @param {Number} params user category id
     * @returns userCategory
     */
    async [BULK_REMOVE_USER_CATEGORY](context, { users, categories }) {
        await UserCategoryService.bulkDestroy(
            context.state.project.slug,
            users,
            categories
        );
    },
};

/**
 * User category Store Mutations
 */
export const mutations = {
    /**
     * Set user categories
     *
     * @param {*} state
     * @returns
     */
    [SET_USER_CATEGORIES](state, categories) {
        state.project.user.categories = categories;
    },

    /**
     * Add user category
     *
     * @param {*} state
     * @param {Number} category to append to user categories list
     */
    [ADD_USER_CATEGORY](state, category) {
        state.project.user.categories = [
            ...(state.project.user.categories
                ? state.project.user.categories
                : []),
            category,
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
     * Remove user category
     *
     * @param {*} context
     * @param {Number} params user category id
     */
    [REMOVE_USER_CATEGORY](state, category) {
        state.project.user.categories = (
            state.project.user.categories ? state.project.user.categories : []
        ).filter((o) => o.id != category.id);
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },
};

/**
 * User category Store Getters
 */
export const getters = {
    /**
     * Get list of user categories
     *
     * @param {*} state
     * @returns
     */
    userCategories: (state) =>
        state.project &&
        state.project.user &&
        state.project.user.categories &&
        Array.isArray(state.project.user.categories)
            ? state.project.user.categories
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
