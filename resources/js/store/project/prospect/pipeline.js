import ProspectPipelineService from "@/apis/project/prospect/pipeline";

import {
    FETCH_PROSPECT_PIPELINE_LABEL,
    UPDATE_PROSPECT_PIPELINE_LABEL,
} from "@/actions/project/prospect/pipeline";

/**
 * ProspectPipeline Store Actions
 */
export const actions = {
    /**
     * Fetch prospect labels
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PROSPECT_PIPELINE_LABEL](context, pipeline) {
        const { data } = await ProspectPipelineService.get(
            context.state.project.slug,
            context.state.project.prospect.id,
            pipeline
        );
        context.commit(UPDATE_PROSPECT_PIPELINE_LABEL, data);
        return data;
    },

    /**
     * Add prospect label
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectLabel
     */
    async [UPDATE_PROSPECT_PIPELINE_LABEL](context, { pipeline, label }) {
        context.commit(UPDATE_PROSPECT_PIPELINE_LABEL, { pipeline, label });
        await ProspectPipelineService.update(
            context.state.project.slug,
            context.state.project.prospect.id,
            pipeline.id,
            label.id
        );
    },
};

/**
 * Prospect label Store Mutations
 */
export const mutations = {
    /**
     * Set prospect labels
     *
     * @param {*} state
     * @returns
     */
    [UPDATE_PROSPECT_PIPELINE_LABEL](state, { pipeline, label }) {
        let labels = state.project.prospect.labels;

        if (pipeline.labels) {
            labels = labels.filter(
                (label) => !pipeline.labels.find((l) => label.id == l.id)
            );
        }

        state.project.prospect.labels = [...labels, label];
        state.project.prospects = state.project.prospects.map((p) =>
            p.id == state.project.prospect.id
                ? { ...p, ...state.project.prospect }
                : p
        );
    },
};

/**
 * Prospect pipeline labels Store Getters
 */
export const getters = {
    /**
     * Get list of prospect labels
     *
     * @param {*} state
     * @returns
     */
    prospectPipelineLabels: (state) => (pipeline) =>
        state.project && state.project.prospect && state.project.prospect.labels
            ? state.project.prospect.labels.find((label) =>
                  pipeline.labels.find((l) => label.id == l.id)
              )
            : null,
};

/**
 * Store
 */
export default {
    actions,
    mutations,
    getters,
};
