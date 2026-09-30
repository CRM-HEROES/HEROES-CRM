import ProspectInteractionService from "@/apis/project/prospect/interaction";

import {
    FETCH_PROSPECT_INTERACTIONS,
    SET_PROSPECT_INTERACTIONS,
    ADD_PROSPECT_INTERACTION,
    UPDATE_PROSPECT_INTERACTION,
    REMOVE_PROSPECT_INTERACTION,
    SET_INTERACTION_PROSPECT,
    SET_PROSPECT_INTERACTION_TAB,
    SET_PROSPECT_INTERACTION_FRAME_TAB,
} from "@/actions/project/prospect/interaction";

/**
 * ProspectInteraction Store state
 */
export const state = {
    interactions: [],
    interactionTab: 0,
    interactionFrameTab: 0,
};

/**
 * ProspectInteraction Store Actions
 */
export const actions = {
    /**
     * Fetch prospect interactions
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PROSPECT_INTERACTIONS](context, params) {
        const { data } = await ProspectInteractionService.get(
            context.state.project.slug,
            context.state.project.interactionProspect.id,
            params
        );
        context.commit(SET_PROSPECT_INTERACTIONS, data);
        return data;
    },

    /**
     * Add prospect interaction
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectInteraction
     */
    async [ADD_PROSPECT_INTERACTION](context, params) {
        const { data } = await ProspectInteractionService.create(
            context.state.project.slug,
            context.state.project.interactionProspect.id,
            params
        );
        context.commit(ADD_PROSPECT_INTERACTION, data);
        return data;
    },

    /**
     * Update prospect interaction
     *
     * @param {*} context
     * @param {Object} params
     * @returns prospectInteraction
     */
    async [UPDATE_PROSPECT_INTERACTION](context, params) {
        const { data } = await ProspectInteractionService.update(
            context.state.project.slug,
            context.state.project.interactionProspect.id,
            params.id,
            params
        );
        context.commit(UPDATE_PROSPECT_INTERACTION, data);
        return data;
    },

    /**
     * Remove prospect interaction
     *
     * @param {*} context
     * @param {Number} params prospect interaction id
     * @returns prospectInteraction
     */
    async [REMOVE_PROSPECT_INTERACTION](context, slug) {
        await ProspectInteractionService.destroy(
            context.state.project.slug,
            context.state.project.interactionProspect.id,
            slug
        );
        context.commit(REMOVE_PROSPECT_INTERACTION, slug);
    },
};

/**
 * Prospect interaction Store Mutations
 */
export const mutations = {
    /**
     * Set prospect interactions
     *
     * @param {*} state
     * @returns
     */
    [SET_PROSPECT_INTERACTIONS](state, interactions) {
        state.project.interactionProspect.interactions = interactions;
    },

    /**
     * Add prospect interaction
     *
     * @param {*} state
     * @param {Number} interaction to append to prospect interactions list
     */
    [ADD_PROSPECT_INTERACTION](state, interaction) {
        state.project.interactionProspect.interactions = [
            interaction,
            ...(state.project.interactionProspect.interactions
                ? state.project.interactionProspect.interactions
                : []),
        ];
        state.project.prospects = state.project.prospects.map((o) =>
            o.id == state.project.interactionProspect.id
                ? { ...o, ...state.project.interactionProspect }
                : o
        );
    },

    /**
     * Update prospect interaction
     *
     * @param {*} state
     * @param {Number} interaction to append to prospect interactions list
     */
    [UPDATE_PROSPECT_INTERACTION](state, params) {
        state.project.interactionProspect.interactions = (
            state.project.interactionProspect.interactions
                ? state.project.interactionProspect.interactions
                : []
        ).map((o) => (o.id == params.id ? { ...o, ...params } : o));

        state.project.prospects = state.project.prospects.map((o) =>
            o.id == state.project.interactionProspect.id
                ? { ...o, ...state.project.interactionProspect }
                : o
        );
    },

    /**
     * Remove prospect interaction
     *
     * @param {*} context
     * @param {Number} params prospect interaction id
     */
    [REMOVE_PROSPECT_INTERACTION](state, slug) {
        state.project.interactionProspect.interactions = (
            state.project.interactionProspect.interactions
                ? state.project.interactionProspect.interactions
                : []
        ).filter((o) => o.id != slug);
        state.project.prospects = state.project.prospects.map((o) =>
            o.id == state.project.interactionProspect.id
                ? { ...o, ...state.project.interactionProspect }
                : o
        );
    },

    /**
     * Set interaction prospect
     *
     * @param {*} context
     * @param {Number} tab
     */
    [SET_INTERACTION_PROSPECT](state, prospect) {
        state.project.interactionProspect = prospect;
    },

    /**
     * Set prospect interaction tab
     *
     * @param {*} context
     * @param {Number} tab
     */
    [SET_PROSPECT_INTERACTION_TAB](state, tab) {
        state.project.interactionTab = tab;
    },

    /**
     * Set prospect interaction frame tab
     *
     * @param {*} context
     * @param {Number} tab
     */
    [SET_PROSPECT_INTERACTION_FRAME_TAB](state, tab) {
        state.project.interactionFrameTab = tab;
    },
};

/**
 * Prospect interaction Store Getters
 */
export const getters = {
    /**
     * Get list of prospect interactions
     *
     * @param {*} state
     * @returns
     */
    prospectInteractions: (state) =>
        state.project &&
        state.project.interactionProspect &&
        state.project.interactionProspect.interactions
            ? state.project.interactionProspect.interactions
            : [],

    /**
     * Get current interaction prospect
     *
     * @param {*} state
     * @returns
     */
    interactionProspect: (state) =>
        state.project && state.project.interactionProspect
            ? state.project.interactionProspect
            : null,

    /**
     * Get current prospect interaction tab
     *
     * @param {*} state
     * @returns
     */
    interactionTab: (state) =>
        state.project && state.project.interactionTab
            ? state.project.interactionTab
            : 0,

    /**
     * Get current prospect interaction frame tab
     *
     * @param {*} state
     * @returns
     */
    interactionFrameTab: (state) =>
        state.project && state.project.interactionFrameTab
            ? state.project.interactionFrameTab
            : 0,
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
