import CommissionService from "@/apis/project/user/commission";

import {
    FETCH_COMMISSIONS,
    SET_COMMISSIONS,
    UPDATE_COMMISSION,
    REMOVE_COMMISSION,
    BULK_UPDATE_COMMISSION,
} from "@/actions/project/user/commission";

/**
 * Commission Store state
 */
export const state = {};

/**
 * Commission Store Actions
 */
const actions = {
    /**
     * Fetch commissions
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_COMMISSIONS](context, params) {
        const { data } = await CommissionService.get(
            context.state.project.slug,
            params
        );
        context.commit(SET_COMMISSIONS, data);
        return data;
    },

    /**
     * update commission
     *
     * @param {*} context
     */
    async [UPDATE_COMMISSION](context, { user, product, action, params }) {
        await CommissionService.update(
            context.state.project.slug,
            user.id,
            product.id,
            action.id,
            params
        );
    },

    /**
     * remove commission
     *
     * @param {*} context
     */
    async [REMOVE_COMMISSION](context, { user, product, action }) {
        await CommissionService.destroy(
            context.state.project.slug,
            user.id,
            product.id,
            action.id
        );
    },

    /**
     * Bulk update commission
     *
     * @param {*} context
     */
    async [BULK_UPDATE_COMMISSION](context, params) {
        await CommissionService.bulkUpdate(context.state.project.slug, params);
    },
};

/**
 * Commission Store Mutations
 */
const mutations = {
    /**
     * Set commissions
     *
     * @param {*} state
     * @returns
     */
    [SET_COMMISSIONS](state, commissions) {
        state.project.commissions = [...commissions];
    },
};

/**
 * Commission Store Getters
 */
const getters = {
    /**
     * Get list of commissions
     *
     * @param {*} state
     * @returns
     */
    commissions: (state) =>
        state.project &&
        state.project.commissions &&
        Array.isArray(state.project.commissions)
            ? state.project.commissions
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
