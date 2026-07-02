import CampaignService from "@/apis/project/campaign";

import {
    FETCH_CAMPAIGNS,
    SET_CAMPAIGNS,
    SET_CAMPAIGN,
    ADD_CAMPAIGN,
    SHOW_CAMPAIGN,
    UPDATE_CAMPAIGN,
    REMOVE_CAMPAIGN,
    UPDATE_CAMPAIGN_ZOOM,
    ADD_CAMPAIGN_CAMPAIGN_ACTION,
    REMOVE_CAMPAIGN_CAMPAIGN_ACTION,
} from "@/actions/project/campaign";

/**
 * Campaign Store state
 */
const state = {
    campaigns: [],
    campaign: null,
    campaignZoom: 1,
};

/**
 * Campaign Store Actions
 */
const actions = {
    /**
     * Fetch campaigns
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_CAMPAIGNS](context, params) {
        const { data } = await CampaignService.get(
            context.rootState.project.project.slug,
            params
        );
        context.commit(SET_CAMPAIGNS, data);
        return data;
    },

    /**
     * Show campaign
     *
     * @param {*} context
     * @param {Number} slug campaign id
     * @returns campaign
     */
    async [SHOW_CAMPAIGN](context, slug) {
        context.commit(SET_CAMPAIGN, {
            id: slug,
        });
        const { data } = await CampaignService.show(
            context.rootState.project.project.slug,
            slug
        );
        context.commit(SET_CAMPAIGN, data);
        return data;
    },

    /**
     * Add campaign
     *
     * @param {*} context
     * @param {Object} params campaign action values
     * @returns campaign
     */
    async [ADD_CAMPAIGN](context, params) {
        const { data } = await CampaignService.create(
            context.rootState.project.project.slug,
            params
        );
        context.commit(ADD_CAMPAIGN, data);
        return data;
    },

    /**
     * update campaign
     *
     * @param {*} context
     * @param {Object} params new campaign action values
     */
    async [UPDATE_CAMPAIGN](context, params) {
        await CampaignService.update(
            context.rootState.project.project.slug,
            params.id,
            params
        );
        context.commit(UPDATE_CAMPAIGN, params);
    },

    /**
     * remove campaign
     *
     * @param {*} context
     * @param {Number} params campaign page id
     * @returns campaign
     */
    async [REMOVE_CAMPAIGN](context, slug) {
        await CampaignService.destroy(
            context.rootState.project.project.slug,
            slug
        );
        context.commit(REMOVE_CAMPAIGN, slug);
    },

    /**
     * Add campaign action
     *
     * @param {*} state
     * @param {Object} campaign
     */
    async [ADD_CAMPAIGN_CAMPAIGN_ACTION](context, { campaign, action }) {
        context.commit(ADD_CAMPAIGN_CAMPAIGN_ACTION, { campaign, action });
        await CampaignService.addAction(
            context.rootState.project.project.slug,
            campaign.id,
            action.id
        );
    },

    /**
     * Remove campaign action
     *
     * @param {*} state
     * @param {Object} campaign
     */
    async [REMOVE_CAMPAIGN_CAMPAIGN_ACTION](context, { campaign, action }) {
        context.commit(REMOVE_CAMPAIGN_CAMPAIGN_ACTION, {
            campaign,
            action,
        });
        await CampaignService.removeAction(
            context.rootState.project.project.slug,
            campaign.id,
            action.id
        );
    },
};

/**
 * Campaign Store Mutations
 */
const mutations = {
    /**
     * Set campaigns
     *
     * @param {*} state
     * @returns
     */
    [SET_CAMPAIGNS](state, campaigns) {
        state.project.campaigns = [...campaigns];
    },

    /**
     * Set current campaign
     *
     * @param {*} state
     * @param {Object} campaign
     */
    [SET_CAMPAIGN](state, campaign) {
        state.project.campaign = campaign;
    },

    /**
     * Add campaign
     *
     * @param {*} state
     * @param {Number} campaign to append to campaigns list
     */
    [ADD_CAMPAIGN](state, campaign) {
        state.project.campaigns = [...state.project.campaigns, campaign];
    },

    /**
     * Update campaign
     *
     * @param {*} state
     */
    [UPDATE_CAMPAIGN](state, params) {
        state.project.campaigns = state.project.campaigns.map((o) =>
            o.id == params.id ? { ...o, ...params } : o
        );
    },

    /**
     * remove campaign
     *
     * @param {*} context
     * @param {Number} params campaign id
     */
    [REMOVE_CAMPAIGN](state, slug) {
        state.project.campaigns = state.project.campaigns.filter(
            (o) => o.id != slug
        );

        state.project.campaign_rules = state.project.campaign_rules.map(
            (rule) => {
                if (
                    rule.campaigns &&
                    rule.campaigns.find((campaign) => campaign.id == slug)
                ) {
                    rule.campaigns = rule.campaigns.filter(
                        (campaign) => campaign.id != slug
                    );
                }

                return rule;
            }
        );

        state.project.campaign_operators = state.project.campaign_operators.map(
            (operator) => {
                if (
                    operator.campaigns &&
                    operator.campaigns.find((campaign) => campaign.id == slug)
                ) {
                    operator.campaigns = operator.campaigns.filter(
                        (campaign) => campaign.id != slug
                    );
                }

                return operator;
            }
        );
    },

    /**
     * Zoom in campaign
     *
     * @param {*} state
     */
    [UPDATE_CAMPAIGN_ZOOM](state, value) {
        state.project.campaignZoom = value;
    },

    /**
     * Add campaign action
     *
     * @param {*} state
     * @param {Object} campaign
     */
    [ADD_CAMPAIGN_CAMPAIGN_ACTION](state, { campaign, action }) {
        const c = state.project.campaigns.find((c) => c.id == campaign.id);
        if (c) {
            c.actions = [...(c.actions ? c.actions : []), action];
        }
    },

    /**
     * Remove campaign action
     *
     * @param {*} state
     * @param {Object} campaign
     */
    [REMOVE_CAMPAIGN_CAMPAIGN_ACTION](state, { campaign, action }) {
        const c = state.project.campaigns.find((c) => c.id == campaign.id);
        if (c) {
            c.actions = c.actions.filter((a) => a.id != action.id);
        }
    },
};

/**
 * Campaign Store Getters
 */
const getters = {
    /**
     * Get campaigns
     *
     * @param {*} state
     * @returns
     */
    campaigns: (state) =>
        state.project &&
        state.project.campaigns &&
        Array.isArray(state.project.campaigns)
            ? state.project.campaigns
            : null,

    /**
     * Get current campaign
     *
     * @param {*} state
     * @returns
     */
    campaign: (state) =>
        state.project && state.project.campaign ? state.project.campaign : null,

    /**
     * Get campaign zoom
     *
     * @param {*} state
     * @returns
     */
    campaignZoom: (state) =>
        state.project && state.project.campaignZoom
            ? state.project.campaignZoom
            : 1,
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
