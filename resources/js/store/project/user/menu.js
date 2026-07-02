import UserMenuService from "@/apis/project/user/menu";

import {
    FETCH_USER_MENUS,
    SET_USER_MENUS,
    ADD_USER_MENU,
    UPDATE_USER_MENU,
    REMOVE_USER_MENU,
    BULK_ADD_USER_MENU,
    BULK_REMOVE_USER_MENU,
} from "@/actions/project/user/menu";

/**
 * UserMenu Store state
 */
export const state = {
    menus: [],
};

/**
 * UserMenu Store Actions
 */
export const actions = {
    /**
     * Fetch user menus
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_USER_MENUS](context, params) {
        const { data } = await UserMenuService.get(
            context.state.project.slug,
            context.state.project.user.id,
            params
        );
        context.commit(SET_USER_MENUS, data);
        return data;
    },

    /**
     * Add user menu
     *
     * @param {*} context
     * @param {Object} params
     * @returns userMenu
     */
    async [ADD_USER_MENU](context, menu) {
        context.commit(ADD_USER_MENU, menu);
        await UserMenuService.update(
            context.state.project.slug,
            context.state.project.user.id,
            menu.id,
            {}
        );
    },

    /**
     * Update user menu
     *
     * @param {*} context
     * @param {Object} params
     * @returns userMenu
     */
    async [UPDATE_USER_MENU](context, { menu, params }) {
        context.commit(UPDATE_USER_MENU, { menu, params });
        await UserMenuService.update(
            context.state.project.slug,
            context.state.project.user.id,
            menu.id,
            params
        );
    },

    /**
     * Remove user menu
     *
     * @param {*} context
     * @param {Number} params user menu id
     * @returns userMenu
     */
    async [REMOVE_USER_MENU](context, menu) {
        context.commit(REMOVE_USER_MENU, menu);
        await UserMenuService.destroy(
            context.state.project.slug,
            context.state.project.user.id,
            menu.id
        );
    },

    /**
     * Add user menu
     *
     * @param {*} context
     * @param {Object} params
     * @returns userMenu
     */
    async [BULK_ADD_USER_MENU](context, { users, menus }) {
        await UserMenuService.bulkUpdate(
            context.state.project.slug,
            users,
            menus
        );
    },

    /**
     * Remove user menu
     *
     * @param {*} context
     * @param {Number} params user menu id
     * @returns userMenu
     */
    async [BULK_REMOVE_USER_MENU](context, { users, menus }) {
        await UserMenuService.bulkDestroy(
            context.state.project.slug,
            users,
            menus
        );
    },
};

/**
 * User menu Store Mutations
 */
export const mutations = {
    /**
     * Set user menus
     *
     * @param {*} state
     * @returns
     */
    [SET_USER_MENUS](state, menus) {
        state.project.user.menus = menus;
    },

    /**
     * Add user menu
     *
     * @param {*} state
     * @param {Number} menu to append to user menus list
     */
    [ADD_USER_MENU](state, menu) {
        state.project.user.menus = [
            ...(state.project.user.menus ? state.project.user.menus : []),
            menu,
        ];
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },

    /**
     * Update user menu
     *
     * @param {*} state
     * @param {Number} menu to append to user menus list
     */
    [UPDATE_USER_MENU](state, { menu, params }) {
        state.project.menus = state.project.menus.map((m) =>
            m.id == menu.id && Array.isArray(m.users)
                ? {
                      ...m,
                      users: m.users.map((u) => ({
                          ...u,
                          pivot: { ...u.pivot, ...params },
                      })),
                  }
                : params && params.default
                ? {
                      ...m,
                      users: m.users.map((u) => ({
                          ...u,
                          pivot: { ...u.pivot, default: false },
                      })),
                  }
                : m
        );
    },

    /**
     * Remove user menu
     *
     * @param {*} context
     * @param {Number} params user menu id
     */
    [REMOVE_USER_MENU](state, menu) {
        state.project.user.menus = (
            state.project.user.menus ? state.project.user.menus : []
        ).filter((o) => o.id != menu.id);
        state.project.users = state.project.users.map((o) =>
            o.id == state.project.user.id ? { ...o, ...state.project.user } : o
        );
    },
};

/**
 * User menu Store Getters
 */
export const getters = {
    /**
     * Get list of user menus
     *
     * @param {*} state
     * @returns
     */
    userMenus: (state) =>
        state.project &&
        state.project.user &&
        state.project.user.menus &&
        Array.isArray(state.project.user.menus)
            ? state.project.user.menus
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
