<template>
    <card-menu-list>
        <template v-if="bulkDisabled">
            <card-menu
                v-if="filtersActivated"
                icon="fa fa-filter"
                :label="$t('project.table.footer.init_filters')"
                title="Réinitialiser les filtres"
                style="background-color: #e6effd"
                color="#1a73e8"
                @click="initParams"
            />
            <card-menu
                icon="fa fa-ellipsis-h"
                :label="$t('project.table.footer.toggle_project_menus')"
                @click="toggleProjectsOptions"
            />
            <div class="hc-projects-table-footer-select">
                <select v-model="count">
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                    <option value="300">300</option>
                    <option value="400">400</option>
                    <option value="500">500</option>
                </select>
                <loading :loading="settingProjectsCount" />
            </div>
            <card-menu
                icon="fa fa-plus"
                :label="$t('project.table.footer.add_project')"
                @click="addProject"
                color="#12a0f3"
            />
            <card-menu
                :disabled="fetchingPreviousPage || projectsPage <= 1"
                icon="fa fa-caret-left"
                :label="$t('project.table.footer.previous')"
                @click="previousPage"
            >
                <loading :loading="fetchingPreviousPage" />
            </card-menu>
            <card-menu
                :disabled="fetchingNextPage || projects.length < projectsCount"
                icon="fa fa-caret-right"
                :label="$t('project.table.footer.next')"
                @click="nextPage"
            >
                <loading :loading="fetchingNextPage" />
            </card-menu>
        </template>

        <template v-else>
            <card-menu
                icon="fa fa-check-square"
                :label="$t('project.table.footer.deselect')"
                @click="bulkDeselect"
            />
            <card-menu
                icon="fa fa-trash"
                :label="$t('project.table.footer.bulk_delete')"
                color="#d9402c"
                @click="bulkRemove"
            >
                <loading :loading="bulkingRemove" />
            </card-menu>
        </template>
        <!--card-menu icon="fa fa-search" :label="$t('project.table.footer.search_and_replace')" /-->
    </card-menu-list>
</template>

<style>
.hc-projects-table-footer-select {
    position: relative;
    width: unset !important;
    min-width: unset !important;
}

.hc-projects-table-footer-select > select {
    border: 1px solid #dddddd;
    background: none;
    padding: 3px 20px 3px 8px;
    border-radius: 7px;
    font-size: 12px;
    -webkit-appearance: none;
    -moz-appearance: none;
    height: 40px;
    width: auto;
}

.hc-projects-table-footer-select:after {
    content: "";
    position: absolute;
    right: 7px;
    top: 17px;
    width: 0;
    height: 0;
    border-radius: 2px;
    border-top: 5px solid #555555;
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { OPEN_MODAL } from "@/actions/modal";

import {
    BULK_REMOVE_PROJECT,
    FETCH_PROJECTS,
    UPDATE_SELECTED_PROJECTS,
    INIT_PROJECT_PARAMS,
    SET_PROJECTS_PAGE,
    TOGGLE_PROJECTS_OPTIONS,
    TOGGLE_PROJECTS_MENUS,
    SET_PROJECTS_COUNT,
} from "@/actions/project";

export default {
    data() {
        return {
            createdProjects: 0,
            bulkingRemove: false,
            bulkingRestore: false,
            bulkingProcessed: false,
            bulkingNotProcessed: false,
            fetchingPreviousPage: false,
            fetchingNextPage: false,
            settingProjectsCount: false,
        };
    },

    methods: {
        /**
         * Deselect selected project
         */
        bulkDeselect() {
            store.commit(UPDATE_SELECTED_PROJECTS, []);
        },

        /**
         * Store new project
         */
        async addProject() {
            store.commit(OPEN_MODAL, "project-add");
        },

        /**
         * Add other columns
         * in the table
         */
        addColumn() {
            store.commit(OPEN_MODAL, "projects-table-add-column");
        },

        /**
         * Previous page
         */
        async previousPage() {
            store.commit(SET_PROJECTS_PAGE, parseInt(this.projectsPage) - 1);

            // Refresh projects list
            this.fetchingPreviousPage = true;

            try {
                await store.dispatch(FETCH_PROJECTS);
            } finally {
                this.fetchingPreviousPage = false;
            }
        },

        /**
         * Next page
         */
        async nextPage() {
            store.commit(SET_PROJECTS_PAGE, parseInt(this.projectsPage) + 1);

            // Refresh projects list
            this.fetchingNextPage = true;

            try {
                await store.dispatch(FETCH_PROJECTS);
            } finally {
                this.fetchingNextPage = false;
            }
        },

        /**
         * Toggle projects menus
         */
        toggleProjectsMenus() {
            store.commit(TOGGLE_PROJECTS_MENUS);
        },

        /**
         * Init filters
         */
        initParams() {
            store.commit(INIT_PROJECT_PARAMS);
            store.dispatch(FETCH_PROJECTS);
        },

        /**
         * Toggle projects options
         * Show or hide each project menus (sms, phone, order, ..)
         * from the projects table
         */
        toggleProjectsOptions() {
            store.commit(TOGGLE_PROJECTS_OPTIONS);
        },

        /**
         * Remove multiple projects
         */
        bulkRemove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.bulkingRemove = true;

                // Selected projects
                const projectsSelected = this.projectsSelected;
                store.commit(UPDATE_SELECTED_PROJECTS, []);

                // Dispatch bulk remove

                try {
                    await store.dispatch(BULK_REMOVE_PROJECT, projectsSelected);

                    // Refresh projects list
                    store.dispatch(FETCH_PROJECTS);
                } finally {
                    this.bulkingRemove = false;
                }
            });
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "projectsSelected",
            "projects",
            "projectsParamExists",
            "fields",
            "projectsPage",
            "projectsCount",
            "projectsParams",
            "projectsParamsUrl",
            "projectsSortOrder",
            "projectsSortBy",
        ]),

        /**
         * Check if there are
         * selected projects
         */
        bulkDisabled() {
            return this.projectsSelected.length == 0;
        },

        /**
         * Check if
         * all selected projects
         * is processed
         */
        selectedProjectsProcessed() {
            return (
                this.projects.filter(
                    (p) =>
                        this.projectsSelected.indexOf(p.id) >= 0 &&
                        p.processed_at
                ).length == this.projectsSelected.length
            );
        },

        /**
         * Check if
         * all selected projects
         * is not processed
         */
        selectedProjectsNotProcessed() {
            return (
                this.projects.filter(
                    (p) =>
                        this.projectsSelected.indexOf(p.id) >= 0 &&
                        !p.processed_at
                ).length == this.projectsSelected.length
            );
        },

        /**
         * Check if
         * there are activated filters
         */
        filtersActivated() {
            return (
                this.projectsParamExists() ||
                this.projectsSortOrder ||
                this.projectsSortBy
            );
        },

        count: {
            get: function () {
                return this.projectsCount;
            },
            set: async function (value) {
                store.commit(SET_PROJECTS_COUNT, value);
                this.settingProjectsCount = true;

                try {
                    await store.dispatch(FETCH_PROJECTS);
                } finally {
                    this.settingProjectsCount = false;
                }
            },
        },
    },
};
</script>
