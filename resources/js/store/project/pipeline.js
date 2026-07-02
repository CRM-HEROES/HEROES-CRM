import pipelineService from "@/apis/project/pipeline";

import {
    FETCH_PIPELINES,
    SET_PIPELINES,
    SET_PIPELINE,
    ADD_PIPELINE,
    SHOW_PIPELINE,
    UPDATE_PIPELINE,
    REMOVE_PIPELINE,
} from "@/actions/project/pipeline";

/**
 * pipeline Store state
 */
export const state = {
    pipelines: [],
    pipeline: null,
};

/**
 * pipeline Store Actions
 */
const actions = {
    /**
     * Fetch pipelines
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PIPELINES](context, params) {
        const { data } = await pipelineService.get(
            context.state.project.slug,
            params
        );
        context.commit(SET_PIPELINES, data);
        return data;
    },

    /**
     * Show pipeline
     *
     * @param {*} context
     * @param {Number} slug pipeline id
     * @returns pipeline
     */
    async [SHOW_PIPELINE](context, slug) {
        const { data } = await pipelineService.show(
            context.state.project.slug,
            slug
        );
        context.commit(SET_PIPELINE, data);
        return data;
    },

    /**
     * Add pipeline
     *
     * @param {*} context
     * @param {Object} params pipeline pipeline values
     * @returns pipeline
     */
    async [ADD_PIPELINE](context, params) {
        const { data } = await pipelineService.create(
            context.state.project.slug,
            params
        );
        context.commit(ADD_PIPELINE, data);
        return data;
    },

    /**
     * update pipeline
     *
     * @param {*} context
     * @param {Object} params new pipeline pipeline values
     */
    async [UPDATE_PIPELINE](context, params) {
        await pipelineService.update(
            context.state.project.slug,
            params.id,
            params
        );
        context.commit(UPDATE_PIPELINE, params);
    },

    /**
     * remove pipeline
     *
     * @param {*} context
     * @param {Number} params pipeline id
     * @returns pipeline
     */
    async [REMOVE_PIPELINE](context, slug) {
        await pipelineService.destroy(context.state.project.slug, slug);
        context.commit(REMOVE_PIPELINE, slug);
    },
};

/**
 * pipeline Store Mutations
 */
const mutations = {
    /**
     * Set pipelines
     *
     * @param {*} state
     * @returns
     */
    [SET_PIPELINES](state, pipelines) {
        state.project.pipelines = [...pipelines];
    },

    /**
     * Set current pipeline
     *
     * @param {*} state
     * @param {Object} pipeline
     */
    [SET_PIPELINE](state, pipeline) {
        state.project.pipeline = pipeline;
    },

    /**
     * Add pipeline
     *
     * @param {*} state
     * @param {Number} pipeline to append to pipelines list
     */
    [ADD_PIPELINE](state, pipeline) {
        state.project.pipelines = [...state.project.pipelines, pipeline];
    },

    /**
     * Update pipeline
     *
     * @param {*} state
     */
    [UPDATE_PIPELINE](state, params) {
        state.project.pipelines = state.project.pipelines.map((o) =>
            o.id == params.id ? { ...o, ...params } : o
        );
    },

    /**
     * remove pipeline
     *
     * @param {*} context
     * @param {Number} params pipeline id
     */
    [REMOVE_PIPELINE](state, slug) {
        state.project.pipelines = state.project.pipelines.filter(
            (o) => o.id != slug
        );
    },
};

/**
 * pipeline Store Getters
 */
const getters = {
    /**
     * Get list of pipelines
     *
     * @param {*} state
     * @returns
     */
    pipelines: (state) =>
        state.project && state.project.pipelines ? state.project.pipelines : [],

    /**
     * Get current pipeline
     *
     * @param {*} state
     * @returns
     */
    pipeline: (state) =>
        state.project && state.project.pipeline ? state.project.pipeline : null,
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
