import ProspectLabelService from "@/apis/project/prospect/label";
import ProspectRevisionService from "@/apis/project/prospect/revision";
import {
    FETCH_PROSPECT_REVISIONS,
    SET_PROSPECT_REVISION_LOADING,
} from "@/actions/project/prospect/revision";

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
        const project = context.state.project.slug;
        const prospect = context.state.project.prospect.id;
        context.commit(SET_PROSPECT_REVISION_LOADING, {
            project,
            prospect,
            categoryId: label.category_id,
            loading: true,
        });

        try {
            let response;
            try {
                response = await ProspectLabelService.update(
                    project,
                    prospect,
                    label.id
                );
            } catch (error) {
                flashLabelAssignmentError(error);
                throw error;
            }

            const attached =
                (response && response.data && response.data.label) || label;
            await refreshLabelHistory(
                context,
                project,
                prospect,
                ADD_PROSPECT_LABEL,
                attached
            );
        } finally {
            context.commit(SET_PROSPECT_REVISION_LOADING, {
                project,
                prospect,
                categoryId: label.category_id,
                loading: false,
            });
        }
    },

    /**
     * Remove prospect label
     *
     * @param {*} context
     * @param {Number} params prospect label id
     * @returns prospectLabel
     */
    async [REMOVE_PROSPECT_LABEL](context, label) {
        const project = context.state.project.slug;
        const prospect = context.state.project.prospect.id;
        context.commit(SET_PROSPECT_REVISION_LOADING, {
            project,
            prospect,
            categoryId: label.category_id,
            loading: true,
        });

        try {
            try {
                await ProspectLabelService.destroy(
                    project,
                    prospect,
                    label.id
                );
            } catch (error) {
                flashLabelAssignmentError(error);
                throw error;
            }

            await refreshLabelHistory(
                context,
                project,
                prospect,
                REMOVE_PROSPECT_LABEL,
                label
            );
        } finally {
            context.commit(SET_PROSPECT_REVISION_LOADING, {
                project,
                prospect,
                categoryId: label.category_id,
                loading: false,
            });
        }
    },

    /**
     * Add prospect label
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectGroup
     */
    async [BULK_ADD_PROSPECT_LABEL](context, { prospects, labels }) {
        await updateBulkLabels(context, prospects, labels, (project) =>
            ProspectLabelService.bulkUpdate(
                project,
                prospects,
                labels
            )
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
        await updateBulkLabels(context, prospects, labels, (project) =>
            ProspectLabelService.bulkDestroy(
                project,
                prospects,
                labels
            )
        );
    },
};

async function updateBulkLabels(context, prospects, labels, update) {
    const project = context.state.project.slug;
    const prospectIds = [...new Set(prospects)];
    const categoryIds = getLabelCategoryIds(context, labels);

    prospectIds.forEach((prospect) => {
        categoryIds.forEach((categoryId) => {
            context.commit(SET_PROSPECT_REVISION_LOADING, {
                project,
                prospect,
                categoryId,
                loading: true,
            });
        });
    });

    try {
        try {
            await update(project);
        } catch (error) {
            flashLabelAssignmentError(error);
            throw error;
        }

        await Promise.all(
            prospectIds.map(async (prospect) => {
                try {
                    await ProspectRevisionService.get(project, prospect);
                } catch {
                    // Refreshing the history does not undo a successful label change.
                }
            })
        );
    } finally {
        prospectIds.forEach((prospect) => {
            categoryIds.forEach((categoryId) => {
                context.commit(SET_PROSPECT_REVISION_LOADING, {
                    project,
                    prospect,
                    categoryId,
                    loading: false,
                });
            });
        });
    }
}

function flashLabelAssignmentError(error) {
    const data = error?.response?.data;
    const validationError = data?.errors
        ? Object.values(data.errors).flat()[0]
        : null;

    flashError({
        title: "Affectation des étiquettes",
        body:
            validationError ||
            data?.message ||
            error?.message ||
            "Impossible d'affecter les étiquettes au prospect.",
        duration: 7000,
    });
}

function getLabelCategoryIds(context, labels) {
    const labelIds = new Set(labels.map(String));
    const matchedLabelIds = new Set();
    const categoryIds = new Set();

    (context.state.project.categories || []).forEach((category) => {
        (category.labels || []).forEach((label) => {
            if (labelIds.has(String(label.id))) {
                matchedLabelIds.add(String(label.id));
                categoryIds.add(category.id);
            }
        });
    });

    return matchedLabelIds.size === labelIds.size && categoryIds.size
        ? [...categoryIds]
        : ["*"];
}

async function refreshLabelHistory(context, project, prospect, mutation, label) {
    const isCurrent = () =>
        context.state.project?.slug === project &&
        context.state.project?.prospect?.id === prospect;
    if (!isCurrent()) return;

    try {
        await context.dispatch(FETCH_PROSPECT_REVISIONS, { all: true });
    } finally {
        // The label change succeeded even if its history could not be refreshed.
        if (isCurrent()) context.commit(mutation, label);
    }
}

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
