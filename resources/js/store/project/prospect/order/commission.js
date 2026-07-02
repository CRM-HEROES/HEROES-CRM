import CommissionService from "@/apis/project/user/commission";

import {
    FETCH_PROSPECT_ORDER_COMMISSIONS,
    SET_PROSPECT_ORDER_COMMISSIONS,
} from "@/actions/project/prospect/order/commission";

/**
 * OrderAction Store state
 */
export const state = {
    commissions: [],
};

/**
 */
export const actions = {
    /**
     * Fetch order commission
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PROSPECT_ORDER_COMMISSIONS](context) {
        const { data } = await CommissionService.get(
            context.state.project.slug,
            {
                params: {
                    order: context.state.project.prospect.order.id,
                },
            }
        );
        context.commit(SET_PROSPECT_ORDER_COMMISSIONS, data);
        return data;
    },
};

/**
 * Order action Store Mutations
 */
export const mutations = {
    /**
     * Set order actions
     *
     * @param {*} state
     * @returns
     */
    [SET_PROSPECT_ORDER_COMMISSIONS](state, commissions) {
        state.project.prospect.order.commissions = commissions;
    },
};

/**
 * Order action Store Getters
 */
export const getters = {
    /**
     * Get list of order commissions
     *
     * @param {*} state
     * @returns
     */
    prospectOrderCommissions: (state) =>
        state.project &&
        state.project.prospect &&
        state.project.prospect.order &&
        state.project.prospect.order.commissions
            ? state.project.prospect.order.commissions
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
