import CampaignOperatorService from "@/apis/project/campaign/operator";
import CampaignService from "@/apis/project/campaign";

import {
    FETCH_CAMPAIGN_OPERATORS,
    SET_CAMPAIGN_OPERATORS,
    SET_CAMPAIGN_OPERATOR,
    ADD_CAMPAIGN_OPERATOR,
    UPDATE_CAMPAIGN_OPERATOR,
    REMOVE_CAMPAIGN_OPERATOR,
    ADD_CAMPAIGN_OPERATOR_OPERATOR,
    REMOVE_CAMPAIGN_OPERATOR_OPERATOR,
    ADD_CAMPAIGN_OPERATOR_CAMPAIGN,
    REMOVE_CAMPAIGN_OPERATOR_CAMPAIGN,
} from "@/actions/project/campaign";

/**
 * Campaign Store state
 */
const state = {};

/**
 * Campaign Store Operators
 */
const actions = {
    /**
     * Fetch campaign operators
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_CAMPAIGN_OPERATORS](context, params) {
        const { data } = await CampaignOperatorService.get(
            context.rootState.project.project.slug,
            params
        );
        context.commit(SET_CAMPAIGN_OPERATORS, data);
        return data;
    },

    /**
     * Add campaign operator
     *
     * @param {*} context
     * @param {Object} params campaign operator values
     * @returns campaign
     */
    async [ADD_CAMPAIGN_OPERATOR](context, params) {
        const { data } = await CampaignOperatorService.create(
            context.rootState.project.project.slug,
            params
        );
        context.commit(ADD_CAMPAIGN_OPERATOR, data);
        return data;
    },

    /**
     * update campaign
     *
     * @param {*} context
     * @param {Object} params new campaign operator values
     */
    [UPDATE_CAMPAIGN_OPERATOR](context, params) {
        context.commit(UPDATE_CAMPAIGN_OPERATOR, params);
        CampaignOperatorService.update(
            context.rootState.project.project.slug,
            params.id,
            params
        );
    },

    /**
     * remove campaign operator
     *
     * @param {*} context
     * @param {Number} params campaign operator id
     * @returns campaign
     */
    async [REMOVE_CAMPAIGN_OPERATOR](context, slug) {
        await CampaignOperatorService.destroy(
            context.rootState.project.project.slug,
            slug
        );
        context.commit(REMOVE_CAMPAIGN_OPERATOR, slug);
    },

    /**
     * Add operator campaign
     *
     * @param {*} state
     * @param {Object} campaign
     */
    async [ADD_CAMPAIGN_OPERATOR_OPERATOR](context, { child, parent }) {
        context.commit(ADD_CAMPAIGN_OPERATOR_OPERATOR, { child, parent });
        await CampaignOperatorService.addOperator(
            context.rootState.project.project.slug,
            parent.id,
            child.id
        );
    },

    /**
     * Remove operator campaign
     *
     * @param {*} state
     * @param {Object} campaign
     */
    async [REMOVE_CAMPAIGN_OPERATOR_OPERATOR](context, { child, parent }) {
        context.commit(REMOVE_CAMPAIGN_OPERATOR_OPERATOR, {
            child,
            parent,
        });
        await CampaignOperatorService.removeOperator(
            context.rootState.project.project.slug,
            parent.id,
            child.id
        );
    },

    /**
     * Add operator campaign
     *
     * @param {*} state
     * @param {Object} campaign
     */
    async [ADD_CAMPAIGN_OPERATOR_CAMPAIGN](context, { operator, campaign }) {
        context.commit(ADD_CAMPAIGN_OPERATOR_CAMPAIGN, { operator, campaign });
        await CampaignService.addOperator(
            context.rootState.project.project.slug,
            campaign.id,
            operator.id
        );
    },

    /**
     * Remove operator campaign
     *
     * @param {*} state
     * @param {Object} campaign
     */
    async [REMOVE_CAMPAIGN_OPERATOR_CAMPAIGN](context, { operator, campaign }) {
        context.commit(REMOVE_CAMPAIGN_OPERATOR_CAMPAIGN, {
            operator,
            campaign,
        });
        await CampaignService.removeOperator(
            context.rootState.project.project.slug,
            campaign.id,
            operator.id
        );
    },
};

/**
 * Campaign Store Mutations
 */
const mutations = {
    /**
     * Set operators
     *
     * @param {*} state
     * @returns
     */
    [SET_CAMPAIGN_OPERATORS](state, operators) {
        state.project.campaign_operators = [...operators];
    },

    /**
     * Set current operator
     *
     * @param {*} state
     * @param {Object} campaign
     */
    [SET_CAMPAIGN_OPERATOR](state, campaignOperator) {
        state.project.campaignOperator = campaignOperator;
    },

    /**
     * Add operator
     *
     * @param {*} state
     * @param {Number} campaign to append to campaigns list
     */
    [ADD_CAMPAIGN_OPERATOR](state, operator) {
        state.project.campaign_operators = [
            ...state.project.campaign_operators,
            operator,
        ];
    },

    /**
     * Update operator
     *
     * @param {*} state
     */
    [UPDATE_CAMPAIGN_OPERATOR](state, params) {
        state.project.campaign_operators = state.project.campaign_operators.map(
            (o) => (o.id == params.id ? { ...o, ...params } : o)
        );
    },

    /**
     * remove operator
     *
     * @param {*} context
     * @param {Number} params operator id
     */
    [REMOVE_CAMPAIGN_OPERATOR](state, slug) {
        state.project.campaign_operators =
            state.project.campaign_operators.filter((o) => o.id != slug);

        state.project.campaign_rules = state.project.campaign_rules.map(
            (rule) => {
                if (
                    rule.operators &&
                    rule.operators.find((operator) => operator.id == slug)
                ) {
                    rule.operators = rule.operators.filter(
                        (operator) => operator.id != slug
                    );
                }

                return rule;
            }
        );

        state.project.campaign_operators = state.project.campaign_operators.map(
            (operator) => {
                if (
                    operator.parent_operators &&
                    operator.parent_operators.find(
                        (operator) => operator.id == slug
                    )
                ) {
                    operator.parent_operators =
                        operator.parent_operators.filter(
                            (operator) => operator.id != slug
                        );
                }

                return operator;
            }
        );
    },

    /**
     * Add operator operator
     *
     * @param {*} state
     * @param {Object} campaign
     */
    [ADD_CAMPAIGN_OPERATOR_OPERATOR](state, { child, parent }) {
        const o = state.project.campaign_operators.find(
            (o) => o.id == child.id
        );
        if (o) {
            o.parent_operators = [
                ...(o.parent_operators ? o.parent_operators : []),
                parent,
            ];
        }
    },

    /**
     * Remove operator operator
     *
     * @param {*} state
     * @param {Object} campaign
     */
    [REMOVE_CAMPAIGN_OPERATOR_OPERATOR](state, { child, parent }) {
        const o = state.project.campaign_operators.find(
            (o) => o.id == child.id
        );
        if (o) {
            o.parent_operators = o.parent_operators.filter(
                (c) => c.id != parent.id
            );
        }
    },

    /**
     * Add operator campaign
     *
     * @param {*} state
     * @param {Object} campaign
     */
    [ADD_CAMPAIGN_OPERATOR_CAMPAIGN](state, { operator, campaign }) {
        const o = state.project.campaign_operators.find(
            (o) => o.id == operator.id
        );
        if (o) {
            o.campaigns = [...(o.campaigns ? o.campaigns : []), campaign];
        }
    },

    /**
     * Remove operator campaign
     *
     * @param {*} state
     * @param {Object} campaign
     */
    [REMOVE_CAMPAIGN_OPERATOR_CAMPAIGN](state, { operator, campaign }) {
        const o = state.project.campaign_operators.find(
            (o) => o.id == operator.id
        );
        if (o) {
            o.campaigns = o.campaigns.filter((c) => c.id != campaign.id);
        }
    },
};

/**
 * Campaign Store Getters
 */
const getters = {
    /**
     * Get list of operators
     *
     * @param {*} state
     * @returns
     */
    campaignOperators: (state) => {
        return state.project &&
            state.project.campaign_operators &&
            Array.isArray(state.project.campaign_operators)
            ? state.project.campaign_operators
            : [];
    },

    /**
     * Get current operator
     *
     * @param {*} state
     * @returns
     */
    campaignOperator: (state) =>
        state.project && state.project.campaignOperator
            ? state.project.campaignOperator
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
