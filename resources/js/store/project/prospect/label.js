import ProspectLabelService from "@/apis/project/prospect/label";

import {
    FETCH_PROSPECT_LABELS,
    SET_PROSPECT_LABELS,
    ADD_PROSPECT_LABEL,
    REMOVE_PROSPECT_LABEL,
    BULK_ADD_PROSPECT_LABEL,
    BULK_REMOVE_PROSPECT_LABEL,
} from "@/actions/project/prospect/label";

/**
 * ProspectLabel Store state
 */
export const state = {
    labels: [],
};

/**
 * ProspectLabel Store Actions
 */
export const actions = {
    /**
     * Fetch prospect labels
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PROSPECT_LABELS](context, params) {
        const { data } = await ProspectLabelService.get(
            context.state.project.slug,
            context.state.project.prospect.id,
            params
        );
        context.commit(SET_PROSPECT_LABELS, data);
        return data;
    },

    /**
     * Add prospect label
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectLabel
     */
    async [ADD_PROSPECT_LABEL](context, label) {
        context.commit(ADD_PROSPECT_LABEL, label);
        await ProspectLabelService.update(
            context.state.project.slug,
            context.state.project.prospect.id,
            label.id
        );
    },

    /**
     * Remove prospect label
     *
     * @param {*} context
     * @param {Number} params prospect label id
     * @returns prospectLabel
     */
    async [REMOVE_PROSPECT_LABEL](context, label) {
        context.commit(REMOVE_PROSPECT_LABEL, label);
        await ProspectLabelService.destroy(
            context.state.project.slug,
            context.state.project.prospect.id,
            label.id
        );
    },

    /**
     * Add prospect label
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectGroup
     */
    async [BULK_ADD_PROSPECT_LABEL](context, { prospects, labels }) {
        await ProspectLabelService.bulkUpdate(
            context.state.project.slug,
            prospects,
            labels
        );
    },

    /**
     * Remove prospect label
     *
     * @param {*} context
     * @param {Number} params prospect group id
     * @returns prospectGroup
     */
    async [BULK_REMOVE_PROSPECT_LABEL](context, { prospects, labels }) {
        await ProspectLabelService.bulkDestroy(
            context.state.project.slug,
            prospects,
            labels
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
    [SET_PROSPECT_LABELS](state, labels) {
        state.project.prospect.labels = labels;
    },

    /**
     * Add prospect label
     *
     * @param {*} state
     * @param {Number} label to append to prospect labels list
     */
    [ADD_PROSPECT_LABEL](state, label) {
        const category = Array.isArray(state.project.categories)
            ? state.project.categories.find(
                  (category) => category.id == label.category_id
              )
            : null;

        const labels = Array.isArray(state.project.prospect.labels)
            ? state.project.prospect.labels.filter(
                  (l) =>
                      !category ||
                      !category.unique ||
                      category.id != l.category_id
              )
            : [];

        if (!labels.find((l) => l.id == label.id)) {
            state.project.prospect.labels = [...labels, label];

            const index = state.project.prospects.findIndex(
                (p) => p.id == state.project.prospect.id
            );
            if (index >= 0) {
                const p = state.project.prospects[index];
                state.project.prospects[index].labels =
                    state.project.prospect.labels;
            }
        }

        /*state.project.prospects = state.project.prospects.map((o) =>
            o.id == state.project.prospect.id ? state.project.prospect : o
        );*/
    },

    /**
     * Remove prospect label
     *
     * @param {*} context
     * @param {Number} params prospect label id
     */
    [REMOVE_PROSPECT_LABEL](state, label) {
        state.project.prospect.labels = (
            state.project.prospect.labels ? state.project.prospect.labels : []
        ).filter((o) => o.id != label.id);
        const index = state.project.prospects.findIndex(
            (p) => p.id == state.project.prospect.id
        );
        if (index >= 0) {
            const p = state.project.prospects[index];
            state.project.prospects[index].labels =
                state.project.prospect.labels;
        }
        /*state.project.prospects = state.project.prospects.map((o) =>
            o.id == state.project.prospect.id ? state.project.prospect : o
        );*/
    },
};

/**
 * Prospect label Store Getters
 */
export const getters = {
    /**
     * Get list of prospect labels
     *
     * @param {*} state
     * @returns
     */
    prospectLabels: (state) =>
        state.project && state.project.prospect && state.project.prospect.labels
            ? state.project.prospect.labels
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
