import ProspectService from "@/apis/project/prospect";

import {
    FETCH_PROSPECTS,
    SET_PROSPECTS,
    SET_PROSPECT,
    ADD_PROSPECT,
    SHOW_PROSPECT,
    DUPLICATE_PROSPECT,
    UPDATE_PROSPECT,
    REMOVE_PROSPECT,
    UPDATE_SELECTED_PROSPECTS,
    TOGGLE_SELECTED_PROSPECTS,
    SET_VIEWED_PROSPECT,
    SET_PROSPECTS_PAGE,
    SET_PROSPECTS_COUNT,
    SET_PROSPECTS_TOTAL,
    SET_PROSPECTS_SORT_BY,
    SET_PROSPECTS_SORT_ORDER,
    SET_PROSPECTS_FIELDS,
    SET_PROSPECT_PARAMS,
    ADD_PROSPECT_PARAMS,
    REMOVE_PROSPECT_PARAMS,
    INIT_PROSPECT_PARAMS,
    SET_PROSPECT_CATEGORY,
    SET_PROSPECT_FILTER_CATEGORY,
    UPDATE_PROSPECT_BULK_GROUPS,
    UPDATE_PROSPECT_BULK_LABELS,
    UPDATE_PROSPECT_BULK_USERS,
    BULK_REMOVE_PROSPECT,
    BULK_FORCE_REMOVE_PROSPECT,
    BULK_RESTORE_PROSPECT,
    BULK_PROCESSED_PROSPECT,
    BULK_NOT_PROCESSED_PROSPECT,
    BULK_UPDATE_PROSPECT,
    BULK_PROJECT_PROSPECT,
    TOGGLE_PROSPECTS_OPTIONS,
    TOGGLE_PROSPECTS_MENUS,
    SET_PROSPECTS_MENU,
    SET_PROSPECTS_MAX_LINES_PER_ROW,
    SET_PROSPECT_PROFILE_SETTING_COLUMN_TAB,
    SET_PROSPECT_PROFILE_SETTING_BLOC_TAB,
} from "@/actions/project/prospect";

import eventStore from "@/store/project/prospect/event";
import fileStore from "@/store/project/prospect/file";
import groupStore from "@/store/project/prospect/group";
import interactionStore from "@/store/project/prospect/interaction";
import labelStore from "@/store/project/prospect/label";
import linkStore from "@/store/project/prospect/link";
import logStore from "@/store/project/prospect/log";
import messageStore from "@/store/project/prospect/message";
import orderStore from "@/store/project/prospect/order";
import revisionStore from "@/store/project/prospect/revision";
import questionStore from "@/store/project/prospect/question";
import questionnaireStore from "@/store/project/prospect/questionnaire";
import pipelineStore from "@/store/project/prospect/pipeline";
import smsStore from "@/store/project/prospect/sms";
import userStore from "@/store/project/prospect/user";

/**
 * Prospect Store state
 */
export const state = {
    prospects: [],
    prospectsSelected: [],
    prospectsSelectedIndex: null,
    prospectsPage: 1,
    prospectsCount: 50,
    prospectsSortBy: null,
    prospectsSortOrder: "desc",
    prospectsFields: null,
    prospectsOptions: false,
    prospectsMenus: true,
    prospectsParams: {},
    prospect: {
        ...eventStore.state,
        ...fileStore.state,
        ...groupStore.state,
        ...interactionStore.state,
        ...labelStore.state,
        ...linkStore.state,
        ...logStore.state,
        ...messageStore.state,
        ...orderStore.state,
        ...questionStore.state,
        ...questionnaireStore.state,
        ...revisionStore.state,
        ...smsStore.state,
        ...userStore.state,
    },
    prospectCategory: null,
    prospectFilterCategory: null,
    prospectBulkGroups: [],
    prospectBulkLabels: [],
    prospectBulkUsers: [],
    prospectProfileSettingColumnTab: 0,
};

/**
 * Prospect Store Actions
 */
const actions = {
    /**
     * Fetch prospects
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PROSPECTS](context) {
        const params = {
            page: context.state.project.prospectsPage
                ? context.state.project.prospectsPage
                : 1,
            count: context.state.project.prospectsCount
                ? context.state.project.prospectsCount
                : 50,
        };

        if (
            context.state.project.prospectsParams &&
            Object.keys(context.state.project.prospectsParams).length > 0
        ) {
            params.filters = JSON.stringify(
                context.state.project.prospectsParams
                    ? context.state.project.prospectsParams
                    : {}
            );
        }

        if (context.state.project.prospectsSortBy) {
            params.sortBy = context.state.project.prospectsSortBy;
        }

        if (context.state.project.prospectsSortOrder) {
            params.sortOrder = context.state.project.prospectsSortOrder;
        }

        if (context.state.project.prospectsFields) {
            params.fields = context.state.project.prospectsFields;
        }

        const { data } = await ProspectService.get(context.state.project.slug, {
            params: params,
        });
        context.commit(SET_PROSPECTS, data.data);
        context.commit(SET_PROSPECTS_TOTAL, data.total);
        return data.data;
    },

    /**
     * Show prospect
     *
     * @param {*} context
     * @param {Number} slug prospect id
     * @returns prospect
     */
    async [SHOW_PROSPECT](context, slug) {
        const { data } = await ProspectService.show(
            context.state.project.slug,
            slug
        );
        context.commit(SET_PROSPECT, data);
        return data;
    },

    /**
     * Duplicate prospect
     *
     * @param {*} context
     * @param {Number} slug prospect id
     * @returns prospect
     */
    async [DUPLICATE_PROSPECT](context, slug) {
        const { data } = await ProspectService.duplicate(
            context.state.project.slug,
            slug
        );
        context.commit(DUPLICATE_PROSPECT, { slug, data });
        return data;
    },

    /**
     * Add prospect
     *
     * @param {*} context
     * @param {Object} params prospect field values
     * @returns prospect
     */
    async [ADD_PROSPECT](context, params) {
        const { data } = await ProspectService.create(
            context.state.project.slug,
            params
        );
        context.commit(ADD_PROSPECT, data);
        return data;
    },

    /**
     * update prospect
     *
     * @param {*} context
     * @param {Object} params new prospect field values
     */
    async [UPDATE_PROSPECT](context, params) {
        context.commit(UPDATE_PROSPECT, params);
        await ProspectService.update(
            context.state.project.slug,
            params.id,
            params
        );
    },

    /**
     * remove prospect
     *
     * @param {*} context
     * @param {Number} params prospect id
     * @returns prospect
     */
    async [REMOVE_PROSPECT](context, slug) {
        await ProspectService.destroy(context.state.project.slug, slug);
        context.commit(REMOVE_PROSPECT, slug);
    },

    /**
     * Bulk remove prospect
     *
     * @param {*} context
     * @param {Number} params prospect id
     * @returns prospect
     */
    async [BULK_REMOVE_PROSPECT](context, prospects) {
        context.commit(BULK_REMOVE_PROSPECT, prospects);
        await ProspectService.bulkDestroy(
            context.state.project.slug,
            prospects
        );
    },

    /**
     * Bulk force remove prospect
     *
     * @param {*} context
     * @param {Number} params prospect id
     * @returns prospect
     */
    async [BULK_FORCE_REMOVE_PROSPECT](context, prospects) {
        context.commit(BULK_FORCE_REMOVE_PROSPECT, prospects);
        await ProspectService.bulkForceDestroy(
            context.state.project.slug,
            prospects
        );
    },

    /**
     * Bulk restore prospect
     *
     * @param {*} context
     * @param {Number} params prospect id
     * @returns prospect
     */
    async [BULK_RESTORE_PROSPECT](context, prospects) {
        await ProspectService.bulkRestore(
            context.state.project.slug,
            prospects
        );
    },

    /**
     * Bulk processed prospect
     *
     * @param {*} context
     * @param {Number} params prospect id
     * @returns prospect
     */
    async [BULK_PROCESSED_PROSPECT](context, prospects) {
        context.commit(BULK_PROCESSED_PROSPECT, prospects);
        await ProspectService.bulkProcessed(
            context.state.project.slug,
            prospects
        );
    },

    /**
     * Bulk not processed prospect
     *
     * @param {*} context
     * @param {Number} params prospect id
     * @returns prospect
     */
    async [BULK_NOT_PROCESSED_PROSPECT](context, prospects) {
        context.commit(BULK_NOT_PROCESSED_PROSPECT, prospects);
        await ProspectService.bulkNotProcessed(
            context.state.project.slug,
            prospects
        );
    },

    /**
     * Bulk update prospect
     *
     * @param {*} context
     * @param {Number} params prospect id
     * @returns prospect
     */
    async [BULK_UPDATE_PROSPECT](context, { prospects, field, value }) {
        context.commit(BULK_UPDATE_PROSPECT, { prospects, field, value });
        await ProspectService.bulkUpdate(
            context.state.project.slug,
            prospects,
            field,
            value
        );
    },

    /**
     * Bulk project prospect
     *
     * @param {*} context
     * @param {Number} params prospect id
     * @returns prospect
     */
    async [BULK_PROJECT_PROSPECT](context, { prospects, project, mapping }) {
        // context.commit(BULK_PROJECT_PROSPECT, { prospects, project });
        await ProspectService.bulkProjectProspect(
            context.state.project.slug,
            prospects,
            project,
            mapping
        );
    },

    ...eventStore.actions,
    ...fileStore.actions,
    ...groupStore.actions,
    ...interactionStore.actions,
    ...labelStore.actions,
    ...linkStore.actions,
    ...logStore.actions,
    ...messageStore.actions,
    ...orderStore.actions,
    ...pipelineStore.actions,
    ...questionStore.actions,
    ...questionnaireStore.actions,
    ...revisionStore.actions,
    ...smsStore.actions,
    ...userStore.actions,
};

/**
 * Prospect Store Mutations
 */
const mutations = {
    /**
     * Set prospects
     *
     * @param {*} state
     * @returns
     */
    [SET_PROSPECTS](state, prospects) {
        state.project.prospects = [...prospects];
    },

    /**
     * Set prospects total
     *
     * @param {*} state
     * @returns
     */
    [SET_PROSPECTS_TOTAL](state, total) {
        state.project.prospectsTotal = total;
    },

    /**
     * Set current prospect
     *
     * @param {*} state
     * @param {Object} prospect
     */
    [SET_PROSPECT](state, prospect) {
        if (!prospect) {
            state.project.prospect = prospect;
        } else {
            state.project.prospectsSelected = [];
            state.project.prospect =
                state.project.prospect &&
                state.project.prospect.id == prospect.id
                    ? { ...state.project.prospect, ...prospect }
                    : prospect;

            if (state.project.prospects) {
                const index = state.project.prospects.findIndex(
                    (p) => p.id == prospect.id
                );
                if (index >= 0) {
                    const p = state.project.prospects[index];
                    state.project.prospects[index] = {
                        ...p,
                        ...prospect,
                        meta: {
                            ...p.meta,
                            ...(prospect.meta ? prospect.meta : {}),
                        },
                    };
                }
            }
            /*state.project.prospects = state.project.prospects.map((o) =>
                o.id == prospect.id ? { ...o, ...prospect } : o
            );*/
        }
    },

    /**
     * Duplicate prospect
     *
     * @param {*} state
     * @returns
     */
    [DUPLICATE_PROSPECT](state, { slug, data }) {
        const index = state.project.prospects.findIndex(
            (prospect) => prospect.id == slug
        );
        if (index >= 0) {
            state.project.prospects = [
                ...state.project.prospects.slice(0, index),
                data,
                ...state.project.prospects.slice(index),
            ];
        }
    },

    /**
     * Add prospect
     *
     * @param {*} state
     * @param {Number} prospect to append to prospects list
     */
    [ADD_PROSPECT](state, prospect) {
        state.project.prospects = [prospect, ...state.project.prospects];
    },

    /**
     * Update prospect
     *
     * @param {*} state
     */
    [UPDATE_PROSPECT](state, params) {
        if (state.project.prospect && state.project.prospect.id == params.id) {
            state.project.prospect = {
                ...state.project.prospect,
                ...params,
                meta: {
                    ...state.project.prospect.meta,
                    ...(params.meta ? params.meta : {}),
                },
            };
        }

        if (state.project.prospects) {
            const index = state.project.prospects.findIndex(
                (p) => p.id == params.id
            );
            if (index >= 0) {
                const prospect = state.project.prospects[index];
                state.project.prospects[index] = {
                    ...prospect,
                    ...params,
                    meta: {
                        ...prospect.meta,
                        ...(params.meta ? params.meta : {}),
                    },
                };
            }
        }
        /*state.project.prospects = state.project.prospects.map((o) =>
            o.id == params.id
                ? {
                      ...o,
                      ...params,
                      meta: { ...o.meta, ...(params.meta ? params.meta : {}) },
                  }
                : o
        );
        if (state.project.prospect.id == params.id) {
            state.project.prospect = { ...state.project.prospect, ...params };
        }*/
    },

    /**
     * remove prospect
     *
     * @param {*} context
     * @param {Number} params prospect id
     */
    [REMOVE_PROSPECT](state, slug) {
        state.project.prospects = state.project.prospects.filter(
            (o) => o.id != slug
        );
    },

    /**
     * Update list of selected prospects
     *
     * @param {*} state
     */
    [UPDATE_SELECTED_PROSPECTS](state, value) {
        state.project.prospectsSelected = Array.isArray(value) ? value : [];
    },

    /**
     * Toggle list of selected prospects
     *
     * @param {*} state
     */
    [TOGGLE_SELECTED_PROSPECTS](state, { index, shift, checked }) {
        const prospectsSelectedIndex = state.project.prospectsSelectedIndex;
        state.project.prospectsSelectedIndex = index;

        if (
            prospectsSelectedIndex === null ||
            !shift ||
            index === prospectsSelectedIndex
        ) {
            return;
        }

        const subset = state.project.prospects
            .slice(
                Math.min(index, prospectsSelectedIndex + 1),
                Math.max(index, prospectsSelectedIndex - 1) + 1
            )
            .map((prospect) => prospect.id);

        state.project.prospectsSelected = checked
            ? [
                  ...state.project.prospectsSelected,
                  ...subset.filter(
                      (id) => state.project.prospectsSelected.indexOf(id) == -1
                  ),
              ]
            : state.project.prospectsSelected.filter(
                  (id) => subset.indexOf(id) == -1
              );
    },

    /**
     * @param {*} state
     */
    [SET_PROSPECTS_PAGE](state, page) {
        state.project.prospectsPage = page;
    },

    /**
     * @param {*} state
     */
    [SET_VIEWED_PROSPECT](state, id) {
        state.project.viewedProspect = id;
    },

    /**
     * @param {*} state
     */
    [SET_PROSPECTS_MAX_LINES_PER_ROW](state, lines) {
        state.project.prospectsMaxLinesPerRow = lines;
    },

    /**
     * @param {*} state
     */
    [SET_PROSPECTS_COUNT](state, count) {
        state.project.prospectsCount = count;
    },

    /**
     * @param {*} state
     */
    [SET_PROSPECTS_SORT_BY](state, column) {
        state.project.prospectsSortBy = column;
    },

    /**
     * @param {*} state
     */
    [SET_PROSPECTS_SORT_ORDER](state, order) {
        state.project.prospectsSortOrder = order;
    },

    /**
     * @param {*} state
     */
    [SET_PROSPECTS_FIELDS](state, fields) {
        state.project.prospectsFields = fields;
    },

    /**
     * @param {*} state
     */
    [SET_PROSPECT_PROFILE_SETTING_COLUMN_TAB](state, tab) {
        state.project.prospectProfileSettingColumnTab = tab;
    },

    /**
     * @param {*} state
     */
    [SET_PROSPECT_PROFILE_SETTING_BLOC_TAB](state, tab) {
        state.project.prospectProfileSettingBlocTab = tab;
    },

    /**
     * @param {*} state
     */
    [SET_PROSPECT_PARAMS](state, value) {
        if (typeof value == "object") {
            state.project.prospectsParams = value;
        }
    },

    /**
     * @param {*} state
     */
    [ADD_PROSPECT_PARAMS](state, { key, value, multiple }) {
        state.project.prospectsPage = 1;

        if (!state.project.prospectsParams) {
            state.project.prospectsParams = {};
        }

        if (value == undefined) {
            value = "";
        }

        if (multiple) {
            if (state.project.prospectsParams[key] === undefined) {
                state.project.prospectsParams[key] = [];
            }

            let values = "" + value;
            if (state.project.prospectsParams[key] !== "") {
                values = [...state.project.prospectsParams[key], value];
            }

            state.project.prospectsParams = Object.fromEntries(
                Object.entries(state.project.prospectsParams).map(([k, v]) => [
                    k,
                    k == key ? values : v,
                ])
            );
        } else {
            state.project.prospectsParams = {
                ...state.project.prospectsParams,
                [key]: value,
            };
        }
    },

    /**
     * @param {*} state
     */
    [REMOVE_PROSPECT_PARAMS](state, { key, value }) {
        state.project.prospectsPage = 1;

        if (!state.project.prospectsParams) {
            state.project.prospectsParams = {};
        }

        if (value !== undefined) {
            if (state.project.prospectsParams[key]) {
                state.project.prospectsParams = Object.fromEntries(
                    Object.entries(state.project.prospectsParams).map(
                        ([k, v]) => [
                            k,
                            k == key
                                ? state.project.prospectsParams[k].filter(
                                      (val) => val != value
                                  )
                                : v,
                        ]
                    )
                );

                if (state.project.prospectsParams[key].length == 0) {
                    state.project.prospectsParams = Object.fromEntries(
                        Object.entries(state.project.prospectsParams).filter(
                            ([k]) => k != key
                        )
                    );
                }
            }
        } else {
            state.project.prospectsParams = Object.fromEntries(
                Object.entries(state.project.prospectsParams).filter(
                    ([k]) => k != key
                )
            );
        }
    },

    /**
     * @param {*} state
     */
    [INIT_PROSPECT_PARAMS](state) {
        state.project.prospectsPage = 1;

        state.project.prospectsSortOrder = null;
        state.project.prospectsSortBy = null;
        state.project.prospectsParams = {};
        state.project.prospectsMenu = null; /*Object.fromEntries(
            Object.entries(state.project.prospectsParams).filter(([k]) => k == "count")
        );*/
    },

    /**
     * @param {*} state
     */
    [SET_PROSPECT_CATEGORY](state, data) {
        state.project.prospectCategory = data;
    },

    /**
     * @param {*} state
     */
    [SET_PROSPECT_FILTER_CATEGORY](state, data) {
        state.project.prospectFilterCategory = data;
    },

    /**
     * Update list of prospects bulk groups
     *
     * @param {*} state
     */
    [UPDATE_PROSPECT_BULK_GROUPS](state, value) {
        state.project.prospectBulkGroups = value;
    },

    /**
     * Update list of prospects bulk labels
     *
     * @param {*} state
     */
    [UPDATE_PROSPECT_BULK_LABELS](state, value) {
        state.project.prospectBulkLabels = value;
    },

    /**
     * Update list of prospects bulk users
     *
     * @param {*} state
     */
    [UPDATE_PROSPECT_BULK_USERS](state, value) {
        state.project.prospectBulkUsers = value;
    },

    /**
     * Update list of prospects bulk remove
     *
     * @param {*} state
     */
    [BULK_REMOVE_PROSPECT](state, prospects) {
        state.project.prospects = state.project.prospects.map((prospect) =>
            prospects.indexOf(prospect.id) >= 0
                ? { ...prospect, deleted_at: new Date() }
                : prospect
        );
    },

    /**
     * Update list of prospects bulk force remove
     *
     * @param {*} state
     */
    [BULK_FORCE_REMOVE_PROSPECT](state, prospects) {
        state.project.prospects = state.project.prospects.filter(
            (prospect) => prospects.indexOf(prospect.id) < 0
        );
    },

    /**
     * Update list of prospects bulk processed
     *
     * @param {*} state
     */
    [BULK_PROCESSED_PROSPECT](state, prospects) {
        state.project.prospects = state.project.prospects.map((prospect) =>
            prospects.indexOf(prospect.id) >= 0
                ? { ...prospect, processed_at: new Date() }
                : prospect
        );
    },

    /**
     * Update list of prospects bulk not processed
     *
     * @param {*} state
     */
    [BULK_NOT_PROCESSED_PROSPECT](state, prospects) {
        state.project.prospects = state.project.prospects.map((prospect) =>
            prospects.indexOf(prospect.id) >= 0
                ? { ...prospect, processed_at: null }
                : prospect
        );
    },

    /**
     * Update list of prospects
     *
     * @param {*} state
     */
    [BULK_UPDATE_PROSPECT](state, { prospects, field, value }) {
        const meta = field.indexOf("meta->") == 0;
        field = field.replace("meta->", "");
        state.project.prospects = state.project.prospects.map((prospect) =>
            prospects.indexOf(prospect.id) >= 0
                ? meta
                    ? {
                          ...prospect,
                          meta: { ...prospect.meta, [field]: value },
                      }
                    : { ...prospect, [field]: value }
                : prospect
        );
    },

    /**
     * Toggle show prospects table options
     *
     * @param {*} state
     */
    [TOGGLE_PROSPECTS_OPTIONS](state) {
        state.project.prospectsOptions =
            state.project.prospectsOptions !== undefined
                ? !state.project.prospectsOptions
                : false;
    },

    /**
     * Toggle show prospects table menus
     *
     * @param {*} state
     */
    [TOGGLE_PROSPECTS_MENUS](state) {
        state.project.prospectsMenus =
            state.project.prospectsMenus !== undefined
                ? !state.project.prospectsMenus
                : false;
    },

    /**
     * Set prospect menu
     *
     * @param {*} state
     */
    [SET_PROSPECTS_MENU](state, value) {
        state.project.prospectsMenu = value;
    },

    ...eventStore.mutations,
    ...fileStore.mutations,
    ...groupStore.mutations,
    ...interactionStore.mutations,
    ...labelStore.mutations,
    ...linkStore.mutations,
    ...logStore.mutations,
    ...messageStore.mutations,
    ...orderStore.mutations,
    ...questionStore.mutations,
    ...questionnaireStore.mutations,
    ...revisionStore.mutations,
    ...pipelineStore.mutations,
    ...smsStore.mutations,
    ...userStore.mutations,
};

/**
 * Prospect Store Getters
 */
const getters = {
    /**
     * Get list of prospects
     *
     * @param {*} state
     * @returns
     */
    prospects: (state) =>
        state.project && state.project.prospects ? state.project.prospects : [],

    /**
     * Get total of prospects
     *
     * @param {*} state
     * @returns
     */
    prospectsTotal: (state) =>
        state.project && state.project.prospectsTotal
            ? state.project.prospectsTotal
            : 0,

    /**
     * Get list of selected prospects
     *
     * @param {*} state
     * @returns
     */
    prospectsSelected: (state) =>
        state.project &&
        state.project.prospectsSelected &&
        Array.isArray(state.project.prospectsSelected)
            ? state.project.prospectsSelected
            : [],

    /**
     * @param {*} state
     * @returns
     */
    prospectsParams: (state) =>
        state.project && state.project.prospectsParams
            ? state.project.prospectsParams
            : {},

    /**
     * @param {*} state
     * @returns
     */
    prospectsParamsUrl: (state) =>
        state.project &&
        state.project.prospectsParams &&
        Object.keys(state.project.prospectsParams).length > 0
            ? encodeURI(JSON.stringify(state.project.prospectsParams))
            : "",

    /**
     * @param {*} state
     * @returns
     */
    prospectsParamValue: (state) => (param) =>
        state.project && state.project.prospectsParams
            ? state.project.prospectsParams[param]
            : undefined,

    /**
     * @param {*} state
     * @returns
     */
    prospectsParamExists: (state) => (param, value) => {
        if (!state.project || !state.project.prospectsParams) {
            return false;
        }

        if (param === undefined) {
            return Object.keys(state.project.prospectsParams).length > 0;
        }

        if (value === undefined) {
            return state.project.prospectsParams[param] !== undefined;
        }

        return (
            state.project.prospectsParams[param] !== undefined &&
            state.project.prospectsParams[param].findIndex((p) => p == value) >=
                0
        );
    },

    /**
     * @param {*} state
     * @returns
     */
    prospectsParamCount: (state) => (param) => {
        if (
            !state.project ||
            !state.project.prospectsParams ||
            state.project.prospectsParams[param] === undefined
        ) {
            return 0;
        }

        if (!Array.isArray(state.project.prospectsParams[param])) {
            return 1;
        }

        return state.project.prospectsParams[param].length;
    },

    /**
     * @param {*} state
     * @returns
     */
    prospectCategory: (state) =>
        state.project && state.project.prospectCategory
            ? state.project.prospectCategory
            : null,

    /**
     * @param {*} state
     * @returns
     */
    prospectFilterCategory: (state) =>
        state.project && state.project.prospectFilterCategory
            ? state.project.prospectFilterCategory
            : null,

    /**
     * @param {*} state
     * @returns
     */
    prospectBulkGroups: (state) =>
        state.project && Array.isArray(state.project.prospectBulkGroups)
            ? state.project.prospectBulkGroups
            : [],

    /**
     * @param {*} state
     * @returns
     */
    prospectBulkLabels: (state) =>
        state.project && Array.isArray(state.project.prospectBulkLabels)
            ? state.project.prospectBulkLabels
            : [],

    /**
     * @param {*} state
     * @returns
     */
    prospectBulkUsers: (state) =>
        state.project && Array.isArray(state.project.prospectBulkUsers)
            ? state.project.prospectBulkUsers
            : [],

    /**
     * Get last viewed prospect
     *
     * @param {*} state
     * @returns
     */
    viewedProspect: (state) =>
        state.project ? state.project.viewedProspect : null,

    /**
     * Get prospects page
     *
     * @param {*} state
     * @returns
     */
    prospectsPage: (state) => (state.project ? state.project.prospectsPage : 0),

    /**
     * Get prospects page
     *
     * @param {*} state
     * @returns
     */
    prospectsMaxLinesPerRow: (state) =>
        state.project && state.project.prospectsMaxLinesPerRow
            ? state.project.prospectsMaxLinesPerRow
            : 1,

    /**
     * Get prospects count pagination
     *
     * @param {*} state
     * @returns
     */
    prospectsCount: (state) =>
        state.project && state.project.prospectsCount
            ? state.project.prospectsCount
            : 50,

    /**
     * Get prospects sort by
     *
     * @param {*} state
     * @returns
     */
    prospectsSortBy: (state) =>
        state.project ? state.project.prospectsSortBy : null,

    /**
     * Get prospects sort order
     *
     * @param {*} state
     * @returns
     */
    prospectsSortOrder: (state) =>
        state.project ? state.project.prospectsSortOrder : "desc",

    /**
     * Get prospects count pagination
     *
     * @param {*} state
     * @returns
     */
    prospectsFields: (state) =>
        state.project && state.project.prospectsFields
            ? state.project.prospectsFields
            : null,

    /**
     * Get prospects options
     *
     * @param {*} state
     * @returns
     */
    prospectsOptions: (state) => {
        return state.project && state.project.prospectsOptions !== undefined
            ? state.project.prospectsOptions
            : true;
    },

    /**
     * Get prospects menus
     *
     * @param {*} state
     * @returns
     */
    prospectsMenus: (state) =>
        state.project && state.project.prospectsMenus !== undefined
            ? state.project.prospectsMenus
            : true,

    /**
     * Set prospects menu
     *
     * @param {*} state
     * @returns
     */
    prospectsMenu: (state) =>
        state.project ? state.project.prospectsMenu : null,

    /**
     * Get current prospect
     *
     * @param {*} state
     * @returns
     */
    prospect: (state) =>
        state.project && state.project.prospect ? state.project.prospect : null,

    /**
     * Get prospect profile setting column tab
     *
     * @param {*} state
     * @returns
     */
    prospectProfileSettingColumnTab: (state) =>
        state.project ? state.project.prospectProfileSettingColumnTab : 0,

    /**
     * Get prospect profile setting bloc tab
     *
     * @param {*} state
     * @returns
     */
    prospectProfileSettingBlocTab: (state) =>
        state.project ? state.project.prospectProfileSettingBlocTab : 0,

    /**
     * Get current prospect full name
     *
     * @param {*} state
     * @returns
     */
    prospectFullName: (state) =>
        state.project && state.project.prospect
            ? [
                  state.project.prospect.first_name,
                  state.project.prospect.last_name,
              ]
                  .filter((n) => n)
                  .join(" ")
            : "",

    ...eventStore.getters,
    ...fileStore.getters,
    ...groupStore.getters,
    ...interactionStore.getters,
    ...labelStore.getters,
    ...linkStore.getters,
    ...logStore.getters,
    ...messageStore.getters,
    ...orderStore.getters,
    ...pipelineStore.getters,
    ...questionStore.getters,
    ...questionnaireStore.getters,
    ...revisionStore.getters,
    ...smsStore.getters,
    ...userStore.getters,
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
