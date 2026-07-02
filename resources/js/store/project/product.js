import productService from "@/apis/project/product";

import {
    FETCH_PRODUCTS,
    SET_PRODUCTS,
    SET_PRODUCT,
    ADD_PRODUCT,
    SHOW_PRODUCT,
    UPDATE_PRODUCT,
    REMOVE_PRODUCT,
    ADD_PRODUCT_IMAGE,
    UPDATE_PRODUCT_IMAGE,
    REMOVE_PRODUCT_IMAGE,
} from "@/actions/project/product";

/**
 * product Store state
 */
export const state = {
    products: [],
    product: null,
};

/**
 * product Store Actions
 */
const actions = {
    /**
     * Fetch products
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PRODUCTS](context, params) {
        const { data } = await productService.get(
            context.state.project.slug,
            params
        );
        context.commit(SET_PRODUCTS, data);
        return data;
    },

    /**
     * Show product
     *
     * @param {*} context
     * @param {Number} slug product id
     * @returns product
     */
    async [SHOW_PRODUCT](context, slug) {
        const { data } = await productService.show(
            context.state.project.slug,
            slug
        );
        context.commit(SET_PRODUCT, data);
        return data;
    },

    /**
     * Add product
     *
     * @param {*} context
     * @param {Object} params product product values
     * @returns product
     */
    async [ADD_PRODUCT](context, params) {
        const { data } = await productService.create(
            context.state.project.slug,
            params
        );
        context.commit(ADD_PRODUCT, data);
        return data;
    },

    /**
     * update product
     *
     * @param {*} context
     * @param {Object} params new product product values
     */
    async [UPDATE_PRODUCT](context, params) {
        await productService.update(
            context.state.project.slug,
            params.id,
            params
        );
        context.commit(UPDATE_PRODUCT, params);
    },

    /**
     * remove product
     *
     * @param {*} context
     * @param {Number} params product id
     * @returns product
     */
    async [REMOVE_PRODUCT](context, slug) {
        await productService.destroy(context.state.project.slug, slug);
        context.commit(REMOVE_PRODUCT, slug);
    },

    /**
     * add product image
     *
     * @param {*} context
     * @param {Object} params new product product values
     */
    async [ADD_PRODUCT_IMAGE](context, { product, params, settings }) {
        const { data } = await productService.addImage(
            context.state.project.slug,
            product.id,
            params,
            settings
        );
        context.commit(ADD_PRODUCT_IMAGE, { product, data });
    },

    /**
     * Update product image
     *
     * @param {*} context
     * @param {Object} params new product product values
     */
    async [UPDATE_PRODUCT_IMAGE](context, params) {
        await productService.updateImage(
            context.state.project.slug,
            params.product_id,
            params.id,
            params
        );
        context.commit(UPDATE_PRODUCT_IMAGE, params);
    },

    /**
     * remove product image
     *
     * @param {*} context
     * @param {Object} params new product product values
     */
    async [REMOVE_PRODUCT_IMAGE](context, { product, image }) {
        await productService.removeImage(
            context.state.project.slug,
            product.id,
            image.id
        );
        context.commit(REMOVE_PRODUCT_IMAGE, { product, image });
    },
};

/**
 * product Store Mutations
 */
const mutations = {
    /**
     * Set products
     *
     * @param {*} state
     * @returns
     */
    [SET_PRODUCTS](state, products) {
        state.project.products = [...products];
    },

    /**
     * Set current product
     *
     * @param {*} state
     * @param {Object} product
     */
    [SET_PRODUCT](state, product) {
        state.project.product = product;
    },

    /**
     * Add product
     *
     * @param {*} state
     * @param {Number} product to append to products list
     */
    [ADD_PRODUCT](state, product) {
        state.project.products = [
            ...(state.project.products ? state.project.products : []),
            product,
        ];
    },

    /**
     * Update product
     *
     * @param {*} state
     */
    [UPDATE_PRODUCT](state, params) {
        state.project.products = state.project.products.map((o) =>
            o.id == params.id ? { ...o, ...params } : o
        );
    },

    /**
     * remove product
     *
     * @param {*} context
     * @param {Number} slug product id
     */
    [REMOVE_PRODUCT](state, slug) {
        state.project.products = state.project.products.filter(
            (o) => o.id != slug
        );
    },

    /**
     * Add product image
     *
     * @param {*} state
     */
    [ADD_PRODUCT_IMAGE](state, { product, data }) {
        const p = state.project.products.find((p) => p.id == product.id);

        if (p) {
            p.images = [...p.images, data];
        }
    },

    /**
     * Update product image
     *
     * @param {*} state
     */
    [UPDATE_PRODUCT_IMAGE](state, image) {
        const p = state.project.products.find((p) => p.id == image.product_id);

        if (p) {
            p.images = p.images.map((i) =>
                i.id == image.id ? { ...i, ...image } : i
            );
        }
    },

    /**
     * Remove product image
     *
     * @param {*} state
     */
    [REMOVE_PRODUCT_IMAGE](state, { product, image }) {
        const p = state.project.products.find((p) => p.id == product.id);

        if (p) {
            p.images = p.images.filter((i) => i.id != image.id);
        }
    },
};

/**
 * product Store Getters
 */
const getters = {
    /**
     * Get list of products
     *
     * @param {*} state
     * @returns
     */
    products: (state) =>
        state.project && state.project.products ? state.project.products : [],

    /**
     * Get current product
     *
     * @param {*} state
     * @returns
     */
    product: (state) =>
        state.project && state.project.product ? state.project.product : null,
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
