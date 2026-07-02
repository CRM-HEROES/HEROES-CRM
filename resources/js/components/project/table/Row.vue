<template>
    <tr
        :class="[
            project.deleted_at ? 'deleted' : '',
            project.processed_at ? 'processed' : '',
        ]"
    >
        <td class="fixed">
            <!-- Select project -->
            <label class="hc-table-selector">
                <input type="checkbox" v-model="selected" :value="project.id" />
                <span></span>
            </label>

            <div class="hc-table-row-options">
                <!-- View project -->

                <router-link
                    :to="{
                        name: 'prospect',
                        params: {
                            project: project.slug,
                        },
                    }"
                    class="hc-table-project-option-view"
                >
                    <i class="fa fa-eye"></i>
                </router-link>
                <!-- Project users connections stat -->
                <a
                    class="hc-table-project-option-view"
                    @click.prevent="showUserConnectionStat"
                    ><i class="fa fa-users"></i
                ></a>
            </div>

            <!-- List of fixed columns -->
            <div class="hc-table-fixed-cells">
                <cell
                    v-for="column in fixedColumns"
                    tag="div"
                    :key="column.key"
                    :project="project"
                    :column="column"
                />
            </div>
        </td>

        <!-- List of none fixed columns -->
        <cell
            v-for="column in notFixedColumns"
            :key="column.key"
            :project="project"
            :column="column"
        />
    </tr>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    UPDATE_SELECTED_PROJECTS,
    TOGGLE_SELECTED_PROJECTS,
} from "@/actions/project";
import { SET_PROJECT } from "@/actions/project";
import { OPEN_SLIDE } from "@/actions/slide";

// Components
import Cell from "./Cell.vue";

export default {
    components: {
        Cell,
    },

    props: {
        /**
         * Project
         */
        project: {
            type: Object,
        },

        /**
         * List of fixed columns
         */
        fixedColumns: {
            type: Array,
        },

        /**
         * List of none fixed columns
         */
        notFixedColumns: {
            type: Array,
        },

        /**
         * Index of the row in the projects table
         * we need it when we use SHIFT
         * to select multiple projects
         * See: toggleSelectedProject
         */
        index: {
            type: Number,
        },
    },

    methods: {
        /**
         * Handle SHIFT
         * when clicking checkbox
         *
         * @param {*} event
         */
        toggleSelectedProject(event) {
            const index = this.index;
            const shift = event.shiftKey;
            const checked = event.currentTarget.checked;

            store.commit(TOGGLE_SELECTED_PROJECTS, { index, shift, checked });
        },

        /**
         * Go to the project profile
         */
        showProject() {
            store.commit(SET_PROJECT, this.project);
            this.$router.push({
                name: "project.show",
                params: {
                    project: this.project.slug,
                },
            });
        },

        /**
         *
         */
        showUserConnectionStat() {
            store.commit(SET_PROJECT, this.project);
            store.commit(OPEN_SLIDE, "user-connection-stat");
        },

        /**
         * Check if option is filtered
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
        /**
         * Select a row
         */
        selected: {
            get() {
                return this.projectsSelected;
            },
            set(value) {
                store.commit(UPDATE_SELECTED_PROJECTS, value);
            },
        },

        ...mapGetters(["project", "projectsSelected", "projectsParamExists"]),
    },
};
</script>
