import ProspectLinkService from "@/apis/project/prospect/link";

import {
    FETCH_PROSPECT_LINKS,
    SET_PROSPECT_LINKS,
    SET_PROSPECT_LINK,
    ADD_PROSPECT_LINK,
    UPDATE_PROSPECT_LINK,
    REMOVE_PROSPECT_LINK,
} from "@/actions/project/prospect/link";

/**
 * ProspectLink Store state
 */
export const state = {
    links: [],
};

/**
 * ProspectLink Store Actions
 */
export const actions = {
    /**
     * Fetch prospect links
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PROSPECT_LINKS](context, params) {
        const { data } = await ProspectLinkService.get(
            context.state.project.slug,
            context.state.project.prospect.id,
            params
        );
        context.commit(SET_PROSPECT_LINKS, data);
        return data;
    },

    /**
     * Add prospect link
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectLink
     */
    async [ADD_PROSPECT_LINK](context, params) {
        const { data } = await ProspectLinkService.create(
            context.state.project.slug,
            context.state.project.prospect.id,
            params
        );
        context.commit(ADD_PROSPECT_LINK, data);
        return data;
    },

    /**
     * Update prospect link
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectLink
     */
    async [UPDATE_PROSPECT_LINK](context, params) {
        const { data } = await ProspectLinkService.update(
            context.state.project.slug,
            context.state.project.prospect.id,
            params.id,
            params
        );
        context.commit(UPDATE_PROSPECT_LINK, params);
        return data;
    },

    /**
     * Remove prospect link
     *
     * @param {*} context
     * @param {Number} params prospect link id
     * @returns prospectLink
     */
    async [REMOVE_PROSPECT_LINK](context, slug) {
        await ProspectLinkService.destroy(
            context.state.project.slug,
            context.state.project.prospect.id,
            slug
        );
        context.commit(REMOVE_PROSPECT_LINK, slug);
    },
};

/**
 * Prospect link Store Mutations
 */
export const mutations = {
    /**
     * Set prospect links
     *
     * @param {*} state
     * @returns
     */
    [SET_PROSPECT_LINKS](state, links) {
        state.project.prospect.links = links;
    },

    /**
     * Set prospect link
     *
     * @param {*} state
     * @returns
     */
    [SET_PROSPECT_LINK](state, link) {
        state.project.prospect.link = link;
    },

    /**
     * Add prospect link
     *
     * @param {*} state
     * @param {Number} link to append to prospect links list
     */
    [ADD_PROSPECT_LINK](state, link) {
        state.project.prospect.links = [
            ...(state.project.prospect.links
                ? state.project.prospect.links
                : []),
            link,
        ];
    },

    /**
     * Update prospect link
     *
     * @param {*} state
     * @param {Number} link to append to prospect links list
     */
    [UPDATE_PROSPECT_LINK](state, params) {
        state.project.prospect.links = (
            state.project.prospect.links ? state.project.prospect.links : []
        ).map((o) => (o.id == params.id ? { ...o, ...params } : o));
    },

    /**
     * Remove prospect link
     *
     * @param {*} context
     * @param {Number} params prospect link id
     */
    [REMOVE_PROSPECT_LINK](state, slug) {
        state.project.prospect.links = (
            state.project.prospect.links ? state.project.prospect.links : []
        ).filter((o) => o.id != slug);
    },
};

/**
 * Prospect link Store Getters
 */
export const getters = {
    /**
     * Current prospect link
     *
     * @param {*} state
     * @returns
     */
    prospectLink: (state) =>
        state.project && state.project.prospect && state.project.prospect.link
            ? state.project.prospect.link
            : [],

    /**
     * Get list of prospect links
     *
     * @param {*} state
     * @returns
     */
    prospectLinks: (state) =>
        state.project && state.project.prospect && state.project.prospect.links
            ? state.project.prospect.links
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
