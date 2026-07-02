import SettingService from "@/apis/project/user/setting";

import {
    ADD_PROJECT_USER_SETTING,
    GET_PROJECT_USER_SETTING,
    UPDATE_PROJECT_USER_SETTING,
    REMOVE_PROJECT_USER_SETTING,
} from "@/actions/project/user/setting";

import { API_URL } from "@/apis/common";

/**
 * Setting Store state
 */
export const state = {
    user_settings: [],
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
    async [GET_PROJECT_USER_SETTING](context, { user, key }) {
        if (!user) {
            user = context.rootState.auth.user.id;
        }

        const { data } = await SettingService.show(
            context.state.project.slug,
            user,
            key
        );
        const value = data;

        if (context.rootState.auth.user.id == user) {
            context.commit(ADD_PROJECT_USER_SETTING, { key, value });
        }

        return data;
    },

    /**
     * update setting
     *
     * @param {*} context
     * @param {Object} params new setting field values
     */
    [UPDATE_PROJECT_USER_SETTING](context, { user, key, value }) {
        if (!user) {
            user = context.rootState.auth.user.id;
        }

        if (context.rootState.auth.user.id == user) {
            context.commit(UPDATE_PROJECT_USER_SETTING, { key, value });
        }

        SettingService.update(context.state.project.slug, user, key, value);
    },

    /**
     * remove setting
     *
     * @param {*} context
     * @param {Number} params setting id
     * @returns setting
     */
    async [REMOVE_PROJECT_USER_SETTING](context, { user, key }) {
        if (!user) {
            user = context.rootState.auth.user.id;
        }

        if (context.rootState.auth.user.id == user) {
            context.commit(REMOVE_PROJECT_USER_SETTING, key);
        }

        await SettingService.destroy(
            context.state.project.slug,
            context.rootState.auth.user.id,
            key
        );
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
    [ADD_PROJECT_USER_SETTING](state, { key, value }) {
        state.project.user_settings = [
            ...state.project.user_settings.filter((us) => us.key != key),
            {
                key,
                value,
            },
        ];
    },

    /**
     * Update setting
     *
     * @param {*} state
     */
    [UPDATE_PROJECT_USER_SETTING](state, { key, value }) {
        if (state.project.user_settings.find((us) => us.key == key)) {
            state.project.user_settings = state.project.user_settings.map(
                (us) => (us.key == key ? { ...us, value } : us)
            );
        } else {
            state.project.user_settings = [
                ...state.project.user_settings,
                { key, value },
            ];
        }
    },

    /**
     * remove setting
     *
     * @param {*} context
     * @param {Number} params setting id
     */
    [REMOVE_PROJECT_USER_SETTING](state, key) {
        state.project.user_settings = state.project.user_settings.filter(
            (us) => us.key != key
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
    projectUserSettings(state) {
        return !state.project ||
            !state.project.user_settings ||
            !Array.isArray(state.project.user_settings)
            ? []
            : state.project.user_settings;
    },

    /**
     * Get user setting
     *
     * @param {*} state
     * @returns
     */
    projectUserSetting: (state, getters) => (key, defaultValue) => {
        const setting = getters.projectUserSettings.find((s) => s.key == key);

        return setting ? setting.value : defaultValue;
    },

    /**
     * Get prospects table setting
     *
     * @param {*} state
     * @returns
     */
    projectUserSettingsProspectsTable(state, getters) {
        let setting = getters.projectUserSetting("prospects-table");

        if (typeof setting === "object") {
            return Object.values(setting);
        }

        return !setting || !Array.isArray(setting)
            ? [
                  { key: "first_name" },
                  { key: "last_name" },
                  { key: "email" },
                  { key: "phone_number" },
                  { key: "mobile_phone_number" },
                  { key: "street" },
                  { key: "street_bis" },
                  { key: "postal_code" },
                  { key: "city" },
                  { key: "country" },
              ]
            : setting;
    },

    /**
     * Get list of settings
     *
     * @param {*} state
     * @returns
     */
    projectUserSettingsProspectsTableHas: (state, getters) => (column) => {
        let setting = getters.projectUserSettingsProspectsTable;

        return setting && setting.findIndex((us) => us.key == column) >= 0;
    },

    /**
     * Get prospects table menu setting
     *
     * @param {*} state
     * @returns
     */
    projectUserSettingsProspectsTableMenus(state, getters) {
        let setting = getters.projectUserSetting("prospects-table.menus");

        if (typeof setting === "object") {
            return Object.values(setting);
        }

        return !setting || !Array.isArray(setting)
            ? [
                  "view",
                  "file",
                  "message",
                  "order",
                  "interaction",
                  "sms",
                  "questionnaire",
                  "user",
              ]
            : setting.filter((s) => s);
    },

    /**
     * Get prospects table setting
     *
     * @param {*} state
     * @returns
     */
    projectUserSettingsUsersTable(state, getters) {
        let setting = getters.projectUserSetting("users-table");

        if (typeof setting === "object") {
            return Object.values(setting);
        }

        return !setting || !Array.isArray(setting)
            ? [
                  { key: "name" },
                  { key: "last_name" },
                  { key: "email" },
                  { key: "phone_number" },
                  { key: "mobile_phone_number" },
                  { key: "street" },
                  { key: "street_bis" },
                  { key: "postal_code" },
                  { key: "city" },
                  { key: "roles" },
                  { key: "assignable_users" },
                  { key: "calendars" },
                  { key: "categories" },
                  { key: "documents" },
                  { key: "folders" },
                  { key: "groups" },
                  { key: "menus" },
                  { key: "order_actions" },
                  { key: "order_steps" },
                  { key: "questionnaires" },
                  { key: "threads" },
                  { key: "last_activity" },
              ]
            : setting;
    },

    /**
     * Get list of settings
     *
     * @param {*} state
     * @returns
     */
    projectUserSettingsUsersTableHas: (state) => (column) => {
        let setting = getters.projectUserSettingsUsersTable;

        return setting && setting.findIndex((us) => us.key == column) >= 0;
    },

    /**
     * Get prospect profile setting
     *
     * @param {*} state
     * @returns
     */
    projectUserSettingsProspectProfile(state, getters) {
        let setting = getters.projectUserSetting("prospect-profile");

        return !setting || !Array.isArray(setting)
            ? JSON.parse(
                  '[[{"name":"Profil","type":"field","items":["last_name","first_name","company_name","title"]},{"name":"Contact","type":"field","items":["website_url","email","mobile_phone_number","phone_number"]},{"name":"Coordonn\u00e9es","type":"field","items":["street","street_bis","state","county","city","postal_code","country","latitude","longitude"]},{"name":"Liens externes","type":"link"}],[{"name":"RDV","type":"event"},{"name":"Commandes","type":"order"}],[{"name":"SMS","type":"sms"},{"name":"Appel","type":"interaction"},{"name":"Document","type":"document"}],[{"name":"Groupes affect\u00e9s","type":"group"},{"name":"Utilisateurs affect\u00e9s","type":"user"}]]'
              )
            : setting;
    },

    /**
     * Get orders table setting
     *
     * @param {*} state
     * @returns
     */
    projectUserSettingsOrdersTable(state, getters) {
        let setting = getters.projectUserSetting("orders-table");

        return !setting || !Array.isArray(setting)
            ? [
                  { key: "created_at" },
                  { key: "prospect" },
                  { key: "products" },
                  { key: "status" },
                  { key: "steps" },
                  { key: "tax_value" },
                  { key: "total_including_tax" },
                  { key: "total_excluding_tax" },
                  { key: "commissions" },
                  { key: "total_commissions" },
              ]
            : setting;
    },

    /**
     * Get list of settings
     *
     * @param {*} state
     * @returns
     */
    projectUserSettingsOrdersTableHas: (state, getters) => (column) => {
        let setting = getters.projectUserSettingsOrdersTable;

        return setting && setting.findIndex((us) => us.key == column) >= 0;
    },

    /**
     * Get orders table menu setting
     *
     * @param {*} state
     * @returns
     */
    projectUserSettingsOrdersTableMenus(state, getters) {
        let setting = getters.projectUserSetting("orders-table.menus");

        return !setting || !Array.isArray(setting)
            ? ["view", "commission", "document", "event", "interaction", "sms"]
            : setting.filter((s) => s);
    },

    /**
     * Get prospect profile setting
     *
     * @param {*} state
     * @returns
     */
    projectUserSettingsUserProfile(state, getters) {
        let setting = getters.projectUserSetting("user-profile");

        return !setting || !Array.isArray(setting)
            ? JSON.parse(
                  '[[{"name": "Groupes affectés", "type": "group"}, {"name": "Utilisateurs affectés", "type": "user"}, {"name": "Rôles affectés", "type": "role"}], [{"name": "Profil", "type": "field", "items": ["name", "last_name", "password"]}, {"name": "Coordonnées", "type": "field", "items": ["street", "street_bis", "postal_code", "city", "country", "email"]}, {"name": "Documents affectés", "type": "document"}], [{"name": "Catégories de filtre affectées", "type": "category"}, {"name": "Dossiers affectés", "type": "folder"}, {"name": "Canaux de discussion affectés", "type": "thread"}], [{"name": "Calendriers affectés", "type": "calendar"}, {"name": "Questionnaires affectées", "type": "questionnaire"}, {"name": "Etapes de devis affectées", "type": "order-step"}, {"name": "Menus affectés", "type": "menu"}, {"name": "Imports", "type": "import"}, {"name": "Exports", "type": "export"}]]'
              )
            : setting;
    },

    /**
     * Get agenda minimum hour
     *
     * @param {*} state
     * @returns
     */
    projectUserSettingsAgendaMinimumHour(state, getters) {
        let setting = getters.projectUserSetting("events.minimum-hour");

        return isNaN(setting) || setting >= 24 ? 6 : setting;
    },

    /**
     * Get agenda maximum hour
     *
     * @param {*} state
     * @returns
     */
    projectUserSettingsAgendaMaximumHour(state, getters) {
        let setting = getters.projectUserSetting("events.maximum-hour");

        return isNaN(setting) || setting > 24 ? 21 : setting;
    },

    /**
     * Get prospects default menu
     *
     * @param {*} state
     * @returns
     */
    projectUserSettingsProspectsDefaultMenu(state, getters) {
        let setting = getters.projectUserSetting("prospects.default-menu");

        return !setting ? null : setting;
    },

    /**
     * Get events default calendar
     *
     * @param {*} state
     * @returns
     */
    projectUserSettingsEventsDefaultCalendar(state, getters) {
        let setting = getters.projectUserSetting("events.default-calendar");

        return !setting ? null : setting;
    },

    /**
     * Get events default user
     *
     * @param {*} state
     * @returns
     */
    projectUserSettingsEventsDefaultUser(state, getters) {
        let setting = getters.projectUserSetting("events.default-user");

        return !setting ? null : setting;
    },

    /**
     * Get prospects default menu
     *
     * @param {*} state
     * @returns
     */
    projectUserSettingsAgendaUsers(state, getters) {
        let setting = getters.projectUserSetting("events.agenda.filters.users");

        return !setting || !Array.isArray(setting) || setting.length == 0
            ? null
            : setting;
    },

    /**
     * Get prospects table row max lines
     *
     * @param {*} state
     * @returns
     */
    projectUserSettingsProspectsMaxRowLines(state, getters) {
        let setting = getters.projectUserSetting("prospects.max-row-lines");

        return isNaN(setting) ? 1 : setting;
    },

    /**
     * Get prospects default menu
     *
     * @param {*} state
     * @returns
     */
    projectUserSettingsMenuIconUrl: (state, getters) => (key) => {
        let setting = getters.projectUserSetting("menu-icon." + key);

        return !setting
            ? null
            : API_URL +
                  "/project/" +
                  getters.project.slug +
                  "/menu-icon/" +
                  key +
                  "?v=" +
                  setting;
    },

    /**
     * Get events reminder
     *
     * @param {*} state
     * @returns
     */
    projectUserSettingsEventsReminder(state, getters) {
        return getters.projectUserSetting("events.reminder", true);
    },

    /**
     * Get show drop off location
     *
     * @param {*} state
     * @returns
     */
    projectUserSettingsEventsShowDropOffLocation(state, getters) {
        return getters.projectUserSetting(
            "events.show-drop-off-location",
            false
        );
    },

    /**
     * Get show event vehicle
     *
     * @param {*} state
     * @returns
     */
    projectUserSettingsEventsShowVehicle(state, getters) {
        return getters.projectUserSetting("events.show-vehicle", true);
    },

    /**
     * Get show event order
     *
     * @param {*} state
     * @returns
     */
    projectUserSettingsEventsShowOrder(state, getters) {
        return getters.projectUserSetting("events.show-order", true);
    },

    /**
     * Get show event labels
     *
     * @param {*} state
     * @returns
     */
    projectUserSettingsEventsShowLabels(state, getters) {
        return getters.projectUserSetting("events.show-labels", true);
    },

    /**
     * Get event color by label
     *
     * @param {*} state
     * @returns
     */
    projectUserSettingsEventsColorByLabel(state, getters) {
        return getters.projectUserSetting("events.color-by-label", null);
    },

    /**
     * Get stat map setting
     *
     * @param {*} state
     * @returns
     */
    projectUserSettingsStatBlocValue(state, getters) {
        return getters.projectUserSetting("stat-bloc-value", [
            {
                key: "prospects",
                name: "Prospects",
                color: "#FFFFFF",
                bgcolor: "#83c7d4",
            },
            {
                key: "interactions",
                name: "Appels",
                color: "#FFFFFF",
                bgcolor: "#8979ff",
            },
            {
                key: "interactions-duration",
                name: "Dur\u00e9e des appels",
                color: "#FFFFFF",
                bgcolor: "#8979ff",
            },
            {
                key: "orders",
                name: "Devis",
                color: "#FFFFFF",
                bgcolor: "#ff8979",
            },
            {
                key: "orders-total",
                name: "Total des devis",
                color: "#FFFFFF",
                bgcolor: "#ff8979",
            },
            {
                key: "sms",
                name: "SMS",
                color: "#FFFFFF",
                bgcolor: "#83d496",
            },
            {
                key: "events",
                name: "RDVs",
                color: "#FFFFFF",
                bgcolor: "#ffae4c",
            },
            {
                key: "files",
                name: "Fichiers",
                color: "#FFFFFF",
                bgcolor: "#ffae4c",
            },
            {
                key: "messages",
                name: "Messages",
                color: "#FFFFFF",
                bgcolor: "#ffae4c",
            },
        ]);
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
