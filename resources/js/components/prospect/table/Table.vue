<template>
    <div
        :class="[
            'hc-table',
            prospectsOptions ? 'hc-table-show-options' : '',
            'max-lines-per-row-' + projectUserSettingsProspectsMaxRowLines,
        ]"
    >
        <table v-if="this.columns.length > 0">
            <header-row
                :columns="columns"
                :fixed-columns="fixedColumns"
                :not-fixed-columns="notFixedColumns"
            />

            <tbody>
                <row
                    v-for="(prospect, index) in prospects"
                    :key="prospect.id"
                    :index="index"
                    :prospect="prospect"
                    :fixed-columns="fixedColumns"
                    :not-fixed-columns="notFixedColumns"
                />

                <footer-row
                    :fixed-columns="fixedColumns"
                    :not-fixed-columns="notFixedColumns"
                />
            </tbody>
        </table>

        <loading :loading="loading" />
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { OPEN_SLIDE } from "@/actions/slide";

import {
    FETCH_PROSPECTS,
    INIT_PROSPECT_PARAMS,
    SET_PROSPECT_PARAMS,
    SET_PROSPECTS_PAGE,
    SET_PROSPECTS_COUNT,
    SET_PROSPECTS_FIELDS,
    SET_PROSPECTS_SORT_ORDER,
    SET_PROSPECTS_SORT_BY,
} from "@/actions/project/prospect";

// Components
import HeaderRow from "./Header.vue";
import FooterRow from "./Footer.vue";
import Row from "./Row.vue";

export default {
    components: {
        HeaderRow,
        FooterRow,
        Row,
    },

    data() {
        return {
            loading: false,
        };
    },

    created() {
        this.updateParamsFromUrl();
        this.showTable();
    },

    methods: {
        /**
         * Update params from URL
         */
        updateParamsFromUrl() {
            store.commit(INIT_PROSPECT_PARAMS);
            store.commit(SET_PROSPECTS_FIELDS, null);
            // store.commit(SET_PROSPECTS_SORT_BY, "id");
            // store.commit(SET_PROSPECTS_SORT_ORDER, "desc");

            const url = new URL(window.location.href);
            const searchParams = new URLSearchParams(url.search);
            let filters = {};

            let prospectsCount = 50;

            searchParams.forEach(function (value, param) {
                if (param == "page") {
                    store.commit(SET_PROSPECTS_PAGE, parseInt(value));
                } else if (param == "count") {
                    prospectsCount = parseInt(value);
                } else if (param == "fields") {
                    store.commit(SET_PROSPECTS_FIELDS, value);
                } else if (param == "sortOrder") {
                    store.commit(SET_PROSPECTS_SORT_ORDER, value);
                } else if (param == "sortBy") {
                    store.commit(SET_PROSPECTS_SORT_BY, value);
                } else if (param == "filters") {
                    try {
                        value = JSON.parse(value);

                        for (let key in value) {
                            filters[key] = value[key];
                        }
                    } catch (e) {}
                }
            });

            if (
                Object.keys(filters).length == 0 &&
                this.projectUserSettingsProspectsDefaultMenu
            ) {
                const menu = this.menus.find(
                    (m) => m.id == this.projectUserSettingsProspectsDefaultMenu
                );

                if (menu) {
                    filters = menu.filters;
                }
            }

            store.commit(SET_PROSPECT_PARAMS, filters);
            store.commit(SET_PROSPECTS_COUNT, prospectsCount);
        },

        /**
         * Import prospects (XLSX, CSV)
         */
        importProspects() {
            store.commit(OPEN_SLIDE, "import");
            store.commit(OPEN_MODAL, "import-add");
        },

        /**
         *
         */
        showTable() {
            this.loading = true;

            Promise.all([this.getProspects()])
                .then((results) => {
                    // Show add import modal
                    // when project does not
                    // have prospect
                    if (
                        !this.prospectsParamExists() &&
                        this.prospects.length == 0 &&
                        this.prospectsPage == 1
                    ) {
                        this.importProspects();
                    }

                    this.$nextTick(() => {
                        this.scrollToViwedProspect();
                    });
                })
                .catch((error) => {
                    console.error("An error occurred:", error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        /**
         * Get project prospects
         */
        scrollToViwedProspect() {
            if (!this.viewedProspect) {
                return;
            }

            const row = document.getElementById(
                "prospect-" + this.viewedProspect
            );

            if (!row) {
                return;
            }

            row.scrollIntoView();
        },

        /**
         * Get project prospects
         */
        getProspects() {
            return store.dispatch(FETCH_PROSPECTS);
        },

        /**
         *
         */
        updateUrl() {
            this.$router.push("?" + this.urlParams);
            // history.pushState(null, null, "?" + this.urlParams);
        },
    },

    watch: {
        urlParams() {
            this.updateUrl();
        },

        projectUserSettingsProspectsDefaultMenu() {
            if (
                this.projectUserSettingsProspectsDefaultMenu &&
                !this.prospectsParamExists()
            ) {
                const menu = this.menus.find(
                    (m) => m.id == this.projectUserSettingsProspectsDefaultMenu
                );

                if (menu) {
                    store.commit(SET_PROSPECT_PARAMS, menu.filters);
                    this.getProspects();
                }
            }
        },
    },

    computed: {
        ...mapGetters([
            "projectUserSettingsProspectsTable",
            "fields",
            "categories",
            "calendars",
            "threads",
            "prospects",
            "project",
            "prospectsOptions",
            // "prospectsMaxLinesPerRow",
            "projectUserSettingsProspectsMaxRowLines",
            "prospectsParamExists",
            "prospectsParamsUrl",
            "prospectsPage",
            "prospectsCount",
            "viewedProspect",
            "menus",
            "projectUserSettingsProspectsDefaultMenu",
            "can",
            "canEvent",
        ]),

        /**
         * Columns
         */
        columns: function () {
            return this.projectUserSettingsProspectsTable
                .map((column, index) => {
                    const key = column.key;
                    let type = "list";
                    let category = key;
                    let id = key;
                    let name;
                    let defaultValue = null;
                    let required = false;
                    let format = null;

                    // Users
                    if (key == "users") {
                        name = this.$t(
                            "prospect.table.column.others.affected_users"
                        );
                        // Groups
                    } else if (key == "groups") {
                        name = this.$t(
                            "prospect.table.column.others.affected_groups"
                        );
                        // Orders
                    } else if (key == "orders") {
                        name = this.$t("prospect.table.column.others.orders");
                        // Import
                    } else if (key == "creator") {
                        name = this.$t("prospect.table.column.others.creator");
                        // Import
                    } else if (key == "import") {
                        name = this.$t("prospect.table.column.others.import");
                        // Event
                    } else if (key == "pipedrive-accounts") {
                        name = "Pipedrive";
                        // Event
                    } else if (key == "events") {
                        if (
                            !this.can("all.prospect.event.view") &&
                            !this.canEvent
                        ) {
                            return null;
                        }

                        name =
                            this.project.slug == "professionnel"
                                ? "Missions"
                                : this.$t(
                                      "prospect.table.column.others.appointments"
                                  );
                        // Interaction
                    } else if (key == "interactions") {
                        if (!this.can("all.prospect.interaction.view")) {
                            return null;
                        }

                        name = this.$t("prospect.table.column.others.calls");
                        // Sms
                    } else if (key == "sms") {
                        if (!this.can("all.prospect.sms.view")) {
                            return null;
                        }

                        name = this.$t("prospect.table.column.others.sms");
                        // Event
                    } else if (key.indexOf("calendar->") == 0) {
                        const calendarId = key.replace("calendar->", "");

                        const c = this.calendars.find(
                            (calendar) => calendar.id == calendarId
                        );

                        if (!c) {
                            return null;
                        }

                        name = c.name;
                        category = "events";
                        id = c.id;
                        // Label
                    } else if (key.indexOf("event->") == 0) {
                        let field = key.replace("event->", "");

                        if (field.indexOf("meta->") == 0) {
                            field = field.replace("meta->", "");
                            category = "event-meta-field";

                            const f = this.fields.find(
                                (f) =>
                                    f.for == "event" &&
                                    f.meta &&
                                    f.slug == field
                            );

                            if (!f) {
                                return null;
                            }

                            name = f.name;
                        } else {
                            category = "event-field";
                            name = this.$t(
                                "prospect.table.column.event.fields." + field
                            );
                        }

                        id = field;
                        // Label
                    } else if (key.indexOf("category->") == 0) {
                        const categoryId = key.replace("category->", "");

                        const c = this.categories.find(
                            (category) => category.id == categoryId
                        );

                        if (!c) {
                            return null;
                        }

                        name = c.name;
                        category = "category";
                        id = c.id;
                        // Meta field
                    } else if (key.indexOf("thread->") == 0) {
                        const threadId = key.replace("thread->", "");

                        const c = this.threads.find(
                            (thread) => thread.id == threadId
                        );

                        if (!c) {
                            return null;
                        }

                        name = c.name;
                        category = "thread";
                        id = c.id;
                        // Meta field
                    } else if (key.indexOf("meta->") == 0) {
                        const slug = key.replace("meta->", "");

                        const field = this.fields.find(
                            (field) =>
                                field.for == "prospect" &&
                                field.meta &&
                                field.slug == slug
                        );

                        if (!field) {
                            return null;
                        }

                        name = field.name;
                        type = field.type;
                        defaultValue = field.default_value;
                        required = field.required;
                        format = field.format;
                        category = "meta";
                        id = field.slug;
                    } else {
                        const slug = key;

                        const field = this.fields.find(
                            (field) =>
                                field.for == "prospect" &&
                                !field.meta &&
                                field.slug == slug
                        );

                        if (!field) {
                            return null;
                        }

                        name = field.name;
                        type = field.type;
                        defaultValue = field.default_value;
                        required = field.required;
                        format = field.format;
                        category = "default";
                    }

                    return {
                        ...column,
                        name: name,
                        type: type,
                        index: index,
                        id: id,
                        category: category,
                        default_value: defaultValue,
                        required: required,
                        format: format,
                    };
                })
                .filter((column) => column);
        },

        /**
         *
         */
        fixedColumns() {
            return this.columns.filter((c) => c.fixed && !c.hidden);
        },

        /**
         *
         */
        notFixedColumns() {
            return this.columns.filter((c) => !c.fixed && !c.hidden);
        },

        /**
         *
         */
        urlParams() {
            let url =
                "page=" + this.prospectsPage + "&count=" + this.prospectsCount;

            if (this.prospectsParamsUrl) {
                url += "&filters=" + this.prospectsParamsUrl;
            }

            return url;
        },
    },
};
</script>
