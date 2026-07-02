import UserService from "@/apis/user";

import projectStore from "./user/project";
import sessionStore from "./user/session";
import settingStore from "./user/setting";

import {
    FETCH_GLOBAL_USERS,
    SET_GLOBAL_USERS,
    ADD_GLOBAL_USER,
    UPDATE_GLOBAL_USER,
    SHOW_GLOBAL_USER,
    SET_GLOBAL_USER,
    UPDATE_GLOBAL_SELECTED_USERS,
    TOGGLE_GLOBAL_SELECTED_USERS,
    SET_GLOBAL_USERS_PAGE,
    SET_GLOBAL_USERS_COUNT,
    SET_GLOBAL_USERS_SORT_BY,
    SET_GLOBAL_USERS_SORT_ORDER,
    SET_GLOBAL_USERS_FIELDS,
    SET_GLOBAL_USER_PARAMS,
    ADD_GLOBAL_USER_PARAMS,
    REMOVE_GLOBAL_USER_PARAMS,
    INIT_GLOBAL_USER_PARAMS,
    BULK_GLOBAL_REMOVE_USER,
    TOGGLE_GLOBAL_USERS_OPTIONS,
} from "@/actions/user";

/**
 * Project Store state
 */
export const state = {
    globalUser: null,
    globalUsers: [],
    globalUsersSelected: [],
    globalUsersSelectedIndex: null,
    globalUsersPage: 1,
    globalUsersCount: 50,
    globalUsersSortBy: null,
    globalUsersSortOrder: "desc",
    globalUsersFields: null,
    globalUsersOptions: false,
    globalUsersMenus: true,
    globalUsersParams: {},
};

/**
 * Project Store Actions
 */
export const actions = {
    /**
     * Fetch users
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_GLOBAL_USERS](context) {
        const params = {
            page: context.state.globalUsersPage
                ? context.state.globalUsersPage
                : 1,
            count: context.state.globalUsersCount
                ? context.state.globalUsersCount
                : 50,
        };

        if (
            context.state.globalUsersParams &&
            Object.keys(context.state.globalUsersParams).length > 0
        ) {
            params.filters = JSON.stringify(
                context.state.globalUsersParams
                    ? context.state.globalUsersParams
                    : {}
            );
        }

        if (context.state.globalUsersSortBy) {
            params.sortBy = context.state.globalUsersSortBy;
        }

        if (context.state.globalUsersSortOrder) {
            params.sortOrder = context.state.globalUsersSortOrder;
        }

        if (context.state.globalUsersFields) {
            params.fields = context.state.globalUsersFields;
        }

        const { data } = await UserService.get({
            params: params,
        });
        context.commit(SET_GLOBAL_USERS, data.data);
        return data;
    },

    /**
     * Add user
     *
     * @param {*} context
     * @returns
     */
    async [ADD_GLOBAL_USER](context, params) {
        const { data } = await UserService.create(params);
        context.commit(ADD_GLOBAL_USER, data);
        return data;
    },

    /**
     * Update user
     *
     * @param {*} context
     * @returns
     */
    async [UPDATE_GLOBAL_USER](context, params) {
        context.commit(UPDATE_GLOBAL_USER, params);
        const { data } = await UserService.update(params.id, params);
        return data;
    },

    /**
     * Show user
     *
     * @param {*} context
     * @returns
     */
    async [SHOW_GLOBAL_USER](context, slug) {
        const { data } = await UserService.show(slug);
        context.commit(SET_GLOBAL_USER, data);
        return data;
    },

    ...projectStore.actions,
    ...sessionStore.actions,
    ...settingStore.actions,
};

/**
 * Project Store Mutations
 */
export const mutations = {
    /**
     * Set users
     *
     * @param {*} state
     * @returns
     */
    [SET_GLOBAL_USERS](state, users) {
        state.globalUsers = [...users];
    },

    /**
     * Add global user
     *
     * @param {*} state
     * @param {Object} smsTemplate
     */
    [ADD_GLOBAL_USER](state, user) {
        state.globalUsers = [user, ...state.globalUsers];
    },

    /**
     * Update global user
     *
     * @param {*} state
     * @param {Object} smsTemplate
     */
    [UPDATE_GLOBAL_USER](state, user) {
        state.globalUsers = state.globalUsers.map((u) =>
            u.id == user.id ? { ...u, ...user } : u
        );

        if (state.globalUser && state.globalUser.id == user.id) {
            state.globalUser = {
                ...state.globalUser,
                ...user,
            };
        }

        if (state.user && state.user.id == user.id) {
            state.globalUser = {
                ...state.globalUser,
                ...user,
            };
        }
    },

    /**
     * Show global user
     *
     * @param {*} state
     * @param {Object} smsTemplate
     */
    [SET_GLOBAL_USER](state, user) {
        state.globalUser = user;
    },

    /**
     * Update list of selected users
     *
     * @param {*} state
     */
    [UPDATE_GLOBAL_SELECTED_USERS](state, value) {
        state.globalUsersSelected = value;
    },

    /**
     * Toggle list of selected users
     *
     * @param {*} state
     */
    [TOGGLE_GLOBAL_SELECTED_USERS](state, { index, shift, checked }) {
        const usersSelectedIndex = state.globalUsersSelectedIndex;
        state.globalUsersSelectedIndex = index;

        if (
            usersSelectedIndex === null ||
            !shift ||
            index === usersSelectedIndex
        ) {
            return;
        }

        const subset = state.globalUsers
            .slice(
                Math.min(index, usersSelectedIndex + 1),
                Math.max(index, usersSelectedIndex - 1) + 1
            )
            .map((user) => user.id);

        state.globalUsersSelected = checked
            ? [
                  ...state.globalUsersSelected,
                  ...subset.filter(
                      (id) => state.globalUsersSelected.indexOf(id) == -1
                  ),
              ]
            : state.globalUsersSelected.filter(
                  (id) => subset.indexOf(id) == -1
              );
    },

    /**
     * @param {*} state
     */
    [SET_GLOBAL_USERS_PAGE](state, page) {
        state.globalUsersPage = page;
    },

    /**
     * @param {*} state
     */
    [SET_GLOBAL_USERS_COUNT](state, count) {
        state.globalUsersCount = count;
    },

    /**
     * @param {*} state
     */
    [SET_GLOBAL_USERS_SORT_BY](state, column) {
        state.globalUsersSortBy = column;
    },

    /**
     * @param {*} state
     */
    [SET_GLOBAL_USERS_SORT_ORDER](state, order) {
        state.globalUsersSortOrder = order;
    },

    /**
     * @param {*} state
     */
    [SET_GLOBAL_USERS_FIELDS](state, fields) {
        state.globalUsersFields = fields;
    },

    /**
     * @param {*} state
     */
    [SET_GLOBAL_USER_PARAMS](state, value) {
        if (typeof value == "object") {
            state.globalUsersParams = value;
        }
    },

    /**
     * @param {*} state
     */
    [ADD_GLOBAL_USER_PARAMS](state, { key, value, multiple }) {
        state.globalUsersPage = 1;

        if (!state.globalUsersParams) {
            state.globalUsersParams = {};
        }

        if (value == undefined) {
            value = "";
        }

        if (multiple) {
            if (state.globalUsersParams[key] === undefined) {
                state.globalUsersParams[key] = [];
            }

            let values = "" + value;
            if (state.globalUsersParams[key] !== "") {
                values = [...state.globalUsersParams[key], value];
            }

            state.globalUsersParams = Object.fromEntries(
                Object.entries(state.globalUsersParams).map(([k, v]) => [
                    k,
                    k == key ? values : v,
                ])
            );
        } else {
            state.globalUsersParams = {
                ...state.globalUsersParams,
                [key]: value,
            };
        }
    },

    /**
     * @param {*} state
     */
    [REMOVE_GLOBAL_USER_PARAMS](state, { key, value }) {
        state.globalUsersPage = 1;

        if (!state.globalUsersParams) {
            state.globalUsersParams = {};
        }

        if (value !== undefined) {
            if (state.globalUsersParams[key]) {
                state.globalUsersParams = Object.fromEntries(
                    Object.entries(state.globalUsersParams).map(([k, v]) => [
                        k,
                        k == key
                            ? state.globalUsersParams[k].filter(
                                  (val) => val != value
                              )
                            : v,
                    ])
                );

                if (state.globalUsersParams[key].length == 0) {
                    state.globalUsersParams = Object.fromEntries(
                        Object.entries(state.globalUsersParams).filter(
                            ([k]) => k != key
                        )
                    );
                }
            }
        } else {
            state.globalUsersParams = Object.fromEntries(
                Object.entries(state.globalUsersParams).filter(
                    ([k]) => k != key
                )
            );
        }
    },

    /**
     * @param {*} state
     */
    [INIT_GLOBAL_USER_PARAMS](state) {
        state.globalUsersPage = 1;

        state.globalUsersSortOrder = null;
        state.globalUsersSortBy = null;
        state.globalUsersParams = {}; /*Object.fromEntries(
            Object.entries(state.globalUsersParams).filter(([k]) => k == "count")
        );*/
    },

    /**
     * Update list of users bulk processed
     *
     * @param {*} state
     */
    [BULK_GLOBAL_REMOVE_USER](state, users) {
        state.globalUsers = state.globalUsers.map((user) =>
            users.indexOf(user.id) >= 0
                ? { ...user, deleted_at: new Date() }
                : user
        );
    },

    /**
     * Toggle show users table options
     *
     * @param {*} state
     */
    [TOGGLE_GLOBAL_USERS_OPTIONS](state) {
        state.globalUsersOptions = !state.globalUsersOptions;
    },

    ...projectStore.mutations,
    ...sessionStore.mutations,
    ...settingStore.mutations,
};

/**
 * Project Store Getters
 */
const getters = {
    /**
     * Get user
     *
     * @param {*} state
     * @returns
     */
    globalUser: (state) => (state.globalUser ? state.globalUser : null),

    /**
     * Get list of users
     *
     * @param {*} state
     * @returns
     */
    globalUsers: (state) => (state.globalUsers ? state.globalUsers : []),

    /**
     * Get list of selected users
     *
     * @param {*} state
     * @returns
     */
    globalUsersSelected: (state) =>
        state && state.globalUsersSelected ? state.globalUsersSelected : [],

    /**
     * @param {*} state
     * @returns
     */
    globalUsersParams: (state) =>
        state && state.globalUsersParams ? state.globalUsersParams : {},

    /**
     * @param {*} state
     * @returns
     */
    globalUsersParamsUrl: (state) =>
        state &&
        state.globalUsersParams &&
        Object.keys(state.globalUsersParams).length > 0
            ? encodeURI(JSON.stringify(state.globalUsersParams))
            : "",

    /**
     * @param {*} state
     * @returns
     */
    globalUsersParamValue: (state) => (param) =>
        state && state.globalUsersParams
            ? state.globalUsersParams[param]
            : undefined,

    /**
     * @param {*} state
     * @returns
     */
    globalUsersParamExists: (state) => (param, value) => {
        if (!state.globalUsersParams) {
            return false;
        }

        if (param === undefined) {
            return Object.keys(state.globalUsersParams).length > 0;
        }

        if (value === undefined) {
            return state.globalUsersParams[param] !== undefined;
        }

        return (
            state.globalUsersParams[param] !== undefined &&
            state.globalUsersParams[param].findIndex((p) => p == value) >= 0
        );
    },

    /**
     * @param {*} state
     * @returns
     */
    globalUsersParamCount: (state) => (param) => {
        if (
            !state.globalUsersParams ||
            state.globalUsersParams[param] === undefined
        ) {
            return 0;
        }

        if (!Array.isArray(state.globalUsersParams[param])) {
            return 1;
        }

        return state.globalUsersParams[param].length;
    },

    /**
     * Get users sort by
     *
     * @param {*} state
     * @returns
     */
    globalUsersSortBy: (state) => (state.globalUsersSortBy ? state.globalUsersSortBy : null),

    /**
     * Get users sort order
     *
     * @param {*} state
     * @returns
     */
    globalUsersSortOrder: (state) => state.globalUsersSortOrder,

    /**
     * Get users page
     *
     * @param {*} state
     * @returns
     */
    globalUsersPage: (state) => state.globalUsersPage,

    /**
     * Get users count pagination
     *
     * @param {*} state
     * @returns
     */
    globalUsersCount: (state) =>
        state.globalUsersCount ? state.globalUsersCount : 50,

    /**
     * Get users options
     *
     * @param {*} state
     * @returns
     */
    globalUsersOptions: (state) => state.globalUsersOptions,

    /**
     * Get users menus
     *
     * @param {*} state
     * @returns
     */
    globalUsersMenus: (state) => state.globalUsersMenus,

    ...projectStore.getters,
    ...sessionStore.getters,
    ...settingStore.getters,
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
