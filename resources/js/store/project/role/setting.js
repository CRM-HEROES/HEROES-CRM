import SettingService from "@/apis/project/role/setting";

import {
    ADD_ROLE_SETTING,
    GET_ROLE_SETTING,
    UPDATE_ROLE_SETTING,
    REMOVE_ROLE_SETTING,
} from "@/actions/project/role/setting";

/**
 * Setting Store state
 */
export const state = {
    roleSettings: [],
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
    async [GET_ROLE_SETTING](context, key) {
        const { data } = await SettingService.show(
            context.state.project.slug,
            context.rootState.auth.role.id,
            key
        );
        const value = data;
        context.commit(ADD_ROLE_SETTING, { key, value });
        return data;
    },

    /**
     * update setting
     *
     * @param {*} context
     * @param {Object} params new setting field values
     */
    async [UPDATE_ROLE_SETTING](context, { key, value }) {
        context.commit(UPDATE_ROLE_SETTING, { key, value });
        await SettingService.update(
            context.state.project.slug,
            context.rootState.auth.role.id,
            key,
            value
        );
    },

    /**
     * remove setting
     *
     * @param {*} context
     * @param {Number} params setting id
     * @returns setting
     */
    async [REMOVE_ROLE_SETTING](context, key) {
        await SettingService.destroy(
            context.state.project.slug,
            context.rootState.auth.role.id,
            key
        );
        context.commit(REMOVE_ROLE_SETTING, key);
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
    [ADD_ROLE_SETTING](state, { key, value }) {
        state.project.roleSettings = {
            ...state.project.roleSettings,
            [key]: value,
        };
    },

    /**
     * Update setting
     *
     * @param {*} state
     */
    [UPDATE_ROLE_SETTING](state, { key, value }) {
        state.project.roleSettings = Object.fromEntries(
            Object.entries(state.project.roleSettings).map(([k, v]) => [
                k,
                k == key ? value : v,
            ])
        );
    },

    /**
     * remove setting
     *
     * @param {*} context
     * @param {Number} params setting id
     */
    [REMOVE_ROLE_SETTING](state, key) {
        state.project.roleSettings = Object.fromEntries(
            Object.entries(state.project.roleSettings).filter(([k]) => k != key)
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
    roleSettings(state) {
        return state.project.roleSettings ? state.project.roleSettings : [];
    },

    /**
     * Get list of settings
     *
     * @param {*} state
     * @returns
     */
    roleSettingsProspectsTable(state) {
        return state.project.roleSettings &&
            state.project.roleSettings["prospects-table"]
            ? state.project.roleSettings["prospects-table"]
            : [];
    },

    /**
     * Get list of settings
     *
     * @param {*} state
     * @returns
     */
    roleSettingsProspectsTableHas: (state) => (column) => {
        return (
            state.project.roleSettings &&
            state.project.roleSettings["prospects-table"] &&
            state.project.roleSettings["prospects-table"].findIndex(
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
