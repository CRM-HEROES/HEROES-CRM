import ProspectRevisionService from "@/apis/project/prospect/revision";

import {
    FETCH_PROSPECT_REVISIONS,
    SET_PROSPECT_REVISIONS,
} from "@/actions/project/prospect/revision";

const pendingFetches = new WeakMap();

/**
 * ProspectRevision Store state
 */
export const state = {
    revisions: [],
};

/**
 * ProspectRevision Store Actions
 */
export const actions = {
    /**
     * Fetch prospect revisions
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PROSPECT_REVISIONS](context, params) {
        const project = context.state.project.slug;
        const prospect = context.state.project.prospect.id;
        const all = params && params.all;
        const mode = all ? "all" : "page";
        const request = {};
        const pending = pendingFetches.get(context.state) || {};
        pending[mode] = request;
        pendingFetches.set(context.state, pending);
        const isCurrent = () =>
            pendingFetches.get(context.state)[mode] === request &&
            context.state.project?.slug === project &&
            context.state.project?.prospect?.id === prospect;

        const query = all
            ? { ...params, params: { ...params.params, page: 1 } }
            : params;
        if (all) delete query.all;

        let { data } = await ProspectRevisionService.get(project, prospect, query);
        if (all) {
            const revisions = [...data.data];
            for (let page = 2; page <= data.last_page && isCurrent(); page++) {
                const response = await ProspectRevisionService.get(project, prospect, {
                    ...query,
                    params: { ...query.params, page },
                });
                revisions.push(...response.data.data);
            }
            data = { ...data, data: revisions };
        }

        // Keep the previous timeline visible until every page is ready.
        if (isCurrent()) context.commit(SET_PROSPECT_REVISIONS, data);
        return data;
    },
};

/**
 * Prospect revision Store Mutations
 */
export const mutations = {
    /**
     * Set prospect revisions
     *
     * @param {*} state
     * @returns
     */
    [SET_PROSPECT_REVISIONS](state, revisions) {
        const previous = state.project.prospect.revisions;
        const existing = previous && Array.isArray(previous.data) ? previous.data : [];
        if (existing.length) {
            // Paginated revision views share this state with the full timeline.
            const entries = new Map(
                [...existing, ...revisions.data].map((item) => [item.id, item])
            );
            state.project.prospect.revisions = {
                ...revisions,
                data: [...entries.values()].sort((left, right) =>
                    new Date(right.created_at) - new Date(left.created_at) ||
                    right.id - left.id
                ),
            };
        } else {
            state.project.prospect.revisions = revisions;
        }
    },
};

/**
 * Prospect revision Store Getters
 */
export const getters = {
    /**
     * Get list of prospect revisions
     *
     * @param {*} state
     * @returns
     */
    prospectRevisions: (state) =>
        state.project &&
        state.project.prospect &&
        state.project.prospect.revisions
            ? state.project.prospect.revisions
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
