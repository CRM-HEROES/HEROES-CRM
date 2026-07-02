import OrderActionService from "@/apis/project/prospect/order/action";

import {
    FETCH_PROSPECT_ORDER_ACTIONS,
    SET_PROSPECT_ORDER_ACTIONS,
    ADD_PROSPECT_ORDER_ACTION,
    REMOVE_PROSPECT_ORDER_ACTION,
} from "@/actions/project/prospect/order/action";

/**
 * OrderAction Store state
 */
export const state = {
    actions: [],
};

/**
 * OrderAction Store Actions
 */
export const actions = {
    /**
     * Fetch order actions
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PROSPECT_ORDER_ACTIONS](context, params) {
        const { data } = await OrderActionService.get(
            context.state.project.slug,
            context.state.project.prospect.id,
            context.state.project.prospect.order.id,
            params
        );
        context.commit(SET_PROSPECT_ORDER_ACTIONS, data);
        return data;
    },

    /**
     * Add order action
     *
     * @param {*} context
     * @param {Object} params
     * @returns orderAction
     */
    async [ADD_PROSPECT_ORDER_ACTION](context, { action, user, params }) {
        params = { ...params, user_id: user.id };
        context.commit(ADD_PROSPECT_ORDER_ACTION, { action, user, params });
        await OrderActionService.update(
            context.state.project.slug,
            context.state.project.prospect.id,
            context.state.project.prospect.order.id,
            action.id,
            params
        );
    },

    /**
     * Remove order action
     *
     * @param {*} context
     * @param {Number} params order action id
     * @returns orderAction
     */
    async [REMOVE_PROSPECT_ORDER_ACTION](context, slug) {
        context.commit(REMOVE_PROSPECT_ORDER_ACTION, slug);
        await OrderActionService.destroy(
            context.state.project.slug,
            context.state.project.prospect.id,
            context.state.project.prospect.order.id,
            slug
        );
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
    [SET_PROSPECT_ORDER_ACTIONS](state, actions) {
        state.project.prospect.order = {
            ...state.project.prospect.order,
            actions: [...actions],
        };
    },

    /**
     * Add order action
     *
     * @param {*} state
     * @param {Number} action to append to order actions list
     */
    [ADD_PROSPECT_ORDER_ACTION](state, { action, user, params }) {
        state.project.prospect.order = {
            ...state.project.prospect.order,
            actions: state.project.prospect.order.actions.find(
                (a) => a.id == action.id
            )
                ? state.project.prospect.order.actions.map((a) =>
                      a.id == action.id
                          ? { ...a, pivot: { ...params, user: user } }
                          : a
                  )
                : [
                      ...state.project.prospect.order.actions,
                      { ...action, pivot: { ...params, user: user } },
                  ],
        };

        state.project.orders = (
            state.project.orders && Array.isArray(state.project.orders)
                ? state.project.orders
                : []
        ).map((o) =>
            o.id == state.project.prospect.order.id
                ? { ...o, ...state.project.prospect.order }
                : o
        );
    },

    /**
     * Remove order action
     *
     * @param {*} context
     * @param {Number} params order action id
     */
    [REMOVE_PROSPECT_ORDER_ACTION](state, slug) {
        state.project.prospect.order = {
            ...state.project.prospect.order,
            actions: (state.project.prospect.order.actions
                ? state.project.prospect.order.actions
                : []
            ).filter((o) => o.id != slug),
        };

        state.project.orders = (
            state.project.orders && Array.isArray(state.project.orders)
                ? state.project.orders
                : []
        ).map((o) =>
            o.id == state.project.prospect.order.id
                ? { ...o, ...state.project.prospect.order }
                : o
        );
    },
};

/**
 * Order action Store Getters
 */
export const getters = {
    /**
     * Get list of order actions
     *
     * @param {*} state
     * @returns
     */
    prospectOrderActions: (state) =>
        state.project &&
        state.project.prospect &&
        state.project.prospect.order &&
        state.project.prospect.order.actions
            ? state.project.prospect.order.actions
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
