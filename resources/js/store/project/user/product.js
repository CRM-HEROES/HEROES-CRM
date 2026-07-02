import UserProductService from "@/apis/project/user/product";

import {
    FETCH_USER_PRODUCTS,
    SET_USER_PRODUCTS,
    ADD_USER_PRODUCT,
    REMOVE_USER_PRODUCT,
    BULK_ADD_USER_PRODUCT,
    BULK_REMOVE_USER_PRODUCT,
} from "@/actions/project/user/product";

/**
 * UserProduct Store state
 */
export const state = {
    products: [],
};

/**
 * UserProduct Store Actions
 */
export const actions = {
    /**
     * Fetch user products
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_USER_PRODUCTS](context, params) {
        const { data } = await UserProductService.get(
            context.state.project.slug,
            context.state.project.user.id,
            params
        );
        context.commit(SET_USER_PRODUCTS, data);
        return data;
    },

    /**
     * Add user product
     *
     * @param {*} context
     * @param {Object} params
     * @returns userProduct
     */
    async [ADD_USER_PRODUCT](context, product) {
        context.commit(ADD_USER_PRODUCT, product);
        await UserProductService.update(
            context.state.project.slug,
            context.state.project.user.id,
            product.id
        );
    },

    /**
     * Remove user product
     *
     * @param {*} context
     * @param {Number} params user product id
     * @returns userProduct
     */
    async [REMOVE_USER_PRODUCT](context, product) {
        context.commit(REMOVE_USER_PRODUCT, product);
        await UserProductService.destroy(
            context.state.project.slug,
            context.state.project.user.id,
            product.id
        );
    },

    /**
     * Add user product
     *
     * @param {*} context
     * @param {Object} params
     * @returns userProduct
     */
    async [BULK_ADD_USER_PRODUCT](context, { users, products }) {
        await UserProductService.bulkUpdate(
            context.state.project.slug,
            users,
            products
        );
    },

    /**
     * Remove user product
     *
     * @param {*} context
     * @param {Number} params user product id
     * @returns userProduct
     */
    async [BULK_REMOVE_USER_PRODUCT](context, { users, products }) {
        await UserProductService.bulkDestroy(
            context.state.project.slug,
            users,
            products
        );
    },
};

/**
 * User product Store Mutations
 */
export const mutations = {
    /**
     * Set user products
     *
     * @param {*} state
     * @returns
     */
    [SET_USER_PRODUCTS](state, products) {
        state.project.user.products = products;
    },

    /**
     * Add user product
     *
     * @param {*} state
     * @param {Number} product to append to user products list
     */
    [ADD_USER_PRODUCT](state, product) {
        state.project.user.products = [
            ...(state.project.user.products ? state.project.user.products : []),
            product,
        ];
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },

    /**
     * Remove user product
     *
     * @param {*} context
     * @param {Number} params user product id
     */
    [REMOVE_USER_PRODUCT](state, product) {
        state.project.user.products = (
            state.project.user.products ? state.project.user.products : []
        ).filter((o) => o.id != product.id);
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },
};

/**
 * User product Store Getters
 */
export const getters = {
    /**
     * Get list of user products
     *
     * @param {*} state
     * @returns
     */
    userProducts: (state) =>
        state.project &&
        state.project.user &&
        state.project.user.products &&
        Array.isArray(state.project.user.products)
            ? state.project.user.products
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
