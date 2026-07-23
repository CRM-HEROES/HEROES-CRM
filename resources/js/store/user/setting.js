import SettingService from "@/apis/user/setting";

import {
    ADD_USER_SETTING,
    GET_USER_SETTING,
    UPDATE_USER_SETTING,
    REMOVE_USER_SETTING,
} from "@/actions/user/setting";

/**
 * Setting Store state
 */
export const state = {
    userSettings: {},
};

/**
 * Setting Store Actions
 */
const actions = {
    /**
     * Show setting
     *
     * @param {*} context
     * @param {Number} slug setting id
     * @returns setting
     */
    async [GET_USER_SETTING](context, key) {
        const userId = context.rootState?.auth?.user?.id;

        if (!userId) {
            return null;
        }

        const { data } = await SettingService.show(userId, key);
        const value = data;
        context.commit(ADD_USER_SETTING, { key, value });
        return data;
    },

    /**
     * update setting
     *
     * @param {*} context
     * @param {Object} params new setting field values
     */
    async [UPDATE_USER_SETTING](context, { key, value }) {
        const userId = context.rootState?.auth?.user?.id;

        if (!userId) {
            return null;
        }

        await SettingService.update(userId, key, value);
        context.commit(ADD_USER_SETTING, { key, value });
    },

    /**
     * remove setting
     *
     * @param {*} context
     * @param {Number} params setting id
     * @returns setting
     */
    async [REMOVE_USER_SETTING](context, key) {
        const userId = context.rootState?.auth?.user?.id;

        if (!userId) {
            return null;
        }

        await SettingService.destroy(userId, key);
        context.commit(REMOVE_USER_SETTING, key);
    },
};

/**
 * Setting Store Mutations
 */
const mutations = {
    /**
     * Update setting
     *
     * @param {*} state
     */
    [ADD_USER_SETTING](state, { key, value }) {
        state.userSettings = {
            ...state.userSettings,
            [key]: value,
        };
    },

    /**
     * Update setting
     *
     * @param {*} state
     */
    [UPDATE_USER_SETTING](state, { key, value }) {
        state.userSettings = {
            ...state.userSettings,
            [key]: value,
        };
    },

    /**
     * remove setting
     *
     * @param {*} context
     * @param {Number} params setting id
     */
    [REMOVE_USER_SETTING](state, key) {
        state.userSettings = Object.fromEntries(
            Object.entries(state.userSettings).filter(([k]) => k != key)
        );
    },
};

/**
 * Setting Store Getters
 */
const getters = {
    /**
     * Get list of settings
     *
     * @param {*} state
     * @returns
     */
    userSettings(state) {
        return state.userSettings ? state.userSettings : {};
    },

    /**
     * Get prospects table setting
     *
     * @param {*} state
     * @returns
     */
    userSettingsProjectsTable(state) {
        return state.userSettings && state.userSettings["projects-table"]
            ? state.userSettings["projects-table"]
            : [];
    },

    /**
     * Get list of settings
     *
     * @param {*} state
     * @returns
     */
    userSettingsProjectsTableHas: (state) => (column) => {
        return (
            state.userSettings &&
            state.userSettings["projects-table"] &&
            state.userSettings["projects-table"].findIndex(
                (us) => us.key == column
            ) >= 0
        );
    },

    /**
     * Get prospects table setting
     *
     * @param {*} state
     * @returns
     */
    userSettingsUsersTable(state) {
        return state.userSettings && state.userSettings["global-users-table"]
            ? state.userSettings["global-users-table"]
            : [
                  {
                      key: "name",
                  },
                  {
                      key: "last_name",
                  },
                  {
                      key: "email",
                  },
                  {
                      key: "street",
                  },
                  {
                      key: "street_bis",
                  },
                  {
                      key: "postal_code",
                  },
                  {
                      key: "city",
                  },
                  {
                      key: "country",
                  },
                  {
                      key: "projects",
                  },
                  {
                      key: "ip_address",
                  },
                  {
                      key: "last_activity",
                  },
                  {
                      key: "last-project",
                  },
              ];
    },

    /**
     * Get list of settings
     *
     * @param {*} state
     * @returns
     */
    userSettingsUsersTableHas: (state) => (column) => {
        return (
            state.userSettings &&
            state.userSettings["global-users-table"] &&
            state.userSettings["global-users-table"].findIndex(
                (us) => us.key == column
            ) >= 0
        );
    },
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
