import CampaignRuleService from "@/apis/project/campaign/rule";
import CampaignOperatorService from "@/apis/project/campaign/operator";
import CampaignService from "@/apis/project/campaign";

import {
    FETCH_CAMPAIGN_RULES,
    SET_CAMPAIGN_RULES,
    SET_CAMPAIGN_RULE,
    ADD_CAMPAIGN_RULE,
    UPDATE_CAMPAIGN_RULE,
    REMOVE_CAMPAIGN_RULE,
    ADD_CAMPAIGN_RULE_OPERATOR,
    REMOVE_CAMPAIGN_RULE_OPERATOR,
    ADD_CAMPAIGN_RULE_CAMPAIGN,
    REMOVE_CAMPAIGN_RULE_CAMPAIGN,
} from "@/actions/project/campaign";

/**
 * Campaign Store state
 */
const state = {
    campaignRules: [],
    campaignRule: null,
};

/**
 * Campaign Store Rules
 */
const actions = {
    /**
     * Fetch campaign rules
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_CAMPAIGN_RULES](context, params) {
        const { data } = await CampaignRuleService.get(
            context.rootState.project.project.slug,
            params
        );
        context.commit(SET_CAMPAIGN_RULES, data);
        return data;
    },

    /**
     * Add campaign rule
     *
     * @param {*} context
     * @param {Object} params campaign rule values
     * @returns campaign
     */
    async [ADD_CAMPAIGN_RULE](context, params) {
        const { data } = await CampaignRuleService.create(
            context.rootState.project.project.slug,
            params
        );
        context.commit(ADD_CAMPAIGN_RULE, data);
        return data;
    },

    /**
     * update campaign
     *
     * @param {*} context
     * @param {Object} params new campaign rule values
     */
    [UPDATE_CAMPAIGN_RULE](context, params) {
        context.commit(UPDATE_CAMPAIGN_RULE, params);
        CampaignRuleService.update(
            context.rootState.project.project.slug,
            params.id,
            params
        );
    },

    /**
     * remove campaign rule
     *
     * @param {*} context
     * @param {Number} params campaign rule id
     * @returns campaign
     */
    async [REMOVE_CAMPAIGN_RULE](context, slug) {
        await CampaignRuleService.destroy(
            context.rootState.project.project.slug,
            slug
        );
        context.commit(REMOVE_CAMPAIGN_RULE, slug);
    },

    /**
     * Add rule operator
     *
     * @param {*} state
     * @param {Object} campaign
     */
    async [ADD_CAMPAIGN_RULE_OPERATOR](context, { rule, operator }) {
        context.commit(ADD_CAMPAIGN_RULE_OPERATOR, { rule, operator });
        await CampaignOperatorService.addRule(
            context.rootState.project.project.slug,
            operator.id,
            rule.id
        );
    },

    /**
     * Remove rule operator
     *
     * @param {*} state
     * @param {Object} campaign
     */
    async [REMOVE_CAMPAIGN_RULE_OPERATOR](context, { rule, operator }) {
        context.commit(REMOVE_CAMPAIGN_RULE_OPERATOR, { rule, operator });
        await CampaignOperatorService.removeRule(
            context.rootState.project.project.slug,
            operator.id,
            rule.id
        );
    },

    /**
     * Add rule campaign
     *
     * @param {*} state
     * @param {Object} campaign
     */
    async [ADD_CAMPAIGN_RULE_CAMPAIGN](context, { rule, campaign }) {
        context.commit(ADD_CAMPAIGN_RULE_CAMPAIGN, { rule, campaign });
        await CampaignService.addRule(
            context.rootState.project.project.slug,
            campaign.id,
            rule.id
        );
    },

    /**
     * Remove rule campaign
     *
     * @param {*} state
     * @param {Object} campaign
     */
    async [REMOVE_CAMPAIGN_RULE_CAMPAIGN](context, { rule, campaign }) {
        context.commit(REMOVE_CAMPAIGN_RULE_CAMPAIGN, { rule, campaign });
        await CampaignService.removeRule(
            context.rootState.project.project.slug,
            campaign.id,
            rule.id
        );
    },
};

/**
 * Campaign Store Mutations
 */
const mutations = {
    /**
     * Set rules
     *
     * @param {*} state
     * @returns
     */
    [SET_CAMPAIGN_RULES](state, rules) {
        state.project.campaign_rules = [...rules];
    },

    /**
     * Set current rule
     *
     * @param {*} state
     * @param {Object} campaign
     */
    [SET_CAMPAIGN_RULE](state, campaignRule) {
        state.project.campaignRule = campaignRule;
    },

    /**
     * Add rule
     *
     * @param {*} state
     * @param {Number} campaign to append to campaigns list
     */
    [ADD_CAMPAIGN_RULE](state, rule) {
        state.project.campaign_rules = [...state.project.campaign_rules, rule];
    },

    /**
     * Update rule
     *
     * @param {*} state
     */
    [UPDATE_CAMPAIGN_RULE](state, params) {
        state.project.campaign_rules = state.project.campaign_rules.map((o) =>
            o.id == params.id ? { ...o, ...params } : o
        );
    },

    /**
     * remove rule
     *
     * @param {*} context
     * @param {Number} params rule id
     */
    [REMOVE_CAMPAIGN_RULE](state, slug) {
        state.project.campaign_rules = state.project.campaign_rules.filter(
            (o) => o.id != slug
        );
    },

    /**
     * Add rule operator
     *
     * @param {*} state
     * @param {Object} campaign
     */
    [ADD_CAMPAIGN_RULE_OPERATOR](state, { rule, operator }) {
        const r = state.project.campaign_rules.find((r) => r.id == rule.id);
        if (r) {
            r.operators = [...(r.operators ? r.operators : []), operator];
        }
    },

    /**
     * Remove rule operator
     *
     * @param {*} state
     * @param {Object} campaign
     */
    [REMOVE_CAMPAIGN_RULE_OPERATOR](state, { rule, operator }) {
        const r = state.project.campaign_rules.find((r) => r.id == rule.id);
        if (r) {
            r.operators = r.operators.filter((o) => o.id != operator.id);
        }
    },

    /**
     * Add rule campaign
     *
     * @param {*} state
     * @param {Object} campaign
     */
    [ADD_CAMPAIGN_RULE_CAMPAIGN](state, { rule, campaign }) {
        const r = state.project.campaign_rules.find((r) => r.id == rule.id);
        if (r) {
            r.campaigns = [...(r.campaigns ? r.campaigns : []), campaign];
        }
    },

    /**
     * Remove rule campaign
     *
     * @param {*} state
     * @param {Object} campaign
     */
    [REMOVE_CAMPAIGN_RULE_CAMPAIGN](state, { rule, campaign }) {
        const r = state.project.campaign_rules.find((r) => r.id == rule.id);
        if (r) {
            r.campaigns = r.campaigns.filter((o) => o.id != campaign.id);
        }
    },
};

/**
 * Campaign Store Getters
 */
const getters = {
    /**
     * Get list of rules
     *
     * @param {*} state
     * @returns
     */
    campaignRules: (state) => {
        return state.project &&
            state.project.campaign_rules &&
            Array.isArray(state.project.campaign_rules)
            ? state.project.campaign_rules
            : [];
    },

    /**
     * Get current rule
     *
     * @param {*} state
     * @returns
     */
    campaignRule: (state) =>
        state.project && state.project.campaignRule
            ? state.project.campaignRule
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
