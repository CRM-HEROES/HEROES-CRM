import labelService from "@/apis/project/label";

import {
    FETCH_LABELS,
    SET_LABELS,
    SET_LABEL,
    SHOW_LABEL,
    ADD_LABEL,
    UPDATE_LABEL,
    REMOVE_LABEL,
} from "@/actions/project/label";

/**
 * group Store state
 */
export const state = {
    label: null,
};

/**
 * label Store Actions
 */
export const actions = {
    /**
     * Fetch labels
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_LABELS](context, category) {
        const { data } = await labelService.get(
            context.state.project.slug,
            category
        );
        context.commit(SET_LABELS, { category, data });
        return data;
    },

    /**
     * Fetch labels
     *
     * @param {*} context
     * @returns
     */
    async [SHOW_LABEL](context, { category, slug }) {
        const { data } = await labelService.show(
            context.state.project.slug,
            category,
            slug
        );
        return data;
    },

    /**
     * Add label
     *
     * @param {*} context
     * @param {Object} params label label values
     * @returns label
     */
    async [ADD_LABEL](context, { category, params }) {
        const { data } = await labelService.create(
            context.state.project.slug,
            category,
            params
        );
        context.commit(ADD_LABEL, { category, data });
        return data;
    },

    /**
     * update label
     *
     * @param {*} context
     * @param {Object} params new label label values
     */
    async [UPDATE_LABEL](context, params) {
        await labelService.update(
            context.state.project.slug,
            params.category_id,
            params.id,
            params
        );
        context.commit(UPDATE_LABEL, params);
    },

    /**
     * remove label
     *
     * @param {*} context
     * @param {Number} params label id
     * @returns label
     */
    async [REMOVE_LABEL](context, { label, params }) {
        await labelService.destroy(
            context.state.project.slug,
            label.category_id,
            label.id,
            params
        );
        context.commit(REMOVE_LABEL, label);
    },
};

/**
 * label Store Mutations
 */
export const mutations = {
    /**
     * Set labels
     *
     * @param {*} state
     * @returns
     */
    [SET_LABELS](state, { category, data }) {
        const cat = state.project.categories.find((c) => c.id == category);

        if (cat) {
            cat.labels = data;
        }
    },

    /**
     * Set labels
     *
     * @param {*} state
     * @returns
     */
    [SET_LABEL](state, label) {
        state.label = label;
    },

    /**
     * Add label
     *
     * @param {*} state
     * @param {Number} label to append to labels list
     */
    [ADD_LABEL](state, { category, data }) {
        const cat = state.project.categories.find((c) => c.id == category);

        if (cat) {
            cat.labels = [...(cat.labels ? cat.labels : []), data];
        }
    },

    /**
     * Update label
     *
     * @param {*} state
     */
    [UPDATE_LABEL](state, params) {
        const cat = state.project.categories.find(
            (c) => c.id == params.category_id
        );

        if (cat) {
            cat.labels = cat.labels.map((label) =>
                label.id == params.id ? { ...label, ...params } : label
            );
        }
    },

    /**
     * remove label
     *
     * @param {*} context
     * @param {Number} params label id
     */
    [REMOVE_LABEL](state, params) {
        const cat = state.project.categories.find(
            (c) => c.id == params.category_id
        );

        if (cat) {
            cat.labels = cat.labels.filter((label) => label.id != params.id);
        }
    },
};

/**
 * label Store Getters
 */
export const getters = {
    /**
     * Get current label
     *
     * @param {*} state
     * @returns
     */
    label: (state) => state.label,
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
