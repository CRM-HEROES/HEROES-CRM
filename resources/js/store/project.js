import ProjectService from "@/apis/project";

import {
    FETCH_PROJECTS,
    SET_PROJECTS,
    SET_PROJECT,
    ADD_PROJECT,
    SHOW_PROJECT,
    UPDATE_PROJECT,
    REMOVE_PROJECT,
    UPDATE_SELECTED_PROJECTS,
    TOGGLE_SELECTED_PROJECTS,
    SET_PROJECTS_PAGE,
    SET_PROJECTS_COUNT,
    SET_PROJECTS_SORT_BY,
    SET_PROJECTS_SORT_ORDER,
    SET_PROJECTS_FIELDS,
    SET_PROJECT_PARAMS,
    ADD_PROJECT_PARAMS,
    REMOVE_PROJECT_PARAMS,
    INIT_PROJECT_PARAMS,
    BULK_REMOVE_PROJECT,
    TOGGLE_PROJECTS_OPTIONS,
    TOGGLE_PROJECTS_MENUS,
} from "@/actions/project";

import calendarStore from "./project/calendar";
import categoryStore from "./project/category";
import campaignStore from "./project/campaign";
import campaignActionStore from "./project/campaign/action";
import campaignOperatorStore from "./project/campaign/operator";
import campaignRuleStore from "./project/campaign/rule";
import commissionStore from "./project/commission";
import documentStore from "./project/document";
import documentTemplateStore from "./project/document-template";
import eventDescriptionTemplateStore from "./project/event-description-template";
import eventStore from "./project/event";
import fieldStore from "./project/field";
import folderStore from "./project/folder";
import groupStore from "./project/group";
import importStore from "./project/import";
import mapStore from "./project/map";
import metricStore from "./project/stat/metric";
import menuStore from "./project/menu";
import messageTemplateStore from "./project/message-template";
import orderStore from "./project/order";
import orderActionStore from "./project/order/action";
import orderStatusStore from "./project/order/status";
import orderStepStore from "./project/order/step";
import pipedriveAccountStore from "./project/pipedrive-account";
import pipelineStore from "./project/pipeline";
import pipelineLabelStore from "./project/pipeline/label";
import productStore from "./project/product";
import prospectStore from "./project/prospect";
import questionnaireStore from "./project/questionnaire";
import questionnaireSectionStore from "./project/questionnaire/section";
import questionnaireQuestionStore from "./project/questionnaire/question";
import questionnaireOptionStore from "./project/questionnaire/option";
import roleStore from "./project/role";
import settingStore from "./project/setting";
import statMapStore from "./project/stat/map";
import smsTemplateStore from "./project/sms-template";
import threadStore from "./project/thread";
import userStore from "./project/user";
import userDailyDirectionStore from "./project/user-daily-direction";
import userSettingStore from "./project/user/setting";
import vehicleStore from "./project/vehicle";

/**
 * Project Store state
 */
export const state = {
    projects: [],
    project: {
        ...prospectStore.state,
        ...statMapStore.state,
        ...mapStore.state,
    },
};

/**
 * Project Store Actions
 */
export const actions = {
    /**
     * Fetch projects
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_PROJECTS](context) {
        const params = {
            page: context.state.projectsPage ? context.state.projectsPage : 1,
            count: context.state.projectsCount
                ? context.state.projectsCount
                : 50,
        };

        if (
            context.state.projectsParams &&
            Object.keys(context.state.projectsParams).length > 0
        ) {
            params.filters = JSON.stringify(
                context.state.projectsParams ? context.state.projectsParams : {}
            );
        }

        if (context.state.projectsSortBy) {
            params.sortBy = context.state.projectsSortBy;
        }

        if (context.state.projectsSortOrder) {
            params.sortOrder = context.state.projectsSortOrder;
        }

        if (context.state.projectsFields) {
            params.fields = context.state.projectsFields;
        }

        const { data } = await ProjectService.get({
            params: params,
        });
        context.commit(SET_PROJECTS, data.data);
        return data;
    },

    /**
     * Show project
     *
     * @param {*} context
     * @param {Number} slug project id
     * @returns project
     */
    async [SHOW_PROJECT](context, slug) {
        const project = context.state.projects.find((o) => o.slug == slug);

        if (project) {
            context.commit(SET_PROJECT, project);
        } else if (
            !context.state.project ||
            context.state.project.slug != slug
        ) {
            context.commit(SET_PROJECT, {
                slug: slug,
            });
        }

        const { data } = await ProjectService.show(slug);
        context.commit(SET_PROJECT, data);
        return data;
    },

    /**
     * Add project
     *
     * @param {*} context
     * @param {Object} params project field values
     * @returns project
     */
    async [ADD_PROJECT](context, params) {
        const { data } = await ProjectService.create(params);
        context.commit(ADD_PROJECT, data);
        return data;
    },

    /**
     * update project
     *
     * @param {*} context
     * @param {Object} params new project field values
     */
    async [UPDATE_PROJECT](context, params) {
        await ProjectService.update(params.slug, params);
        context.commit(UPDATE_PROJECT, params);
    },

    /**
     * remove project
     *
     * @param {*} context
     * @param {Number} params project id
     * @returns project
     */
    async [REMOVE_PROJECT](context, slug) {
        await ProjectService.destroy(slug);
        context.commit(REMOVE_PROJECT, slug);
    },

    /**
     * Bulk remove projects
     *
     * @param {*} context
     * @param {Number} params projects
     * @returns project
     */
    async [BULK_REMOVE_PROJECT](context, projects) {
        context.commit(BULK_REMOVE_PROJECT, projects);
        await ProjectService.bulkDestroy(projects);
    },

    ...calendarStore.actions,
    ...categoryStore.actions,
    ...campaignStore.actions,
    ...campaignActionStore.actions,
    ...campaignOperatorStore.actions,
    ...campaignRuleStore.actions,
    ...commissionStore.actions,
    ...documentStore.actions,
    ...documentTemplateStore.actions,
    ...eventDescriptionTemplateStore.actions,
    ...eventStore.actions,
    ...fieldStore.actions,
    ...folderStore.actions,
    ...groupStore.actions,
    ...importStore.actions,
    ...menuStore.actions,
    ...metricStore.actions,
    ...messageTemplateStore.actions,
    ...orderStore.actions,
    ...orderActionStore.actions,
    ...orderStepStore.actions,
    ...orderStatusStore.actions,
    ...pipelineStore.actions,
    ...pipelineLabelStore.actions,
    ...productStore.actions,
    ...prospectStore.actions,
    ...questionnaireStore.actions,
    ...questionnaireSectionStore.actions,
    ...questionnaireQuestionStore.actions,
    ...questionnaireOptionStore.actions,
    ...roleStore.actions,
    ...settingStore.actions,
    ...smsTemplateStore.actions,
    ...threadStore.actions,
    ...userStore.actions,
    ...userSettingStore.actions,
    ...userDailyDirectionStore.actions,
    ...vehicleStore.actions,
};

/**
 * Project Store Mutations
 */
export const mutations = {
    /**
     * Set projects
     *
     * @param {*} state
     * @returns
     */
    [SET_PROJECTS](state, projects) {
        state.projects = [...projects];
    },

    /**
     * Set current project
     *
     * @param {*} state
     * @param {Object} project
     */
    [SET_PROJECT](state, project) {
        if (project) {
            if (state.projects.find((o) => o.slug == project.slug)) {
                state.projects = state.projects.map((o) =>
                    o.slug == project.slug ? { ...o, ...project } : o
                );
            } else {
                state.projects = [...state.projects, project];
            }

            state.project = state.projects.find((o) => o.slug == project.slug);
        } else {
            state.project = null;
        }
    },

    /**
     * Add project
     *
     * @param {*} state
     * @param {Number} project to append to projects list
     */
    [ADD_PROJECT](state, project) {
        state.projects = [...state.projects, project];
    },

    /**
     * Update project
     *
     * @param {*} state
     */
    [UPDATE_PROJECT](state, params) {
        state.projects = state.projects.map((o) =>
            o.id == params.slug ? { ...o, ...params } : o
        );
    },

    /**
     * remove project
     *
     * @param {*} context
     * @param {Number} params project id
     */
    [REMOVE_PROJECT](state, slug) {
        state.projects = state.projects.filter((o) => o.id != slug);
    },

    /**
     * Update list of selected projects
     *
     * @param {*} state
     */
    [UPDATE_SELECTED_PROJECTS](state, value) {
        state.projectsSelected = value;
    },

    /**
     * Toggle list of selected projects
     *
     * @param {*} state
     */
    [TOGGLE_SELECTED_PROJECTS](state, { index, shift, checked }) {
        const projectsSelectedIndex = state.projectsSelectedIndex;
        state.projectsSelectedIndex = index;

        if (
            projectsSelectedIndex === null ||
            !shift ||
            index === projectsSelectedIndex
        ) {
            return;
        }

        const subset = state.projects
            .slice(
                Math.min(index, projectsSelectedIndex + 1),
                Math.max(index, projectsSelectedIndex - 1) + 1
            )
            .map((project) => project.id);

        state.projectsSelected = checked
            ? [
                  ...state.projectsSelected,
                  ...subset.filter(
                      (id) => state.projectsSelected.indexOf(id) == -1
                  ),
              ]
            : state.projectsSelected.filter((id) => subset.indexOf(id) == -1);
    },

    /**
     * @param {*} state
     */
    [SET_PROJECTS_PAGE](state, page) {
        state.projectsPage = page;
    },

    /**
     * @param {*} state
     */
    [SET_PROJECTS_COUNT](state, count) {
        state.projectsCount = count;
    },

    /**
     * @param {*} state
     */
    [SET_PROJECTS_SORT_BY](state, column) {
        state.projectsSortBy = column;
    },

    /**
     * @param {*} state
     */
    [SET_PROJECTS_SORT_ORDER](state, order) {
        state.projectsSortOrder = order;
    },

    /**
     * @param {*} state
     */
    [SET_PROJECTS_FIELDS](state, fields) {
        state.projectsFields = fields;
    },

    /**
     * @param {*} state
     */
    [SET_PROJECT_PARAMS](state, value) {
        if (typeof value == "object") {
            state.projectsParams = value;
        }
    },

    /**
     * @param {*} state
     */
    [ADD_PROJECT_PARAMS](state, { key, value, multiple }) {
        state.projectsPage = 1;

        if (!state.projectsParams) {
            state.projectsParams = {};
        }

        if (value == undefined) {
            value = "";
        }

        if (multiple) {
            if (state.projectsParams[key] === undefined) {
                state.projectsParams[key] = [];
            }

            let values = "" + value;
            if (state.projectsParams[key] !== "") {
                values = [...state.projectsParams[key], value];
            }

            state.projectsParams = Object.fromEntries(
                Object.entries(state.projectsParams).map(([k, v]) => [
                    k,
                    k == key ? values : v,
                ])
            );
        } else {
            state.projectsParams = {
                ...state.projectsParams,
                [key]: value,
            };
        }
    },

    /**
     * @param {*} state
     */
    [REMOVE_PROJECT_PARAMS](state, { key, value }) {
        state.projectsPage = 1;

        if (!state.projectsParams) {
            state.projectsParams = {};
        }

        if (value !== undefined) {
            if (state.projectsParams[key]) {
                state.projectsParams = Object.fromEntries(
                    Object.entries(state.projectsParams).map(([k, v]) => [
                        k,
                        k == key
                            ? state.projectsParams[k].filter(
                                  (val) => val != value
                              )
                            : v,
                    ])
                );

                if (state.projectsParams[key].length == 0) {
                    state.projectsParams = Object.fromEntries(
                        Object.entries(state.projectsParams).filter(
                            ([k]) => k != key
                        )
                    );
                }
            }
        } else {
            state.projectsParams = Object.fromEntries(
                Object.entries(state.projectsParams).filter(([k]) => k != key)
            );
        }
    },

    /**
     * @param {*} state
     */
    [INIT_PROJECT_PARAMS](state) {
        state.projectsPage = 1;

        state.projectsSortOrder = null;
        state.projectsSortBy = null;
        state.projectsParams = {}; /*Object.fromEntries(
            Object.entries(state.projectsParams).filter(([k]) => k == "count")
        );*/
    },

    /**
     * Update list of projects bulk processed
     *
     * @param {*} state
     */
    [BULK_REMOVE_PROJECT](state, projects) {
        state.projects = state.projects.map((project) =>
            projects.indexOf(project.id) >= 0
                ? { ...project, deleted_at: new Date() }
                : project
        );
    },

    /**
     * Toggle show projects table options
     *
     * @param {*} state
     */
    [TOGGLE_PROJECTS_OPTIONS](state) {
        state.projectsOptions =
            state.projectsOptions !== undefined
                ? !state.projectsOptions
                : false;
    },

    /**
     * Toggle show projects table menus
     *
     * @param {*} state
     */
    [TOGGLE_PROJECTS_MENUS](state) {
        state.projectsMenus = !state.projectsMenus;
    },

    ...calendarStore.mutations,
    ...categoryStore.mutations,
    ...campaignStore.mutations,
    ...campaignActionStore.mutations,
    ...campaignOperatorStore.mutations,
    ...campaignRuleStore.mutations,
    ...commissionStore.mutations,
    ...documentStore.mutations,
    ...documentTemplateStore.mutations,
    ...fieldStore.mutations,
    ...folderStore.mutations,
    ...eventDescriptionTemplateStore.mutations,
    ...eventStore.mutations,
    ...groupStore.mutations,
    ...importStore.mutations,
    ...mapStore.mutations,
    ...menuStore.mutations,
    ...metricStore.mutations,
    ...messageTemplateStore.mutations,
    ...orderStore.mutations,
    ...orderActionStore.mutations,
    ...orderStepStore.mutations,
    ...orderStatusStore.mutations,
    ...pipelineStore.mutations,
    ...pipelineLabelStore.mutations,
    ...productStore.mutations,
    ...prospectStore.mutations,
    ...questionnaireStore.mutations,
    ...questionnaireSectionStore.mutations,
    ...questionnaireQuestionStore.mutations,
    ...questionnaireOptionStore.mutations,
    ...roleStore.mutations,
    ...settingStore.mutations,
    ...statMapStore.mutations,
    ...smsTemplateStore.mutations,
    ...threadStore.mutations,
    ...userStore.mutations,
    ...userSettingStore.mutations,
    ...userDailyDirectionStore.mutations,
    ...vehicleStore.mutations,
};

/**
 * Project Store Getters
 */
const getters = {
    /**
     * Get list of projects
     *
     * @param {*} state
     * @returns
     */
    projects: (state) => state.projects,

    /**
     * Get current project
     *
     * @param {*} state
     * @returns
     */
    project: (state) => state.project,

    /**
     * Get list of selected projects
     *
     * @param {*} state
     * @returns
     */
    projectsSelected: (state) =>
        state.projectsSelected && Array.isArray(state.projectsSelected)
            ? state.projectsSelected
            : [],

    /**
     * @param {*} state
     * @returns
     */
    projectsParams: (state) =>
        state.projectsParams ? state.projectsParams : {},

    /**
     * @param {*} state
     * @returns
     */
    projectsParamsUrl: (state) =>
        state.projectsParams && Object.keys(state.projectsParams).length > 0
            ? encodeURI(JSON.stringify(state.projectsParams))
            : "",

    /**
     * @param {*} state
     * @returns
     */
    projectsParamValue: (state) => (param) =>
        state.projectsParams ? state.projectsParams[param] : undefined,

    /**
     * @param {*} state
     * @returns
     */
    projectsParamExists: (state) => (param, value) => {
        if (!state.projectsParams) {
            return false;
        }

        if (param === undefined) {
            return Object.keys(state.projectsParams).length > 0;
        }

        if (value === undefined) {
            return state.projectsParams[param] !== undefined;
        }

        return (
            state.projectsParams[param] !== undefined &&
            state.projectsParams[param].findIndex((p) => p == value) >= 0
        );
    },

    /**
     * @param {*} state
     * @returns
     */
    projectsParamCount: (state) => (param) => {
        if (
            !state.projectsParams ||
            state.projectsParams[param] === undefined
        ) {
            return 0;
        }

        if (!Array.isArray(state.projectsParams[param])) {
            return 1;
        }

        return state.projectsParams[param].length;
    },

    /**
     * Get projects page
     *
     * @param {*} state
     * @returns
     */
    projectsPage: (state) => (state.projectsPage ? state.projectsPage : 0),

    /**
     * Get projects count pagination
     *
     * @param {*} state
     * @returns
     */
    projectsCount: (state) => (state.projectsCount ? state.projectsCount : 50),

    /**
     * Get projects sort by
     *
     * @param {*} state
     * @returns
     */
    projectsSortBy: (state) =>
        state.projectsSortBy ? state.projectsSortBy : null,

    /**
     * Get projects sort order
     *
     * @param {*} state
     * @returns
     */
    projectsSortOrder: (state) =>
        state.projectsSortOrder ? state.projectsSortOrder : "desc",

    /**
     * Get projects count pagination
     *
     * @param {*} state
     * @returns
     */
    projectsFields: (state) =>
        state.projectsFields ? state.projectsFields : null,

    /**
     * Get projects options
     *
     * @param {*} state
     * @returns
     */
    projectsOptions: (state) =>
        state.projectsOptions && state.projectsOptions !== undefined
            ? state.projectsOptions
            : true,

    /**
     * Get projects menus
     *
     * @param {*} state
     * @returns
     */
    projectsMenus: (state) =>
        state.projectsMenus ? state.projectsMenus : true,

    ...calendarStore.getters,
    ...categoryStore.getters,
    ...campaignStore.getters,
    ...campaignActionStore.getters,
    ...campaignOperatorStore.getters,
    ...campaignRuleStore.getters,
    ...commissionStore.getters,
    ...documentStore.getters,
    ...documentTemplateStore.getters,
    ...eventDescriptionTemplateStore.getters,
    ...eventStore.getters,
    ...fieldStore.getters,
    ...folderStore.getters,
    ...groupStore.getters,
    ...importStore.getters,
    ...mapStore.getters,
    ...menuStore.getters,
    ...metricStore.getters,
    ...messageTemplateStore.getters,
    ...orderStore.getters,
    ...orderActionStore.getters,
    ...orderStepStore.getters,
    ...orderStatusStore.getters,
    ...pipedriveAccountStore.getters,
    ...pipelineStore.getters,
    ...pipelineLabelStore.getters,
    ...productStore.getters,
    ...prospectStore.getters,
    ...questionnaireStore.getters,
    ...questionnaireSectionStore.getters,
    ...questionnaireQuestionStore.getters,
    ...questionnaireOptionStore.getters,
    ...roleStore.getters,
    ...settingStore.getters,
    ...statMapStore.getters,
    ...smsTemplateStore.getters,
    ...threadStore.getters,
    ...userStore.getters,
    ...userSettingStore.getters,
    ...userDailyDirectionStore.getters,
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
