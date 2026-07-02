<template>
    <div :class="['hc-table', projectsOptions ? 'hc-table-show-options' : '']">
        <table v-if="this.columns.length > 0">
            <draggable
                tag="thead"
                :list="notFixedColumns"
                item-key="key"
                :disabled="!headerMovable"
                @end="notFixedColumnMoved"
            >
                <template #header>
                    <th class="fixed">
                        <label class="hc-table-selector">
                            <input type="checkbox" v-model="selectAll" />
                            <span></span>
                        </label>

                        <div class="hc-table-row-options">
                            <a></a>
                            <a></a>
                        </div>

                        <draggable
                            tag="div"
                            :list="fixedColumns"
                            class="hc-table-fixed-headers"
                            item-key="key"
                            :disabled="!headerMovable"
                            @end="fixedColumnMoved"
                        >
                            <template #item="{ element }">
                                <header-cell tag="div" :column="element" />
                            </template>
                        </draggable>
                    </th>
                </template>

                <template #item="{ element }">
                    <header-cell :column="element" />
                </template>
            </draggable>

            <tbody>
                <row
                    v-for="(project, index) in projects"
                    :key="project.id"
                    :index="index"
                    :project="project"
                    :fixed-columns="fixedColumns"
                    :not-fixed-columns="notFixedColumns"
                />
            </tbody>
        </table>

        <add-project-modal />
        <!--loading :loading="loading" /-->
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { OPEN_SLIDE } from "@/actions/slide";
import { GET_USER_SETTING, UPDATE_USER_SETTING } from "@/actions/user/setting";
import { FETCH_FIELDS } from "@/actions/project/field";
import { FETCH_CATEGORIES } from "@/actions/project/category";
import {
    FETCH_PROJECTS,
    UPDATE_SELECTED_PROJECTS,
    INIT_PROJECT_PARAMS,
    SET_PROJECT_PARAMS,
    SET_PROJECTS_PAGE,
    SET_PROJECTS_COUNT,
    SET_PROJECTS_FIELDS,
    SET_PROJECTS_SORT_ORDER,
    SET_PROJECTS_SORT_BY,
} from "@/actions/project";

// Components
import Row from "./Row.vue";
import HeaderCell from "./HeaderCell.vue";

import AddProjectModal from "@/components/project/add/Modal.vue";

export default {
    components: {
        Row,
        HeaderCell,
        AddProjectModal,
    },

    data() {
        return {
            loading: false,
            headerMovable: deviceType() == "desktop",
            fields: [
                {
                    slug: "name",
                    name: "Nom",
                },
                {
                    slug: "email",
                    name: "Email",
                },
                {
                    slug: "street",
                    name: "Rue",
                },
                {
                    slug: "street_bis",
                    name: "Rue 2",
                },
                {
                    slug: "postal_code",
                    name: "CP",
                },
                {
                    slug: "country",
                    name: "Pays",
                },
                {
                    slug: "city",
                    name: "Ville",
                },
            ],
        };
    },

    created() {
        this.updateParamsFromUrl();
        this.getColumns();
        this.getProjects();
    },

    methods: {
        /**
         * Update params from URL
         */
        updateParamsFromUrl() {
            store.commit(INIT_PROJECT_PARAMS);
            store.commit(SET_PROJECTS_FIELDS, null);
            store.commit(SET_PROJECTS_SORT_BY, "id");
            store.commit(SET_PROJECTS_SORT_ORDER, "desc");

            const url = new URL(window.location.href);
            const searchParams = new URLSearchParams(url.search);
            const filters = {};

            let projectsCount = 50;

            searchParams.forEach(function (value, param) {
                if (param == "page") {
                    store.commit(SET_PROJECTS_PAGE, parseInt(value));
                } else if (param == "count") {
                    projectsCount = parseInt(value);
                } else if (param == "fields") {
                    store.commit(SET_PROJECTS_FIELDS, value);
                } else if (param == "sortOrder") {
                    store.commit(SET_PROJECTS_SORT_ORDER, value);
                } else if (param == "sortBy") {
                    store.commit(SET_PROJECTS_SORT_BY, value);
                } else if (param == "filters") {
                    try {
                        value = JSON.parse(value);

                        for (let key in value) {
                            filters[key] = value[key];
                        }
                    } catch (e) {}
                }
            });

            store.commit(SET_PROJECT_PARAMS, filters);
            store.commit(SET_PROJECTS_COUNT, projectsCount);
        },

        /**
         * Get user columns
         */
        getColumns() {
            store.dispatch(GET_USER_SETTING, "projects-table");
        },

        /**
         * Get project fields
         */
        getProjectFields() {
            store.dispatch(FETCH_FIELDS);
        },

        /**
         * Get project categories
         */
        getProjectCategories() {
            store.dispatch(FETCH_CATEGORIES);
        },

        /**
         * Import projects (XLSX, CSV)
         */
        importProjects() {
            store.commit(OPEN_SLIDE, "import");
            store.commit(OPEN_MODAL, "import-add");
        },

        /**
         * Get project projects
         */
        async getProjects() {
            this.loading = true;

            try {
                await store.dispatch(FETCH_PROJECTS);
            } finally {
                this.loading = false;
            }

            // Show add import modal
            // when project does not
            // have project
            if (
                !this.projectsParamExists() &&
                this.projects.length == 0 &&
                this.projectsPage == 1
            ) {
                this.importProjects();
            }
        },

        /**
         *
         * @param {*} e
         */
        fixedColumnMoved(e) {
            this.columnMoved(e, true);
        },

        /**
         *
         * @param {*} e
         */
        notFixedColumnMoved(e) {
            this.columnMoved(e, false);
        },

        /**
         *
         * @param {*} e
         * @param {*} fixed
         */
        columnMoved(e, fixed) {
            let settings = this.userSettingsProjectsTable;
            let newSettings;

            const oldIndex = fixed
                ? this.columns.filter((c) => c.fixed)[e.oldDraggableIndex].index
                : this.columns.filter((c) => !c.fixed)[e.oldDraggableIndex]
                      .index;
            const newIndex = fixed
                ? this.columns.filter((c) => c.fixed)[e.newDraggableIndex].index
                : this.columns.filter((c) => !c.fixed)[e.newDraggableIndex]
                      .index;

            if (oldIndex < newIndex) {
                newSettings = [
                    ...settings.slice(0, oldIndex),
                    ...settings.slice(oldIndex + 1, newIndex + 1),
                    settings[oldIndex],
                    ...settings.slice(newIndex + 1),
                ];
            } else if (oldIndex > newIndex) {
                newSettings = [
                    ...settings.slice(0, newIndex),
                    settings[oldIndex],
                    ...settings.slice(newIndex, oldIndex),
                    ...settings.slice(oldIndex + 1),
                ];
            }

            if (newSettings) {
                store.dispatch(UPDATE_USER_SETTING, {
                    key: "projects-table",
                    value: newSettings,
                });
            }
        },

        /**
         * Filter by file
         */
        filterFile() {
            store.commit(OPEN_SLIDE, "projects-table-filter-file");
        },

        /**
         * Filter by message
         */
        filterMessage() {
            store.commit(OPEN_SLIDE, "projects-table-filter-message");
        },

        /**
         * Filter by order
         */
        filterOrder() {
            store.commit(OPEN_SLIDE, "projects-table-filter-order");
        },

        /**
         * Filter by interaction
         */
        filterInteraction() {
            store.commit(OPEN_SLIDE, "projects-table-filter-interaction");
        },

        /**
         * Filter by sms
         */
        filterSms() {
            store.commit(OPEN_SLIDE, "projects-table-filter-sms");
        },

        /**
         * Filter by questionnaire
         */
        filterQuestionnaire() {
            store.commit(OPEN_SLIDE, "projects-table-filter-questionnaire");
        },

        /**
         * Check if option (file, message, ...) is filtered
         * @param {*} option
         */
        isOptionFiltered(option) {
            // By files
            if (option == "file") {
                return (
                    this.projectsParamExists("withFiles") ||
                    this.projectsParamExists("withoutFiles")
                );
                // By messages
            } else if (option == "message") {
                return (
                    this.projectsParamExists("withMessages") ||
                    this.projectsParamExists("withoutMessages")
                );
                // By orders
            } else if (option == "order") {
                return (
                    this.projectsParamExists("withOrders") ||
                    this.projectsParamExists("withoutOrders")
                );
                // By interactions
            } else if (option == "interaction") {
                return (
                    this.projectsParamExists("withInteractions") ||
                    this.projectsParamExists("withoutInteractions")
                );
                // By sms
            } else if (option == "sms") {
                return (
                    this.projectsParamExists("withSms") ||
                    this.projectsParamExists("withoutSms")
                );
            }

            return false;
        },
    },

    computed: {
        ...mapGetters([
            "userSettingsProjectsTable",
            "projects",
            "projectsSelected",
            "projectsOptions",
            "projectsParamExists",
            "projectsParamsUrl",
            "projectsPage",
            "projectsCount",
        ]),

        /**
         *
         */
        selectAll: {
            get() {
                return this.projects.length == this.projectsSelected.length;
            },
            set(value) {
                store.commit(
                    UPDATE_SELECTED_PROJECTS,
                    value ? this.projects.map((p) => p.id) : []
                );
            },
        },

        /**
         * Columns
         */
        columns: function () {
            return this.userSettingsProjectsTable
                .map((column, index) => {
                    const key = column.key;
                    let type = "list";
                    let category = key;
                    let id = key;
                    let name;

                    // Project
                    if (key == "active-users") {
                        name = this.$t(
                            "project.table.column.others.active_users"
                        );
                        // Event
                    } else if (key == "users") {
                        name = this.$t(
                            "project.table.column.others.affected_users"
                        );
                        // Event
                    } else if (key == "groups") {
                        name = this.$t(
                            "project.table.column.others.affected_groups"
                        );
                        // Event
                    } else if (key.indexOf("meta->") == 0) {
                        return null;
                    } else {
                        const slug = key;

                        const field = this.fields.find(
                            (field) => !field.meta && field.slug == slug
                        );

                        if (!field) {
                            return null;
                        }

                        name = field.name;
                        type = field.type;
                        category = "default";
                    }

                    return {
                        ...column,
                        name: name,
                        type: type,
                        index: index,
                        id: id,
                        category: category,
                    };
                })
                .filter((column) => column);
        },

        /**
         *
         */
        fixedColumns() {
            return this.columns.filter((c) => c.fixed);
        },

        /**
         *
         */
        notFixedColumns() {
            return this.columns.filter((c) => !c.fixed);
        },
    },

    watch: {
        projectsParamsUrl(newValue) {
            let url =
                "?page=" + this.projectsPage + "&count=" + this.projectsCount;

            if (newValue) {
                url += "&filters=" + newValue;
            }

            history.pushState(null, null, url);
        },
    },
};
</script>
