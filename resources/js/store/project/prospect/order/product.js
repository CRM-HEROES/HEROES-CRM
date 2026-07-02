import OrderProductService from "@/apis/project/prospect/order/product";

import {
    FETCH_ORDER_PRODUCTS,
    SET_ORDER_PRODUCTS,
    ADD_ORDER_PRODUCT,
    REMOVE_ORDER_PRODUCT,
} from "@/actions/project/prospect/order/product";

/**
 * OrderProduct Store state
 */
export const state = {
    products: [],
};

/**
 * OrderProduct Store Products
 */
export const actions = {
    /**
     * Fetch order products
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_ORDER_PRODUCTS](context, params) {
        const { data } = await OrderProductService.get(
            context.state.project.slug,
            context.state.project.prospect.id,
            context.state.project.prospect.order.id,
            params
        );
        context.commit(SET_ORDER_PRODUCTS, data);
        return data;
    },

    /**
     * Add order product
     *
     * @param {*} context
     * @param {Object} params
     * @returns orderProduct
     */
    async [ADD_ORDER_PRODUCT](context, product) {
        context.commit(ADD_ORDER_PRODUCT, product);
        await OrderProductService.update(
            context.state.project.slug,
            context.state.project.prospect.id,
            context.state.project.prospect.order.id,
            product.id,
            product
        );
    },

    /**
     * Remove order product
     *
     * @param {*} context
     * @param {Number} params order product id
     * @returns orderProduct
     */
    async [REMOVE_ORDER_PRODUCT](context, slug) {
        context.commit(REMOVE_ORDER_PRODUCT, slug);
        await OrderProductService.destroy(
            context.state.project.slug,
            context.state.project.prospect.id,
            context.state.project.prospect.order.id,
            slug
        );
    },
};

/**
 * Order product Store Mutations
 */
export const mutations = {
    /**
     * Set order products
     *
     * @param {*} state
     * @returns
     */
    [SET_ORDER_PRODUCTS](state, products) {
        state.project.prospect.order = {
            ...state.project.prospect.order,
            products: [...products],
        };
    },

    /**
     * Add order product
     *
     * @param {*} state
     * @param {Number} product to append to order products list
     */
    [ADD_ORDER_PRODUCT](state, product) {
        state.project.prospect.order = {
            ...state.project.prospect.order,
            products: [
                ...(state.project.prospect.order.products
                    ? state.project.prospect.order.products
                    : []
                ).filter((p) => p.id != product.id),
                {
                    ...state.project.products.find((p) => p.id == product.id),
                    pivot: { ...product },
                },
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
     * Remove order product
     *
     * @param {*} context
     * @param {Number} params order product id
     */
    [REMOVE_ORDER_PRODUCT](state, slug) {
        state.project.prospect.order = {
            ...state.project.prospect.order,
            products: (state.project.prospect.order.products
                ? state.project.prospect.order.products
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
 * Order product Store Getters
 */
export const getters = {
    /**
     * Get list of order products
     *
     * @param {*} state
     * @returns
     */
    orderProducts: (state) =>
        state.project &&
        state.project.prospect &&
        state.project.prospect.order &&
        state.project.prospect.order.products
            ? state.project.prospect.order.products
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
