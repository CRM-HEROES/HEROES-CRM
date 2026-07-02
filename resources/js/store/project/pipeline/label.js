import PipelineLabelService from "@/apis/project/pipeline/label";

import {
    FETCH_PIPELINE_LABELS,
    SET_PIPELINE_LABELS,
    ADD_PIPELINE_LABEL,
    REMOVE_PIPELINE_LABEL,
    BULK_ADD_PIPELINE_LABEL,
    BULK_REMOVE_PIPELINE_LABEL,
} from "@/actions/project/pipeline/label";

/**
 * PipelineLabel Store state
 */
export const state = {
    labels: [],
};

/**
 * PipelineLabel Store Actions
 */
export const actions = {
    /**
     * Fetch pipeline labels
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PIPELINE_LABELS](context, params) {
        const { data } = await PipelineLabelService.get(
            context.state.project.slug,
            context.state.project.pipeline.id,
            params
        );
        context.commit(SET_PIPELINE_LABELS, data);
        return data;
    },

    /**
     * Add pipeline label
     *
     * @param {*} context
     * @param {Object} params
     * @returns pipelineLabel
     */
    async [ADD_PIPELINE_LABEL](context, label) {
        context.commit(ADD_PIPELINE_LABEL, label);
        await PipelineLabelService.update(
            context.state.project.slug,
            context.state.project.pipeline.id,
            label.id
        );
    },

    /**
     * Remove pipeline label
     *
     * @param {*} context
     * @param {Number} params pipeline label id
     * @returns pipelineLabel
     */
    async [REMOVE_PIPELINE_LABEL](context, label) {
        context.commit(REMOVE_PIPELINE_LABEL, label);
        await PipelineLabelService.destroy(
            context.state.project.slug,
            context.state.project.pipeline.id,
            label.id
        );
    },

    /**
     * Add pipeline label
     *
     * @param {*} context
     * @param {Object} params
     * @returns pipelineGroup
     */
    async [BULK_ADD_PIPELINE_LABEL](context, { pipelines, labels }) {
        await PipelineLabelService.bulkUpdate(
            context.state.project.slug,
            pipelines,
            labels
        );
    },

    /**
     * Remove pipeline label
     *
     * @param {*} context
     * @param {Number} params pipeline group id
     * @returns pipelineGroup
     */
    async [BULK_REMOVE_PIPELINE_LABEL](context, { pipelines, labels }) {
        await PipelineLabelService.bulkDestroy(
            context.state.project.slug,
            pipelines,
            labels
        );
    },
};

/**
 * Pipeline label Store Mutations
 */
export const mutations = {
    /**
     * Set pipeline labels
     *
     * @param {*} state
     * @returns
     */
    [SET_PIPELINE_LABELS](state, labels) {
        state.project.pipeline.labels = labels;
    },

    /**
     * Add pipeline label
     *
     * @param {*} state
     * @param {Number} label to append to pipeline labels list
     */
    [ADD_PIPELINE_LABEL](state, label) {
        const category = Array.isArray(state.project.categories)
            ? state.project.categories.find(
                  (category) => category.id == label.category_id
              )
            : null;

        const labels = Array.isArray(state.project.pipeline.labels)
            ? state.project.pipeline.labels.filter(
                  (l) =>
                      !category ||
                      !category.unique ||
                      category.id != l.category_id
              )
            : [];

        if (!labels.find((l) => l.id == label.id)) {
            state.project.pipeline.labels = [...labels, label];

            const index = state.project.pipelines.findIndex(
                (p) => p.id == state.project.pipeline.id
            );
            if (index >= 0) {
                const p = state.project.pipelines[index];
                state.project.pipelines[index].labels =
                    state.project.pipeline.labels;
            }
        }

        /*state.project.pipelines = state.project.pipelines.map((o) =>
            o.id == state.project.pipeline.id ? state.project.pipeline : o
        );*/
    },

    /**
     * Remove pipeline label
     *
     * @param {*} context
     * @param {Number} params pipeline label id
     */
    [REMOVE_PIPELINE_LABEL](state, label) {
        state.project.pipeline.labels = (
            state.project.pipeline.labels ? state.project.pipeline.labels : []
        ).filter((o) => o.id != label.id);
        const index = state.project.pipelines.findIndex(
            (p) => p.id == state.project.pipeline.id
        );
        if (index >= 0) {
            const p = state.project.pipelines[index];
            state.project.pipelines[index].labels =
                state.project.pipeline.labels;
        }
        /*state.project.pipelines = state.project.pipelines.map((o) =>
            o.id == state.project.pipeline.id ? state.project.pipeline : o
        );*/
    },
};

/**
 * Pipeline label Store Getters
 */
export const getters = {
    /**
     * Get list of pipeline labels
     *
     * @param {*} state
     * @returns
     */
    pipelineLabels: (state) =>
        state.project && state.project.pipeline && state.project.pipeline.labels
            ? state.project.pipeline.labels
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
