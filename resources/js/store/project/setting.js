import SettingService from "@/apis/project/setting";

import {
    ADD_SETTING,
    GET_SETTING,
    UPDATE_SETTING,
    REMOVE_SETTING,
} from "@/actions/project/setting";

/**
 * Setting Store state
 */
export const state = {
    settings: [],
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
    async [GET_SETTING](context, key) {
        const { data } = await SettingService.show(
            context.state.project.slug,
            key
        );
        const value = data;
        context.commit(ADD_SETTING, { key, value });
        return data;
    },

    /**
     * update setting
     *
     * @param {*} context
     * @param {Object} params new setting field values
     */
    async [UPDATE_SETTING](context, { key, value }) {
        context.commit(UPDATE_SETTING, { key, value });
        await SettingService.update(context.state.project.slug, key, value);
    },

    /**
     * remove setting
     *
     * @param {*} context
     * @param {Number} params setting id
     * @returns setting
     */
    async [REMOVE_SETTING](context, key) {
        await SettingService.destroy(context.state.project.slug, key);
        context.commit(REMOVE_SETTING, key);
    },
};

/**
 * Setting Store Mutations
 */
const mutations = {
    /**
     * Add setting
     *
     * @param {*} state
     */
    [ADD_SETTING](state, { key, value }) {
        state.project.settings = {
            ...state.project.settings,
            [key]: value,
        };
    },

    /**
     * Update setting
     *
     * @param {*} state
     */
    [UPDATE_SETTING](state, { key, value }) {
        if (state.project.settings[key]) {
            state.project.settings = Object.fromEntries(
                Object.entries(state.project.settings).map(([k, v]) => [
                    k,
                    k == key ? value : v,
                ])
            );
        } else {
            state.project.settings = {
                ...state.project.settings,
                [key]: value,
            };
        }
    },

    /**
     * remove setting
     *
     * @param {*} context
     * @param {Number} params setting id
     */
    [REMOVE_SETTING](state, key) {
        state.project.settings = Object.fromEntries(
            Object.entries(state.project.settings).filter(([k]) => k != key)
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
    settings(state) {
        return state.project.settings ? state.project.settings : [];
    },

    /**
     * Get list of settings
     *
     * @param {*} state
     * @returns
     */
    settingsHas: (state) => (setting) => {
        return state.project.settings && state.project.settings[setting];
    },

    /**
     * Get list of settings
     *
     * @param {*} state
     * @returns
     */
    settingsGet: (state) => (setting) => {
        return state.project.settings
            ? state.project.settings[setting]
            : undefined;
    },

    /**
     * Get stat map setting
     *
     * @param {*} state
     * @returns
     */
    settingsStatMap(state) {
        return state.project.settings &&
            state.project.settings["stat-map"] &&
            typeof state.project.settings["stat-map"] === "object"
            ? state.project.settings["stat-map"]
            : {};
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
