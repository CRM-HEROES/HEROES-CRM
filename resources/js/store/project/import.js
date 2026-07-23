import importService from "@/apis/project/import";
import importGroupService from "@/apis/project/import/group";
import importLabelService from "@/apis/project/import/label";
import importUserService from "@/apis/project/import/user";
import importMappingService from "@/apis/project/import/mapping";
import importRoleService from "@/apis/project/import/role";

import {
    FETCH_IMPORTS,
    SET_IMPORTS,
    SET_IMPORT,
    ADD_IMPORT,
    SHOW_IMPORT,
    UPDATE_IMPORT,
    REMOVE_IMPORT,
    ADD_IMPORT_GROUP,
    REMOVE_IMPORT_GROUP,
    ADD_IMPORT_LABEL,
    REMOVE_IMPORT_LABEL,
    ADD_IMPORT_USER,
    REMOVE_IMPORT_USER,
    ADD_IMPORT_MAPPING_FIELD,
    ADD_IMPORT_MAPPING_META,
    ADD_IMPORT_MAPPING_CATEGORY,
    ADD_IMPORT_MAPPING_THREAD,
    ADD_IMPORT_MAPPING_EVENT,
    ADD_IMPORT_MAPPING_ORDER,
    ADD_IMPORT_MAPPING_SMS,
    ADD_IMPORT_MAPPING_INTERACTION,
    ADD_IMPORT_MAPPING_LINK,
    ADD_IMPORT_MAPPING_USER,
    REMOVE_IMPORT_MAPPING,
    ADD_IMPORT_ROLE,
    REMOVE_IMPORT_ROLE,
} from "@/actions/project/import";

/**
 * import Store state
 */
export const state = {
    imports: [],
    import: null,
};

/**
 * import Store Actions
 */
const actions = {
    /**
     * Fetch imports
     *
     * @param {*} context
     * @returns
     */
    async [FETCH_IMPORTS](context, params) {
        const { data } = await importService.get(
            context.state.project.slug,
            params
        );
        context.commit(SET_IMPORTS, data);
        return data;
    },

    /**
     * Show import
     *
     * @param {*} context
     * @param {Number} slug import id
     * @returns import
     */
    async [SHOW_IMPORT](context, slug) {
        const { data } = await importService.show(
            context.state.project.slug,
            slug
        );
        context.commit(SET_IMPORT, data);
        return data;
    },

    /**
     * Add import
     *
     * @param {*} context
     * @param {Object} params import import values
     * @returns import
     */
    async [ADD_IMPORT](context, params) {
        const { data } = await importService.create(
            context.state.project.slug,
            params
        );
        context.commit(ADD_IMPORT, data);
        return data;
    },

    /**
     * update import
     *
     * @param {*} context
     * @param {Object} params new import import values
     */
    async [UPDATE_IMPORT](context, params) {
        context.commit(UPDATE_IMPORT, params);
        await importService.update(
            context.state.project.slug,
            params.id,
            params
        );
    },

    /**
     * remove import
     *
     * @param {*} context
     * @param {Number} params import id
     * @returns import
     */
    async [REMOVE_IMPORT](context, { slug, params }) {
        await importService.destroy(context.state.project.slug, slug, params);
        context.commit(REMOVE_IMPORT, slug);
    },

    /**
     * Add import group
     *
     * @param {*} context
     * @param {Number} params import id
     * @returns import
     */
    async [ADD_IMPORT_GROUP](context, slug) {
        context.commit(ADD_IMPORT_GROUP, slug);
        await importGroupService.update(
            context.state.project.slug,
            context.state.project.import.id,
            slug
        );
    },

    /**
     * Remove import group
     *
     * @param {*} context
     * @param {Number} params import id
     * @returns import
     */
    async [REMOVE_IMPORT_GROUP](context, slug) {
        context.commit(REMOVE_IMPORT_GROUP, slug);
        await importGroupService.destroy(
            context.state.project.slug,
            context.state.project.import.id,
            slug
        );
    },

    /**
     * Add import group
     *
     * @param {*} context
     * @param {Number} params import id
     * @returns import
     */
    async [ADD_IMPORT_LABEL](context, slug) {
        context.commit(ADD_IMPORT_LABEL, slug);
        await importLabelService.update(
            context.state.project.slug,
            context.state.project.import.id,
            slug
        );
    },

    /**
     * Remove import group
     *
     * @param {*} context
     * @param {Number} params import id
     * @returns import
     */
    async [REMOVE_IMPORT_LABEL](context, slug) {
        context.commit(REMOVE_IMPORT_LABEL, slug);
        await importLabelService.destroy(
            context.state.project.slug,
            context.state.project.import.id,
            slug
        );
    },

    /**
     * Add import user
     *
     * @param {*} context
     * @param {Number} params import id
     * @returns import
     */
    async [ADD_IMPORT_USER](context, slug) {
        context.commit(ADD_IMPORT_USER, slug);
        await importUserService.update(
            context.state.project.slug,
            context.state.project.import.id,
            slug
        );
    },

    /**
     * Remove import user
     *
     * @param {*} context
     * @param {Number} params import id
     * @returns import
     */
    async [REMOVE_IMPORT_USER](context, slug) {
        context.commit(REMOVE_IMPORT_USER, slug);
        await importUserService.destroy(
            context.state.project.slug,
            context.state.project.import.id,
            slug
        );
    },
    async [ADD_IMPORT_ROLE](context, slug) {
        context.commit(ADD_IMPORT_ROLE, slug);
        await importRoleService.update(
            context.state.project.slug,
            context.state.project.import.id,
            slug
        );
    },

    async [REMOVE_IMPORT_ROLE](context, slug) {
        context.commit(REMOVE_IMPORT_ROLE, slug);
        await importRoleService.destroy(
            context.state.project.slug,
            context.state.project.import.id,
            slug
        );
    },

    /**
     * Add import mapping field
     *
     * @param {*} context
     * @param {Number} params import id
     * @returns import
     */
    async [ADD_IMPORT_MAPPING_FIELD](context, { column, field }) {
        context.commit(ADD_IMPORT_MAPPING_FIELD, { column, field });
        await importMappingService.updateField(
            context.state.project.slug,
            context.state.project.import.id,
            column,
            field
        );
    },

    /**
     * Add import mapping meta
     *
     * @param {*} context
     * @param {Number} params import id
     * @returns import
     */
    async [ADD_IMPORT_MAPPING_META](context, { column, meta }) {
        context.commit(ADD_IMPORT_MAPPING_META, {
            column,
            meta,
        });
        await importMappingService.updateMeta(
            context.state.project.slug,
            context.state.project.import.id,
            column,
            meta
        );
    },

    /**
     * Add import mapping category
     *
     * @param {*} context
     * @param {Number} params import id
     * @returns import
     */
    async [ADD_IMPORT_MAPPING_CATEGORY](context, { column, category }) {
        context.commit(ADD_IMPORT_MAPPING_CATEGORY, {
            column,
            category,
        });
        await importMappingService.updateCategory(
            context.state.project.slug,
            context.state.project.import.id,
            column,
            category
        );
    },

    /**
     * Add import mapping thread
     *
     * @param {*} context
     * @param {Number} params import id
     * @returns import
     */
    async [ADD_IMPORT_MAPPING_THREAD](context, { column, thread }) {
        context.commit(ADD_IMPORT_MAPPING_THREAD, {
            column,
            thread,
        });
        await importMappingService.updateThread(
            context.state.project.slug,
            context.state.project.import.id,
            column,
            thread
        );
    },

    /**
     * Add import mapping event
     *
     * @param {*} context
     * @param {Number} params import id
     * @returns import
     */
    async [ADD_IMPORT_MAPPING_EVENT](context, column) {
        context.commit(ADD_IMPORT_MAPPING_EVENT, column);
        await importMappingService.updateEvent(
            context.state.project.slug,
            context.state.project.import.id,
            column
        );
    },

    /**
     * Add import mapping order
     *
     * @param {*} context
     * @param {Number} params import id
     * @returns import
     */
    async [ADD_IMPORT_MAPPING_ORDER](context, column) {
        context.commit(ADD_IMPORT_MAPPING_ORDER, column);
        await importMappingService.updateOrder(
            context.state.project.slug,
            context.state.project.import.id,
            column
        );
    },

    /**
     * Add import mapping sms
     *
     * @param {*} context
     * @param {Number} params import id
     * @returns import
     */
    async [ADD_IMPORT_MAPPING_SMS](context, column) {
        context.commit(ADD_IMPORT_MAPPING_SMS, column);
        await importMappingService.updateSms(
            context.state.project.slug,
            context.state.project.import.id,
            column
        );
    },

    /**
     * Add import mapping interaction
     *
     * @param {*} context
     * @param {Number} params import id
     * @returns import
     */
    async [ADD_IMPORT_MAPPING_INTERACTION](context, column) {
        context.commit(ADD_IMPORT_MAPPING_INTERACTION, column);
        await importMappingService.updateInteraction(
            context.state.project.slug,
            context.state.project.import.id,
            column
        );
    },

    /**
     * Add import mapping link
     *
     * @param {*} context
     * @param {Number} params import id
     * @returns import
     */
    async [ADD_IMPORT_MAPPING_LINK](context, column) {
        context.commit(ADD_IMPORT_MAPPING_LINK, column);
        await importMappingService.updateLink(
            context.state.project.slug,
            context.state.project.import.id,
            column
        );
    },

    /**
     * Add import mapping user
     *
     * @param {*} context
     * @param {Number} params import id
     * @returns import
     */
    async [ADD_IMPORT_MAPPING_USER](context, column) {
        context.commit(ADD_IMPORT_MAPPING_USER, column);
        await importMappingService.updateUser(
            context.state.project.slug,
            context.state.project.import.id,
            column
        );
    },

    /**
     * Remove import group
     *
     * @param {*} context
     * @param {Number} params import id
     * @returns import
     */
    async [REMOVE_IMPORT_MAPPING](context, mapping) {
        context.commit(REMOVE_IMPORT_MAPPING, mapping);
        await importMappingService.destroy(
            context.state.project.slug,
            context.state.project.import.id,
            mapping
        );
    },
};

/**
 * import Store Mutations
 */
const mutations = {
    /**
     * Set imports
     *
     * @param {*} state
     * @returns
     */
    [SET_IMPORTS](state, imports) {
        state.project.imports = [...imports];
    },

    /**
     * Set current import
     *
     * @param {*} state
     * @param {Object} import
     */
    [SET_IMPORT](state, prospectImport) {
        state.project.import = prospectImport;
    },

    /**
     * Add import
     *
     * @param {*} state
     * @param {Number} import to append to imports list
     */
    [ADD_IMPORT](state, prospectImport) {
        state.project.imports = [
            ...(state.project.imports ?? []),
            prospectImport,
        ];
    },

    /**
     * Update import
     *
     * @param {*} state
     */
    [UPDATE_IMPORT](state, params) {
        state.project.imports = state.project.imports.map((o) =>
            o.id == params.id ? { ...o, ...params } : o
        );

        if (state.project.import.id == params.id) {
            state.project.import = { ...state.project.import, ...params };
        }
    },

    /**
     * remove import
     *
     * @param {*} context
     * @param {Number} params import id
     */
    [REMOVE_IMPORT](state, slug) {
        state.project.imports = state.project.imports.filter(
            (o) => o.id != slug
        );
    },

    /**
     * Add import group
     *
     * @param {*} context
     * @param {Number} params import id
     */
    [ADD_IMPORT_GROUP](state, slug) {
        state.project.import.groups = [
            ...(state.project.import.groups ?? []),
            slug,
        ];
    },

    /**
     * Remove import group
     *
     * @param {*} context
     * @param {Number} params import id
     */
    [REMOVE_IMPORT_GROUP](state, slug) {
        state.project.import.groups = state.project.import.groups.filter(
            (o) => o != slug
        );
    },

    /**
     * Add import label
     *
     * @param {*} context
     * @param {Number} params import id
     */
    [ADD_IMPORT_LABEL](state, slug) {
        state.project.import.labels = [
            ...(state.project.import.labels ?? []),
            slug,
        ];
    },

    /**
     * Remove import group
     *
     * @param {*} context
     * @param {Number} params import id
     */
    [REMOVE_IMPORT_LABEL](state, slug) {
        state.project.import.labels = state.project.import.labels.filter(
            (o) => o != slug
        );
    },

    /**
     * Add import user
     *
     * @param {*} context
     * @param {Number} params import id
     */
    [ADD_IMPORT_USER](state, slug) {
        state.project.import.users = [
            ...(state.project.import.users ?? []),
            slug,
        ];
    },

    /**
     * Remove import user
     *
     * @param {*} context
     * @param {Number} params import id
     */
    [REMOVE_IMPORT_USER](state, slug) {
        state.project.import.users = state.project.import.users.filter(
            (o) => o != slug
        );
    },
    [ADD_IMPORT_ROLE](state, slug) {
        state.project.import.roles = [
            ...(state.project.import.roles ?? []),
            slug,
        ];
    },

    [REMOVE_IMPORT_ROLE](state, slug) {
        state.project.import.roles = state.project.import.roles.filter(
            (o) => o != slug
        );
    },

    /**
     * Add import mapping
     *
     * @param {*} context
     * @param {Number} params import id
     */
    [ADD_IMPORT_MAPPING_FIELD](state, { column, field }) {
        if (!state.project.import.mapping) {
            state.project.import.mapping = new Array(
                state.project.import.headers.length
            ).fill(null);
        }

        state.project.import.mapping = state.project.import.mapping.map(
            (o, i) => (i == column ? field : o)
        );

        state.project.imports = state.project.imports.map((o) =>
            o.id == state.project.import.id ? state.project.import : o
        );
    },

    /**
     * Add import mapping
     *
     * @param {*} context
     * @param {Number} params import id
     */
    [ADD_IMPORT_MAPPING_META](state, { column, meta }) {
        if (!state.project.import.mapping) {
            state.project.import.mapping = new Array(
                state.project.import.headers.length
            ).fill(null);
        }

        state.project.import.mapping = state.project.import.mapping.map(
            (o, i) => (i == column ? "meta->" + meta : o)
        );

        state.project.imports = state.project.imports.map((o) =>
            o.id == state.project.import.id ? state.project.import : o
        );
    },

    /**
     * Add import mapping
     *
     * @param {*} context
     * @param {Number} params import id
     */
    [ADD_IMPORT_MAPPING_CATEGORY](state, { column, category }) {
        if (!state.project.import.mapping) {
            state.project.import.mapping = new Array(
                state.project.import.headers.length
            ).fill(null);
        }

        state.project.import.mapping = state.project.import.mapping.map(
            (o, i) => (i == column ? "category->" + category : o)
        );

        state.project.imports = state.project.imports.map((o) =>
            o.id == state.project.import.id ? state.project.import : o
        );
    },

    /**
     * Add import mapping
     *
     * @param {*} context
     * @param {Number} params import id
     */
    [ADD_IMPORT_MAPPING_THREAD](state, { column, thread }) {
        if (!state.project.import.mapping) {
            state.project.import.mapping = new Array(
                state.project.import.headers.length
            ).fill(null);
        }

        state.project.import.mapping = state.project.import.mapping.map(
            (o, i) => (i == column ? "thread->" + thread : o)
        );

        state.project.imports = state.project.imports.map((o) =>
            o.id == state.project.import.id ? state.project.import : o
        );
    },

    /**
     * Add import mapping
     *
     * @param {*} context
     * @param {Number} params import id
     */
    [ADD_IMPORT_MAPPING_EVENT](state, column) {
        if (!state.project.import.mapping) {
            state.project.import.mapping = new Array(
                state.project.import.headers.length
            ).fill(null);
        }

        state.project.import.mapping = state.project.import.mapping.map(
            (o, i) => (i == column ? "events" : o)
        );

        state.project.imports = state.project.imports.map((o) =>
            o.id == state.project.import.id ? state.project.import : o
        );
    },

    /**
     * Add import mapping
     *
     * @param {*} context
     * @param {Number} params import id
     */
    [ADD_IMPORT_MAPPING_ORDER](state, column) {
        if (!state.project.import.mapping) {
            state.project.import.mapping = new Array(
                state.project.import.headers.length
            ).fill(null);
        }

        state.project.import.mapping = state.project.import.mapping.map(
            (o, i) => (i == column ? "orders" : o)
        );

        state.project.imports = state.project.imports.map((o) =>
            o.id == state.project.import.id ? state.project.import : o
        );
    },

    /**
     * Add import mapping
     *
     * @param {*} context
     * @param {Number} params import id
     */
    [ADD_IMPORT_MAPPING_SMS](state, column) {
        if (!state.project.import.mapping) {
            state.project.import.mapping = new Array(
                state.project.import.headers.length
            ).fill(null);
        }

        state.project.import.mapping = state.project.import.mapping.map(
            (o, i) => (i == column ? "sms" : o)
        );

        state.project.imports = state.project.imports.map((o) =>
            o.id == state.project.import.id ? state.project.import : o
        );
    },

    /**
     * Add import mapping
     *
     * @param {*} context
     * @param {Number} params import id
     */
    [ADD_IMPORT_MAPPING_INTERACTION](state, column) {
        if (!state.project.import.mapping) {
            state.project.import.mapping = new Array(
                state.project.import.headers.length
            ).fill(null);
        }

        state.project.import.mapping = state.project.import.mapping.map(
            (o, i) => (i == column ? "interactions" : o)
        );

        state.project.imports = state.project.imports.map((o) =>
            o.id == state.project.import.id ? state.project.import : o
        );
    },

    /**
     * Add import mapping
     *
     * @param {*} context
     * @param {Number} params import id
     */
    [ADD_IMPORT_MAPPING_LINK](state, column) {
        if (!state.project.import.mapping) {
            state.project.import.mapping = new Array(
                state.project.import.headers.length
            ).fill(null);
        }

        state.project.import.mapping = state.project.import.mapping.map(
            (o, i) => (i == column ? "links" : o)
        );

        state.project.imports = state.project.imports.map((o) =>
            o.id == state.project.import.id ? state.project.import : o
        );
    },

    /**
     * Add import mapping
     *
     * @param {*} context
     * @param {Number} params import id
     */
    [ADD_IMPORT_MAPPING_USER](state, column) {
        if (!state.project.import.mapping) {
            state.project.import.mapping = new Array(
                state.project.import.headers.length
            ).fill(null);
        }

        state.project.import.mapping = state.project.import.mapping.map(
            (o, i) => (i == column ? "users" : o)
        );

        state.project.imports = state.project.imports.map((o) =>
            o.id == state.project.import.id ? state.project.import : o
        );
    },

    /**
     * Remove import mapping
     *
     * @param {*} context
     * @param {Number} params import id
     */
    [REMOVE_IMPORT_MAPPING](state, index) {
        state.project.import.mapping = state.project.import.mapping.map(
            (o, i) => (i == index ? null : o)
        );

        state.project.imports = state.project.imports.map((o) =>
            o.id == state.project.import.id ? state.project.import : o
        );
    },
};

/**
 * import Store Getters
 */
const getters = {
    /**
     * Get list of imports
     *
     * @param {*} state
     * @returns
     */
    imports: (state) =>
        state.project && state.project.imports ? state.project.imports : [],

    /**
     * Get current import
     *
     * @param {*} state
     * @returns
     */
    prospectImport: (state) =>
        state.project && state.project.import ? state.project.import : null,
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
