import UserService from "@/apis/project/user";

import {
    FETCH_USERS,
    SET_USERS,
    SET_USER,
    ADD_USER,
    SHOW_USER,
    UPDATE_USER,
    REMOVE_USER,
    UPDATE_SELECTED_USERS,
    TOGGLE_SELECTED_USERS,
    SET_USERS_PAGE,
    SET_USERS_COUNT,
    SET_USERS_SORT_BY,
    SET_USERS_SORT_ORDER,
    SET_USERS_FIELDS,
    SET_USER_PARAMS,
    ADD_USER_PARAMS,
    REMOVE_USER_PARAMS,
    INIT_USER_PARAMS,
    BULK_REMOVE_USER,
    TOGGLE_USERS_OPTIONS,
    UPDATE_USER_BULK_CALENDARS,
    UPDATE_USER_BULK_CATEGORIES,
    UPDATE_USER_BULK_DOCUMENTS,
    UPDATE_USER_BULK_FOLDERS,
    UPDATE_USER_BULK_GROUPS,
    UPDATE_USER_BULK_MENUS,
    UPDATE_USER_BULK_ORDER_ACTIONS,
    UPDATE_USER_BULK_ORDER_STEPS,
    UPDATE_USER_BULK_ROLES,
    UPDATE_USER_BULK_THREADS,
    UPDATE_USER_BULK_USERS,
} from "@/actions/project/user";

import actionStore from "@/store/project/user/action";
import calendarStore from "@/store/project/user/calendar";
import categoryStore from "@/store/project/user/category";
import documentStore from "@/store/project/user/document";
import fileStore from "@/store/project/user/file";
import folderStore from "@/store/project/user/folder";
import groupStore from "@/store/project/user/group";
import labelStore from "@/store/project/user/label";
import menuStore from "@/store/project/user/menu";
import orderActionStore from "@/store/project/user/order-action";
import orderStepStore from "@/store/project/user/order-step";
import productStore from "@/store/project/user/product";
import permissionStore from "@/store/project/user/permission";
import questionnaireStore from "@/store/project/user/questionnaire";
import roleStore from "@/store/project/user/role";
import threadStore from "@/store/project/user/thread";
import userStore from "@/store/project/user/user";
import vehicleStore from "@/store/project/user/vehicle";

/**
 * User Store state
 */
export const state = {
    users: [],
    usersSelected: [],
    usersSelectedIndex: null,
    usersPage: 1,
    usersCount: 50,
    usersSortBy: null,
    usersSortOrder: "desc",
    usersFields: null,
    usersOptions: true,
    usersMenus: true,
    usersParams: {},
    userBulkCalendars: [],
    userBulkCategories: [],
    userBulkDocuments: [],
    userBulkFolders: [],
    userBulkMenus: [],
    userBulkOrderActions: [],
    userBulkOrderSteps: [],
    userBulkRoles: [],
    userBulkThreads: [],
    userBulkUsers: [],
    user: {
        ...actionStore.state,
        ...calendarStore.state,
        ...categoryStore.state,
        ...documentStore.state,
        ...fileStore.state,
        ...folderStore.state,
        ...groupStore.state,
        ...labelStore.state,
        ...menuStore.state,
        ...orderActionStore.state,
        ...orderStepStore.state,
        ...productStore.state,
        ...permissionStore.state,
        ...questionnaireStore.state,
        ...roleStore.state,
        ...threadStore.state,
        ...userStore.state,
        ...vehicleStore.state,
    },
};

/**
 * user Store Actions
 */
const actions = {
    /**
     * Fetch users
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_USERS](context) {
        const params = {
            page: context.state.project.usersPage
                ? context.state.project.usersPage
                : 1,
            count: context.state.project.usersCount
                ? context.state.project.usersCount
                : 50,
        };

        if (
            context.state.project.usersParams &&
            Object.keys(context.state.project.usersParams).length > 0
        ) {
            params.filters = JSON.stringify(
                context.state.project.usersParams
                    ? context.state.project.usersParams
                    : {}
            );
        }

        if (context.state.project.usersSortBy) {
            params.sortBy = context.state.project.usersSortBy;
        }

        if (context.state.project.usersSortOrder) {
            params.sortOrder = context.state.project.usersSortOrder;
        }

        if (context.state.project.usersFields) {
            params.fields = context.state.project.usersFields;
        }

        const { data } = await UserService.get(context.state.project.slug, {
            params: params,
        });
        context.commit(SET_USERS, data);
        return data;
    },

    /**
     * Show user
     *
     * @param {*} context
     * @param {Number} slug user id
     * @returns user
     */
    async [SHOW_USER](context, slug) {
        const { data } = await UserService.show(
            context.state.project.slug,
            slug
        );
        context.commit(SET_USER, data);
        return data;
    },

    /**
     * Update user
     *
     * @param {*} context
     * @param {Object} params new thread thread values
     */
    async [UPDATE_USER](context, params) {
        context.commit(UPDATE_USER, params);
        await UserService.update(context.state.project.slug, params.id, params);
    },

    /**
     * Add user
     *
     * @param {*} context
     * @param {Object} params user user values
     * @returns user
     */
    async [ADD_USER](context, params) {
        const { data } = await UserService.create(
            context.state.project.slug,
            params
        );
        context.commit(ADD_USER, data);
        return data;
    },

    /**
     * remove user
     *
     * @param {*} context
     * @param {Number} params user id
     * @returns user
     */
    async [REMOVE_USER](context, slug) {
        await UserService.destroy(context.state.project.slug, slug);
        context.commit(REMOVE_USER, slug);
    },

    /**
     * Bulk remove user
     *
     * @param {*} context
     * @param {Number} params user id
     * @returns user
     */
    async [BULK_REMOVE_USER](context, users) {
        context.commit(BULK_REMOVE_USER, users);
        await UserService.bulkDestroy(context.state.project.slug, users);
    },

    ...actionStore.actions,
    ...calendarStore.actions,
    ...categoryStore.actions,
    ...documentStore.actions,
    ...folderStore.actions,
    ...fileStore.actions,
    ...groupStore.actions,
    ...labelStore.actions,
    ...menuStore.actions,
    ...orderActionStore.actions,
    ...orderStepStore.actions,
    ...productStore.actions,
    ...permissionStore.actions,
    ...questionnaireStore.actions,
    ...roleStore.actions,
    ...threadStore.actions,
    ...userStore.actions,
    ...vehicleStore.actions,
};

/**
 * user Store Mutations
 */
const mutations = {
    /**
     * Set users
     *
     * @param {*} state
     * @returns
     */
    [SET_USERS](state, users) {
        state.project.users = [...users];
    },

    /**
     * Set current user
     *
     * @param {*} state
     * @param {Object} user
     */
    [SET_USER](state, user) {
        state.project.user = user;
    },

    /**
     * Update user
     *
     * @param {*} state
     */
    [UPDATE_USER](state, params) {
        if (state.project.user && state.project.user.id == params.id) {
            state.project.user = { ...state.project.user, ...params };
        }

        state.project.users = state.project.users.map((o) =>
            o.id == params.id ? { ...o, ...params } : o
        );
    },

    /**
     * Add user
     *
     * @param {*} state
     * @param {Number} user to append to users list
     */
    [ADD_USER](state, user) {
        state.project.users = [...(state.project.users ?? []), user];
    },

    /**
     * remove user
     *
     * @param {*} context
     * @param {Number} params user id
     */
    [REMOVE_USER](state, slug) {
        state.project.users = state.project.users.filter((o) => o.id != slug);
    },

    /**
     * Update list of selected users
     *
     * @param {*} state
     */
    [UPDATE_SELECTED_USERS](state, value) {
        state.project.usersSelected = value;
    },

    /**
     * Toggle list of selected users
     *
     * @param {*} state
     */
    [TOGGLE_SELECTED_USERS](state, { index, shift, checked }) {
        const usersSelectedIndex = state.project.usersSelectedIndex;
        state.project.usersSelectedIndex = index;

        if (
            usersSelectedIndex === null ||
            !shift ||
            index === usersSelectedIndex
        ) {
            return;
        }

        const subset = state.project.users
            .slice(
                Math.min(index, usersSelectedIndex + 1),
                Math.max(index, usersSelectedIndex - 1) + 1
            )
            .map((user) => user.id);

        state.project.usersSelected = checked
            ? [
                  ...state.project.usersSelected,
                  ...subset.filter(
                      (id) => state.project.usersSelected.indexOf(id) == -1
                  ),
              ]
            : state.project.usersSelected.filter(
                  (id) => subset.indexOf(id) == -1
              );
    },

    /**
     * @param {*} state
     */
    [SET_USERS_PAGE](state, page) {
        state.project.usersPage = page;
    },

    /**
     * @param {*} state
     */
    [SET_USERS_COUNT](state, count) {
        state.project.usersCount = count;
    },

    /**
     * @param {*} state
     */
    [SET_USERS_SORT_BY](state, column) {
        state.project.usersSortBy = column;
    },

    /**
     * @param {*} state
     */
    [SET_USERS_SORT_ORDER](state, order) {
        state.project.usersSortOrder = order;
    },

    /**
     * @param {*} state
     */
    [SET_USERS_FIELDS](state, fields) {
        state.project.usersFields = fields;
    },

    /**
     * @param {*} state
     */
    [SET_USER_PARAMS](state, value) {
        if (typeof value == "object") {
            state.project.usersParams = value;
        }
    },

    /**
     * @param {*} state
     */
    [ADD_USER_PARAMS](state, { key, value, multiple }) {
        state.project.usersPage = 1;

        if (!state.project.usersParams) {
            state.project.usersParams = {};
        }

        if (value == undefined) {
            value = "";
        }

        if (multiple) {
            if (state.project.usersParams[key] === undefined) {
                state.project.usersParams[key] = [];
            }

            let values = "" + value;
            if (state.project.usersParams[key] !== "") {
                values = [...state.project.usersParams[key], value];
            }

            state.project.usersParams = Object.fromEntries(
                Object.entries(state.project.usersParams).map(([k, v]) => [
                    k,
                    k == key ? values : v,
                ])
            );
        } else {
            state.project.usersParams = {
                ...state.project.usersParams,
                [key]: value,
            };
        }
    },

    /**
     * @param {*} state
     */
    [REMOVE_USER_PARAMS](state, { key, value }) {
        state.project.usersPage = 1;

        if (!state.project.usersParams) {
            state.project.usersParams = {};
        }

        if (value !== undefined) {
            if (state.project.usersParams[key]) {
                state.project.usersParams = Object.fromEntries(
                    Object.entries(state.project.usersParams).map(([k, v]) => [
                        k,
                        k == key
                            ? state.project.usersParams[k].filter(
                                  (val) => val != value
                              )
                            : v,
                    ])
                );

                if (state.project.usersParams[key].length == 0) {
                    state.project.usersParams = Object.fromEntries(
                        Object.entries(state.project.usersParams).filter(
                            ([k]) => k != key
                        )
                    );
                }
            }
        } else {
            state.project.usersParams = Object.fromEntries(
                Object.entries(state.project.usersParams).filter(
                    ([k]) => k != key
                )
            );
        }
    },

    /**
     * @param {*} state
     */
    [INIT_USER_PARAMS](state) {
        state.project.usersPage = 1;

        state.project.usersSortOrder = null;
        state.project.usersSortBy = null;
        state.project.usersParams = {}; /*Object.fromEntries(
            Object.entries(state.project.usersParams).filter(([k]) => k == "count")
        );*/
    },

    /**
     * Update list of users bulk calendars
     *
     * @param {*} state
     */
    [UPDATE_USER_BULK_CALENDARS](state, value) {
        state.project.userBulkCalendars = value;
    },

    /**
     * Update list of users bulk categories
     *
     * @param {*} state
     */
    [UPDATE_USER_BULK_CATEGORIES](state, value) {
        state.project.userBulkCategories = value;
    },

    /**
     * Update list of users bulk documents
     *
     * @param {*} state
     */
    [UPDATE_USER_BULK_DOCUMENTS](state, value) {
        state.project.userBulkDocuments = value;
    },

    /**
     * Update list of users bulk folders
     *
     * @param {*} state
     */
    [UPDATE_USER_BULK_FOLDERS](state, value) {
        state.project.userBulkFolders = value;
    },

    /**
     * Update list of users bulk groups
     *
     * @param {*} state
     */
    [UPDATE_USER_BULK_GROUPS](state, value) {
        state.project.userBulkGroups = value;
    },

    /**
     * Update list of users bulk menus
     *
     * @param {*} state
     */
    [UPDATE_USER_BULK_MENUS](state, value) {
        state.project.userBulkMenus = value;
    },

    /**
     * Update list of users bulk order actions
     *
     * @param {*} state
     */
    [UPDATE_USER_BULK_ORDER_ACTIONS](state, value) {
        state.project.userBulkOrderActions = value;
    },

    /**
     * Update list of users bulk order steps
     *
     * @param {*} state
     */
    [UPDATE_USER_BULK_ORDER_STEPS](state, value) {
        state.project.userBulkOrderSteps = value;
    },

    /**
     * Update list of users bulk roles
     *
     * @param {*} state
     */
    [UPDATE_USER_BULK_ROLES](state, value) {
        state.project.userBulkRoles = value;
    },

    /**
     * Update list of users bulk threads
     *
     * @param {*} state
     */
    [UPDATE_USER_BULK_THREADS](state, value) {
        state.project.userBulkThreads = value;
    },

    /**
     * Update list of users bulk users
     *
     * @param {*} state
     */
    [UPDATE_USER_BULK_USERS](state, value) {
        state.project.userBulkUsers = value;
    },

    /**
     * Update list of users bulk processed
     *
     * @param {*} state
     */
    [BULK_REMOVE_USER](state, users) {
        state.project.users = state.project.users.map((user) =>
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
    [TOGGLE_USERS_OPTIONS](state) {
        state.project.usersOptions =
            state.project.usersOptions !== undefined
                ? !state.project.usersOptions
                : false;
    },

    ...actionStore.mutations,
    ...calendarStore.mutations,
    ...categoryStore.mutations,
    ...documentStore.mutations,
    ...fileStore.mutations,
    ...folderStore.mutations,
    ...groupStore.mutations,
    ...labelStore.mutations,
    ...menuStore.mutations,
    ...orderActionStore.mutations,
    ...orderStepStore.mutations,
    ...productStore.mutations,
    ...permissionStore.mutations,
    ...questionnaireStore.mutations,
    ...roleStore.mutations,
    ...threadStore.mutations,
    ...userStore.mutations,
    ...vehicleStore.mutations,
};

/**
 * user Store Getters
 */
const getters = {
    /**
     * Get list of users
     *
     * @param {*} state
     * @returns
     */
    users: (state) =>
        state.project && state.project.users ? state.project.users : [],

    /**
     * Get list of selected users
     *
     * @param {*} state
     * @returns
     */
    usersSelected: (state) =>
        state.project && state.project.usersSelected
            ? state.project.usersSelected
            : [],

    /**
     * @param {*} state
     * @returns
     */
    usersParams: (state) =>
        state.project && state.project.usersParams
            ? state.project.usersParams
            : {},

    /**
     * @param {*} state
     * @returns
     */
    usersParamsUrl: (state) =>
        state.project &&
        state.project.usersParams &&
        Object.keys(state.project.usersParams).length > 0
            ? encodeURI(JSON.stringify(state.project.usersParams))
            : "",

    /**
     * @param {*} state
     * @returns
     */
    usersParamValue: (state) => (param) =>
        state.project && state.project.usersParams
            ? state.project.usersParams[param]
            : undefined,

    /**
     * @param {*} state
     * @returns
     */
    usersParamExists: (state) => (param, value) => {
        if (!state.project || !state.project.usersParams) {
            return false;
        }

        if (param === undefined) {
            return Object.keys(state.project.usersParams).length > 0;
        }

        if (value === undefined) {
            return state.project.usersParams[param] !== undefined;
        }

        return (
            state.project.usersParams[param] !== undefined &&
            state.project.usersParams[param].findIndex((p) => p == value) >= 0
        );
    },

    /**
     * @param {*} state
     * @returns
     */
    usersParamCount: (state) => (param) => {
        if (
            !state.project ||
            !state.project.usersParams ||
            state.project.usersParams[param] === undefined
        ) {
            return 0;
        }

        if (!Array.isArray(state.project.usersParams[param])) {
            return 1;
        }

        return state.project.usersParams[param].length;
    },

    /**
     * @param {*} state
     * @returns
     */
    userBulkCalendars: (state) =>
        state.project && Array.isArray(state.project.userBulkCalendars)
            ? state.project.userBulkCalendars
            : [],

    /**
     * @param {*} state
     * @returns
     */
    userBulkCategories: (state) =>
        state.project && Array.isArray(state.project.userBulkCategories)
            ? state.project.userBulkCategories
            : [],

    /**
     * @param {*} state
     * @returns
     */
    userBulkDocuments: (state) =>
        state.project && Array.isArray(state.project.userBulkDocuments)
            ? state.project.userBulkDocuments
            : [],

    /**
     * @param {*} state
     * @returns
     */
    userBulkFolders: (state) =>
        state.project && Array.isArray(state.project.userBulkFolders)
            ? state.project.userBulkFolders
            : [],

    /**
     * @param {*} state
     * @returns
     */
    userBulkGroups: (state) =>
        state.project && Array.isArray(state.project.userBulkGroups)
            ? state.project.userBulkGroups
            : [],

    /**
     * @param {*} state
     * @returns
     */
    userBulkMenus: (state) =>
        state.project && Array.isArray(state.project.userBulkMenus)
            ? state.project.userBulkMenus
            : [],

    /**
     * @param {*} state
     * @returns
     */
    userBulkOrderActions: (state) =>
        state.project && Array.isArray(state.project.userBulkOrderActions)
            ? state.project.userBulkOrderActions
            : [],

    /**
     * @param {*} state
     * @returns
     */
    userBulkOrderSteps: (state) =>
        state.project && Array.isArray(state.project.userBulkOrderSteps)
            ? state.project.userBulkOrderSteps
            : [],

    /**
     * @param {*} state
     * @returns
     */
    userBulkRoles: (state) =>
        state.project && Array.isArray(state.project.userBulkRoles)
            ? state.project.userBulkRoles
            : [],

    /**
     * @param {*} state
     * @returns
     */
    userBulkThreads: (state) =>
        state.project && Array.isArray(state.project.userBulkThreads)
            ? state.project.userBulkThreads
            : [],

    /**
     * @param {*} state
     * @returns
     */
    userBulkUsers: (state) =>
        state.project && Array.isArray(state.project.userBulkUsers)
            ? state.project.userBulkUsers
            : [],

    /**
     * Get users page
     *
     * @param {*} state
     * @returns
     */
    usersPage: (state) => (state.project ? state.project.usersPage : 0),

    /**
     * Get users count pagination
     *
     * @param {*} state
     * @returns
     */
    usersCount: (state) =>
        state.project && state.project.usersCount
            ? state.project.usersCount
            : 50,

    /**
     * Get users sort by
     *
     * @param {*} state
     * @returns
     */
    usersSortBy: (state) => (state.project ? state.project.usersSortBy : null),

    /**
     * Get users sort order
     *
     * @param {*} state
     * @returns
     */
    usersSortOrder: (state) =>
        state.project ? state.project.usersSortOrder : "desc",

    /**
     * Get users count pagination
     *
     * @param {*} state
     * @returns
     */
    usersFields: (state) =>
        state.project && state.project.usersFields
            ? state.project.usersFields
            : null,

    /**
     * Get users options
     *
     * @param {*} state
     * @returns
     */
    usersOptions: (state) =>
        state.project && state.project.usersOptions !== undefined
            ? state.project.usersOptions
            : true,

    /**
     * Get users menus
     *
     * @param {*} state
     * @returns
     */
    usersMenus: (state) => (state.project ? state.project.usersMenus : false),

    /**
     * Get current user
     *
     * @param {*} state
     * @returns
     */
    user: (state) =>
        state.project && state.project.user ? state.project.user : null,

    /**
     *
     * @param {*} state
     * @returns
     */
    userFullName: (state) =>
        state.project && state.project.user
            ? [
                  state.project.user.name,
                  // state.project.user.last_name,
              ]
                  .filter((n) => n)
                  .join(" ")
            : "",

    ...actionStore.getters,
    ...calendarStore.getters,
    ...categoryStore.getters,
    ...documentStore.getters,
    ...fileStore.getters,
    ...folderStore.getters,
    ...groupStore.getters,
    ...labelStore.getters,
    ...menuStore.getters,
    ...orderActionStore.getters,
    ...orderStepStore.getters,
    ...productStore.getters,
    ...permissionStore.getters,
    ...questionnaireStore.getters,
    ...roleStore.getters,
    ...threadStore.getters,
    ...userStore.getters,
    ...vehicleStore.getters,
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
