import CategoryService from "@/apis/project/category";

import {
    FETCH_CATEGORIES,
    SET_CATEGORIES,
    SET_CATEGORY,
    ADD_CATEGORY,
    SHOW_CATEGORY,
    UPDATE_CATEGORY,
    REMOVE_CATEGORY,
    CATEGORY_TO_FIELD,
    CATEGORY_COMBINE_WITH,
} from "@/actions/project/category";

import {
    state as labelStates,
    actions as labelActions,
    mutations as labelMutations,
    getters as labelGetters,
} from "@/store/project/label";

/**
 * Category Store state
 */
export const state = {
    categories: [],
    category: {
        label: {},
        labels: [],
    },
    ...labelStates,
};

/**
 * Category Store Actions
 */
const actions = {
    /**
     * Fetch categories
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_CATEGORIES](context, params) {
        const { data } = await CategoryService.get(
            context.state.project.slug,
            params
        );
        context.commit(SET_CATEGORIES, data);
        return data;
    },

    /**
     * Show category
     *
     * @param {*} context
     * @param {Number} slug category id
     * @returns category
     */
    async [SHOW_CATEGORY](context, slug) {
        const { data } = await CategoryService.show(
            context.state.project.slug,
            slug
        );
        // context.commit(SET_CATEGORY, data);
        return data;
    },

    /**
     * Add category
     *
     * @param {*} context
     * @param {Object} params category field values
     * @returns category
     */
    async [ADD_CATEGORY](context, params) {
        const { data } = await CategoryService.create(
            context.state.project.slug,
            params
        );
        context.commit(ADD_CATEGORY, data);
        return data;
    },

    /**
     * update category
     *
     * @param {*} context
     * @param {Object} params new category field values
     */
    async [UPDATE_CATEGORY](context, params) {
        await CategoryService.update(
            context.state.project.slug,
            params.id,
            params
        );
        context.commit(UPDATE_CATEGORY, params);
    },

    /**
     * remove category
     *
     * @param {*} context
     * @param {Number} params category id
     * @returns category
     */
    async [REMOVE_CATEGORY](context, slug) {
        await CategoryService.destroy(context.state.project.slug, slug);
        context.commit(REMOVE_CATEGORY, slug);
    },

    /**
     * Turn category into field
     *
     * @param {*} context
     * @param {Number} params category id
     * @returns category
     */
    async [CATEGORY_TO_FIELD](context, slug) {
        const { data } = await CategoryService.field(
            context.state.project.slug,
            slug
        );
        context.commit(CATEGORY_TO_FIELD, { slug, data });
    },

    /**
     * Combine category with another category
     *
     * @param {*} context
     * @param {Number} params category id
     * @returns category
     */
    async [CATEGORY_COMBINE_WITH](context, { category1, category2 }) {
        const { data } = await CategoryService.combine(
            context.state.project.slug,
            category1,
            category2
        );
        context.commit(CATEGORY_COMBINE_WITH, { category1, category2, data });
    },

    ...labelActions,
};

/**
 * Category Store Mutations
 */
const mutations = {
    /**
     * Set categories
     *
     * @param {*} state
     * @returns
     */
    [SET_CATEGORIES](state, categories) {
        state.project.categories = [...categories];
    },

    /**
     * Set current category
     *
     * @param {*} state
     * @param {Object} category
     */
    [SET_CATEGORY](state, category) {
        state.project.category = category;
    },

    /**
     * Add category
     *
     * @param {*} state
     * @param {Number} category to append to categories list
     */
    [ADD_CATEGORY](state, category) {
        state.project.categories = [
            ...(state.project.categories ?? []),
            category,
        ];
    },

    /**
     * Update category
     *
     * @param {*} state
     */
    [UPDATE_CATEGORY](state, params) {
        if (state.project.categories) {
            state.project.categories = state.project.categories.map((o) =>
                o.id == params.id ? { ...o, ...params } : o
            );
        }
    },

    /**
     * remove category
     *
     * @param {*} context
     * @param {Number} params category id
     */
    [REMOVE_CATEGORY](state, slug) {
        if (state.project.categories) {
            state.project.categories = state.project.categories.filter(
                (o) => o.id != slug
            );
        }
    },

    /**
     * Turn category into field
     *
     * @param {*} context
     * @param {Number} params category id
     */
    [CATEGORY_TO_FIELD](state, { slug, data }) {
        if (state.project.categories) {
            state.project.fields = [...state.project.fields, data];
            state.project.categories = state.project.categories.filter(
                (o) => o.id != slug
            );
        }
    },

    /**
     * Turn category into field
     *
     * @param {*} context
     * @param {Number} params category id
     */
    [CATEGORY_COMBINE_WITH](state, { category1, category2, data }) {
        if (state.project.categories) {
            state.project.categories = state.project.categories.filter(
                (o) => o.id != category1
            );
        }
    },

    ...labelMutations,
};

/**
 * Category Store Getters
 */
const getters = {
    /**
     * Get list of categories
     *
     * @param {*} state
     * @returns
     */
    categories: (state) =>
        state.project && state.project.categories
            ? state.project.categories
            : [],

    /**
     * Get current category
     *
     * @param {*} state
     * @returns
     */
    category: (state) =>
        state.project && state.project.category ? state.project.category : null,

    ...labelGetters,
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
