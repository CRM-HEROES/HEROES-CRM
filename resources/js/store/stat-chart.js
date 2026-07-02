import chartService from "@/apis/project/stat/chart";

import {
    FETCH_CHARTS,
    SET_CHARTS,
    SET_CHART,
    ADD_CHART,
    SHOW_CHART,
    UPDATE_CHART,
    REMOVE_CHART,
} from "@/actions/project/stat/chart";

/**
 * chart Store state
 */
export const state = {
    charts: [],
    chart: null,
};

/**
 * chart Store Actions
 */
const actions = {
    /**
     * Fetch charts
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_CHARTS](context, params) {
        const { data } = await chartService.get(params);
        context.commit(SET_CHARTS, data);
        return data;
    },

    /**
     * Show chart
     *
     * @param {*} context
     * @param {Number} slug chart id
     * @returns chart
     */
    async [SHOW_CHART](context, { project, slug, params }) {
        const { data } = await chartService.show(project, slug, params);
        context.commit(SET_CHART, data);
        return data;
    },

    /**
     * Add chart
     *
     * @param {*} context
     * @param {Object} params chart chart values
     * @returns chart
     */
    async [ADD_CHART](context, params) {
        const { data } = await chartService.create(params.project, params);
        context.commit(ADD_CHART, data);
        return data;
    },

    /**
     * update chart
     *
     * @param {*} context
     * @param {Object} params new chart chart values
     */
    async [UPDATE_CHART](context, params) {
        await chartService.update(params.project, params.id, params);
        context.commit(UPDATE_CHART, params);
    },

    /**
     * remove chart
     *
     * @param {*} context
     * @param {Number} params chart id
     * @returns chart
     */
    async [REMOVE_CHART](context, { project, slug }) {
        await chartService.destroy(project, slug);
        context.commit(REMOVE_CHART, slug);
    },
};

/**
 * chart Store Mutations
 */
const mutations = {
    /**
     * Set charts
     *
     * @param {*} state
     * @returns
     */
    [SET_CHARTS](state, charts) {
        state.statCharts = [...charts];
    },

    /**
     * Set current chart
     *
     * @param {*} state
     * @param {Object} chart
     */
    [SET_CHART](state, chart) {
        state.statChart = chart;
        state.statCharts = state.statCharts.map((o) =>
            o.id == chart.id ? { ...o, ...chart } : o
        );
    },

    /**
     * Add chart
     *
     * @param {*} state
     * @param {Number} chart to append to charts list
     */
    [ADD_CHART](state, chart) {
        state.statCharts = [...state.statCharts, { ...chart, data: [] }];
    },

    /**
     * Update chart
     *
     * @param {*} state
     */
    [UPDATE_CHART](state, params) {
        state.statCharts = state.statCharts.map((o) =>
            o.id == params.id ? { ...o, ...params } : o
        );
    },

    /**
     * remove chart
     *
     * @param {*} context
     * @param {Number} params chart id
     */
    [REMOVE_CHART](state, slug) {
        state.statCharts = state.statCharts.filter((o) => o.id != slug);
    },
};

/**
 * chart Store Getters
 */
const getters = {
    /**
     * Get list of charts
     *
     * @param {*} state
     * @returns
     */
    statCharts: (state) => (state.statCharts ? state.statCharts : []),

    /**
     * Get current chart
     *
     * @param {*} state
     * @returns
     */
    statChart: (state) => (state.statChart ? state.statChart : null),
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
