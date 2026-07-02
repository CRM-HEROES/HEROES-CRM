import menuService from "@/apis/project/menu";

import {
    FETCH_MENUS,
    SET_MENUS,
    SET_MENU,
    ADD_MENU,
    SHOW_MENU,
    UPDATE_MENU,
    REMOVE_MENU,
} from "@/actions/project/menu";

/**
 * menu Store Actions
 */
const actions = {
    /**
     * Fetch menus
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_MENUS](context, params) {
        const { data } = await menuService.get(
            context.state.project.slug,
            params
        );
        context.commit(SET_MENUS, data);
        return data;
    },

    /**
     * Show menu
     *
     * @param {*} context
     * @param {Number} slug menu id
     * @returns menu
     */
    async [SHOW_MENU](context, slug) {
        const { data } = await menuService.show(
            context.state.project.slug,
            slug
        );
        // context.commit(SET_MENU, data);
        return data;
    },

    /**
     * Add menu
     *
     * @param {*} context
     * @param {Object} params menu menu values
     * @returns menu
     */
    async [ADD_MENU](context, params) {
        const { data } = await menuService.create(
            context.state.project.slug,
            params
        );
        context.commit(ADD_MENU, data);
        return data;
    },

    /**
     * update menu
     *
     * @param {*} context
     * @param {Object} params new menu menu values
     */
    async [UPDATE_MENU](context, params) {
        await menuService.update(context.state.project.slug, params.id, params);
        context.commit(UPDATE_MENU, params);
    },

    /**
     * remove menu
     *
     * @param {*} context
     * @param {Number} params menu id
     * @returns menu
     */
    async [REMOVE_MENU](context, slug) {
        await menuService.destroy(context.state.project.slug, slug);
        context.commit(REMOVE_MENU, slug);
    },
};

/**
 * menu Store Mutations
 */
const mutations = {
    /**
     * Set menus
     *
     * @param {*} state
     * @returns
     */
    [SET_MENUS](state, menus) {
        state.project.menus = [...menus];
    },

    /**
     * Set current menu
     *
     * @param {*} state
     * @param {Object} menu
     */
    [SET_MENU](state, menu) {
        state.project.menu = menu;
    },

    /**
     * Add menu
     *
     * @param {*} state
     * @param {Number} menu to append to menus list
     */
    [ADD_MENU](state, menu) {
        state.project.menus = [...(state.project.menus ?? []), menu];
    },

    /**
     * Update menu
     *
     * @param {*} state
     */
    [UPDATE_MENU](state, params) {
        state.project.menus = state.project.menus.map((o) =>
            o.id == params.id ? { ...o, ...params } : o
        );
    },

    /**
     * remove menu
     *
     * @param {*} context
     * @param {Number} params menu id
     */
    [REMOVE_MENU](state, slug) {
        state.project.menus = state.project.menus.filter((o) => o.id != slug);
    },
};

/**
 * menu Store Getters
 */
const getters = {
    /**
     * Get list of menus
     *
     * @param {*} state
     * @returns
     */
    menus: (state) =>
        state.project && Array.isArray(state.project.menus)
            ? state.project.menus
            : [],

    /**
     * Get current menu
     *
     * @param {*} state
     * @returns
     */
    menu: (state) =>
        state.project && state.project.menu ? state.project.menu : null,

    /**
     * Get default menu
     *
     * @param {*} state
     * @returns
     */
    defaultMenu: (state) =>
        state.project && Array.isArray(state.project.menus)
            ? state.project.menus.find(
                  (menu) =>
                      Array.isArray(menu.users) &&
                      menu.users.find((user) => user.pivot.default)
              )
            : null,
};

/**
 * Store
 */
export default {
    actions,
    mutations,
    getters,
};
