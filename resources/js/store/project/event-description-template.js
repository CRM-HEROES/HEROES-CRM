import EventDescriptionTemplateService from "@/apis/project/event-description-template";

import {
    FETCH_EVENT_DESCRIPTION_TEMPLATES,
    SET_EVENT_DESCRIPTION_TEMPLATES,
    SET_EVENT_DESCRIPTION_TEMPLATE,
    ADD_EVENT_DESCRIPTION_TEMPLATE,
    SHOW_EVENT_DESCRIPTION_TEMPLATE,
    UPDATE_EVENT_DESCRIPTION_TEMPLATE,
    REMOVE_EVENT_DESCRIPTION_TEMPLATE,
} from "@/actions/project/event-description-template";

/**
 * Event description Template Store Actions
 */
const actions = {
    /**
     * Fetch event description templates
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_EVENT_DESCRIPTION_TEMPLATES](context, params) {
        const { data } = await EventDescriptionTemplateService.get(
            context.state.project.slug,
            params
        );
        context.commit(SET_EVENT_DESCRIPTION_TEMPLATES, data);
        return data;
    },

    /**
     * Show event description template
     *
     * @param {*} context
     * @param {Number} slug eventDescriptionTemplate id
     * @returns eventDescriptionTemplate
     */
    async [SHOW_EVENT_DESCRIPTION_TEMPLATE](context, slug) {
        const { data } = await EventDescriptionTemplateService.show(
            context.state.project.slug,
            slug
        );
        // context.commit(SET_EVENT_DESCRIPTION_TEMPLATE, data);
        return data;
    },

    /**
     * Add event description template
     *
     * @param {*} context
     * @param {Object} params eventDescriptionTemplate event description template values
     * @returns eventDescriptionTemplate
     */
    async [ADD_EVENT_DESCRIPTION_TEMPLATE](context, params) {
        const { data } = await EventDescriptionTemplateService.create(
            context.state.project.slug,
            params
        );
        context.commit(ADD_EVENT_DESCRIPTION_TEMPLATE, data);
        return data;
    },

    /**
     * update event description template
     *
     * @param {*} context
     * @param {Object} params new event description template values
     */
    async [UPDATE_EVENT_DESCRIPTION_TEMPLATE](context, params) {
        await EventDescriptionTemplateService.update(
            context.state.project.slug,
            params.id,
            params
        );
        context.commit(UPDATE_EVENT_DESCRIPTION_TEMPLATE, params);
    },

    /**
     * remove event description template
     *
     * @param {*} context
     * @param {Number} slug event description template id
     */
    async [REMOVE_EVENT_DESCRIPTION_TEMPLATE](context, slug) {
        await EventDescriptionTemplateService.destroy(
            context.state.project.slug,
            slug
        );
        context.commit(REMOVE_EVENT_DESCRIPTION_TEMPLATE, slug);
    },
};

/**
 * Event description template Store Mutations
 */
const mutations = {
    /**
     * Set event description templates
     *
     * @param {*} state
     * @returns
     */
    [SET_EVENT_DESCRIPTION_TEMPLATES](state, eventDescriptionTemplates) {
        state.project.eventDescriptionTemplates = [
            ...eventDescriptionTemplates,
        ];
    },

    /**
     * Set current event description template
     *
     * @param {*} state
     * @param {Object} eventDescriptionTemplate
     */
    [SET_EVENT_DESCRIPTION_TEMPLATE](state, eventDescriptionTemplate) {
        state.project.eventDescriptionTemplate = eventDescriptionTemplate;
    },

    /**
     * Add event description template
     *
     * @param {*} state
     * @param {Number} eventDescriptionTemplate to append to eventDescriptionTemplates list
     */
    [ADD_EVENT_DESCRIPTION_TEMPLATE](state, eventDescriptionTemplate) {
        state.project.eventDescriptionTemplates = [
            ...(state.project.eventDescriptionTemplates ?? []),
            eventDescriptionTemplate,
        ];
    },

    /**
     * Update event description template
     *
     * @param {*} state
     */
    [UPDATE_EVENT_DESCRIPTION_TEMPLATE](state, params) {
        state.project.eventDescriptionTemplates =
            state.project.eventDescriptionTemplates.map((o) =>
                o.id == params.id ? { ...o, ...params } : o
            );
    },

    /**
     * remove event description template
     *
     * @param {*} context
     * @param {Number} params eventDescriptionTemplate id
     */
    [REMOVE_EVENT_DESCRIPTION_TEMPLATE](state, slug) {
        state.project.eventDescriptionTemplates =
            state.project.eventDescriptionTemplates.filter((o) => o.id != slug);
    },
};

/**
 * Event description template Store Getters
 */
const getters = {
    /**
     * Get list of event description templates
     *
     * @param {*} state
     * @returns
     */
    eventDescriptionTemplates: (state) =>
        state.project && Array.isArray(state.project.eventDescriptionTemplates)
            ? state.project.eventDescriptionTemplates
            : [],

    /**
     * Get current event description template
     *
     * @param {*} state
     * @returns
     */
    eventDescriptionTemplate: (state) =>
        state.project && state.project.eventDescriptionTemplate
            ? state.project.eventDescriptionTemplate
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
