import FieldService from "@/apis/project/field";

import {
    FETCH_FIELDS,
    SET_FIELDS,
    SET_FIELD,
    ADD_FIELD,
    SHOW_FIELD,
    UPDATE_FIELD,
    REMOVE_FIELD,
    SET_NEW_FIELD_FOR,
    FIELD_TO_CATEGORY,
} from "@/actions/project/field";

/**
 * Field Store state
 */
export const state = {
    fields: [],
    field: null,
};

/**
 * Field Store Actions
 */
const actions = {
    /**
     * Fetch fields
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_FIELDS](context, params) {
        const { data } = await FieldService.get(
            context.state.project.slug,
            params
        );
        context.commit(SET_FIELDS, data);
        return data;
    },

    /**
     * Show field
     *
     * @param {*} context
     * @param {Number} slug field id
     * @returns field
     */
    async [SHOW_FIELD](context, slug) {
        const { data } = await FieldService.show(
            context.state.project.slug,
            slug
        );
        // context.commit(SET_FIELD, data);
        return data;
    },

    /**
     * Add field
     *
     * @param {*} context
     * @param {Object} params field field values
     * @returns field
     */
    async [ADD_FIELD](context, params) {
        const { data } = await FieldService.create(
            context.state.project.slug,
            params
        );
        context.commit(ADD_FIELD, data);
        return data;
    },

    /**
     * update field
     *
     * @param {*} context
     * @param {Object} params new field field values
     */
    async [UPDATE_FIELD](context, params) {
        await FieldService.update(
            context.state.project.slug,
            params.id,
            params
        );
        context.commit(UPDATE_FIELD, params);
    },

    /**
     * remove field
     *
     * @param {*} context
     * @param {Number} params field id
     * @returns field
     */
    async [REMOVE_FIELD](context, slug) {
        context.commit(REMOVE_FIELD, slug);
        await FieldService.destroy(context.state.project.slug, slug);
    },

    /**
     * Turn field into category
     *
     * @param {*} context
     * @param {Number} params field id
     * @returns field
     */
    async [FIELD_TO_CATEGORY](context, slug) {
        const { data } = await FieldService.category(
            context.state.project.slug,
            slug
        );
        context.commit(FIELD_TO_CATEGORY, { slug, data });
    },
};

/**
 * Field Store Mutations
 */
const mutations = {
    /**
     * Set fields
     *
     * @param {*} state
     * @returns
     */
    [SET_FIELDS](state, fields) {
        state.project.fields = [...fields];
    },

    /**
     * Set current field
     *
     * @param {*} state
     * @param {Object} field
     */
    [SET_FIELD](state, field) {
        state.project.field = field;
    },

    /**
     * Add field
     *
     * @param {*} state
     * @param {Number} field to append to fields list
     */
    [ADD_FIELD](state, field) {
        state.project.fields = [...(state.project.fields ?? []), field];
    },

    /**
     * Update field
     *
     * @param {*} state
     */
    [UPDATE_FIELD](state, params) {
        if (state.project.fields) {
            state.project.fields = state.project.fields.map((o) =>
                o.id == params.id ? { ...o, ...params } : o
            );
        }
    },

    /**
     * remove field
     *
     * @param {*} context
     * @param {Number} params field id
     */
    [REMOVE_FIELD](state, slug) {
        if (state.project.fields) {
            state.project.fields = state.project.fields.filter(
                (o) => o.id != slug
            );
        }
    },

    /**
     * remove field
     *
     * @param {*} context
     * @param {Number} params field id
     */
    [SET_NEW_FIELD_FOR](state, fieldFor) {
        state.project.newFieldFor = fieldFor;
    },

    /**
     * Turn field into category
     *
     * @param {*} context
     * @param {Number} params field id
     */
    [FIELD_TO_CATEGORY](state, { slug, data }) {
        if (state.project.fields) {
            state.project.categories = [...state.project.categories, data];
            state.project.fields = state.project.fields.filter(
                (o) => o.id != slug
            );
        }
    },
};

/**
 * Field Store Getters
 */
const getters = {
    /**
     * Get list of fields
     *
     * @param {*} state
     * @returns
     */
    fields: (state) => {
        return state.project && state.project.fields
            ? state.project.fields
            : [];
    },

    /**
     * Get current field
     *
     * @param {*} state
     * @returns
     */
    field: (state) =>
        state.project && state.project.field ? state.project.field : null,

    /**
     * Get current new field for
     *
     * @param {*} state
     * @returns
     */
    newFieldFor: (state) =>
        state.project && state.project.newFieldFor
            ? state.project.newFieldFor
            : "prospect",
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
