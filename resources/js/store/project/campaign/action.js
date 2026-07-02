import CampaignActionService from "@/apis/project/campaign/action";

import {
    FETCH_CAMPAIGN_ACTIONS,
    SET_CAMPAIGN_ACTIONS,
    ADD_CAMPAIGN_ACTION,
    UPDATE_CAMPAIGN_ACTION,
    REMOVE_CAMPAIGN_ACTION,
} from "@/actions/project/campaign";

/**
 * Campaign Store state
 */
const state = {
    campaignActions: [],
    campaignAction: null,
};

/**
 * Campaign Store Actions
 */
const actions = {
    /**
     * Fetch campaign actions
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_CAMPAIGN_ACTIONS](context, params) {
        const { data } = await CampaignActionService.get(
            context.rootState.project.project.slug,
            params
        );
        context.commit(SET_CAMPAIGN_ACTIONS, data);
        return data;
    },

    /**
     * Add campaign action
     *
     * @param {*} context
     * @param {Object} params campaign action values
     * @returns campaign
     */
    async [ADD_CAMPAIGN_ACTION](context, params) {
        const { data } = await CampaignActionService.create(
            context.rootState.project.project.slug,
            params
        );
        context.commit(ADD_CAMPAIGN_ACTION, data);
        return data;
    },

    /**
     * update campaign
     *
     * @param {*} context
     * @param {Object} params new campaign action values
     */
    [UPDATE_CAMPAIGN_ACTION](context, params) {
        context.commit(UPDATE_CAMPAIGN_ACTION, params);
        CampaignActionService.update(
            context.rootState.project.project.slug,
            params.id,
            params
        );
    },

    /**
     * remove campaign action
     *
     * @param {*} context
     * @param {Number} params campaign action id
     * @returns campaign
     */
    async [REMOVE_CAMPAIGN_ACTION](context, slug) {
        await CampaignActionService.destroy(
            context.rootState.project.project.slug,
            slug
        );
        context.commit(REMOVE_CAMPAIGN_ACTION, slug);
    },
};

/**
 * Campaign Store Mutations
 */
const mutations = {
    /**
     * Set actions
     *
     * @param {*} state
     * @returns
     */
    [SET_CAMPAIGN_ACTIONS](state, actions) {
        state.project.campaign_actions = [...actions];
    },

    /**
     * Add action
     *
     * @param {*} state
     * @param {Number} campaign to append to campaigns list
     */
    [ADD_CAMPAIGN_ACTION](state, action) {
        state.project.campaign_actions = [
            ...state.project.campaign_actions,
            action,
        ];
    },

    /**
     * Update action
     *
     * @param {*} state
     */
    [UPDATE_CAMPAIGN_ACTION](state, params) {
        state.project.campaign_actions = state.project.campaign_actions.map(
            (o) => (o.id == params.id ? { ...o, ...params } : o)
        );
    },

    /**
     * remove action
     *
     * @param {*} context
     * @param {Number} params action id
     */
    [REMOVE_CAMPAIGN_ACTION](state, slug) {
        state.project.campaign_actions = state.project.campaign_actions.filter(
            (o) => o.id != slug
        );

        state.project.campaigns = state.project.campaigns.map((campaign) => {
            if (
                campaign.actions &&
                campaign.actions.find((action) => action.id == slug)
            ) {
                campaign.actions = campaign.actions.filter(
                    (action) => action.id != slug
                );
            }

            return campaign;
        });
    },
};

/**
 * Campaign Store Getters
 */
const getters = {
    /**
     * Get list of actions
     *
     * @param {*} state
     * @returns
     */
    campaignActions: (state) => {
        return state.project &&
            state.project.campaign_actions &&
            Array.isArray(state.project.campaign_actions)
            ? state.project.campaign_actions
            : [];
    },

    /**
     * Get current action
     *
     * @param {*} state
     * @returns
     */
    campaignAction: (state) =>
        state.project && state.project.campaignAction
            ? state.project.campaignAction
            : null,
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
