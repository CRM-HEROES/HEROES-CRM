import metricService from "@/apis/project/stat/metric";

import {
    FETCH_METRICS,
    SET_METRICS,
    SET_METRIC,
    ADD_METRIC,
    SHOW_METRIC,
    UPDATE_METRIC,
    REMOVE_METRIC,
} from "@/actions/project/stat/metric";

/**
 * metric Store state
 */
export const state = {
    metrics: [],
    metric: null,
};

/**
 * metric Store Actions
 */
const actions = {
    /**
     * Fetch metrics
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_METRICS](context, params) {
        const { data } = await metricService.get(
            context.state.project.slug,
            params
        );
        context.commit(SET_METRICS, data);
        return data;
    },

    /**
     * Show metric
     *
     * @param {*} context
     * @param {Number} slug metric id
     * @returns metric
     */
    async [SHOW_METRIC](context, slug) {
        const { data } = await metricService.show(
            context.state.project.slug,
            slug
        );
        context.commit(SET_METRIC, data);
        return data;
    },

    /**
     * Add metric
     *
     * @param {*} context
     * @param {Object} params metric metric values
     * @returns metric
     */
    async [ADD_METRIC](context, params) {
        const { data } = await metricService.create(
            context.state.project.slug,
            params
        );
        context.commit(ADD_METRIC, data);
        return data;
    },

    /**
     * update metric
     *
     * @param {*} context
     * @param {Object} params new metric metric values
     */
    async [UPDATE_METRIC](context, params) {
        await metricService.update(
            context.state.project.slug,
            params.id,
            params
        );
        context.commit(UPDATE_METRIC, params);
    },

    /**
     * remove metric
     *
     * @param {*} context
     * @param {Number} params metric id
     * @returns metric
     */
    async [REMOVE_METRIC](context, slug) {
        await metricService.destroy(context.state.project.slug, slug);
        context.commit(REMOVE_METRIC, slug);
    },
};

/**
 * metric Store Mutations
 */
const mutations = {
    /**
     * Set metrics
     *
     * @param {*} state
     * @returns
     */
    [SET_METRICS](state, metrics) {
        state.project.metrics = [...metrics];
    },

    /**
     * Set current metric
     *
     * @param {*} state
     * @param {Object} metric
     */
    [SET_METRIC](state, metric) {
        state.project.metric = metric;
    },

    /**
     * Add metric
     *
     * @param {*} state
     * @param {Number} metric to append to metrics list
     */
    [ADD_METRIC](state, metric) {
        state.project.metrics = [...state.project.metrics, metric];
    },

    /**
     * Update metric
     *
     * @param {*} state
     */
    [UPDATE_METRIC](state, params) {
        state.project.metrics = state.project.metrics.map((o) =>
            o.id == params.id ? { ...o, ...params } : o
        );
    },

    /**
     * remove metric
     *
     * @param {*} context
     * @param {Number} params metric id
     */
    [REMOVE_METRIC](state, slug) {
        state.project.metrics = state.project.metrics.filter(
            (o) => o.id != slug
        );
    },
};

/**
 * metric Store Getters
 */
const getters = {
    /**
     * Get list of metrics
     *
     * @param {*} state
     * @returns
     */
    metrics: (state) => (state.project.metrics ? state.project.metrics : []),

    /**
     * Get current metric
     *
     * @param {*} state
     * @returns
     */
    metric: (state) => (state.project.metric ? state.project.metric : null),
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
