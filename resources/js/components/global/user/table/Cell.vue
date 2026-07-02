<template>
    <component :is="tag" :class="[isFiltered ? 'filtered' : '']" :style="style">
        <relation-cell
            v-if="column.category == 'projects'"
            :user="user"
            :items="user.projects ? user.projects : []"
            @click="manageProjects"
        />
        <relation-cell
            v-else-if="column.category == 'last-project'"
            :user="user"
            :items="user.last_project ? [user.last_project] : []"
            @item-clicked="showProject"
        />
        <meta-cell
            v-else-if="column.category == 'meta'"
            :user="user"
            :field="column.id"
            :type="column.type"
        />
        <street-cell
            v-else-if="column.key == 'street'"
            :user="user"
            :field="column.key"
        />
        <default-cell v-else :user="user" :field="column.key" />
    </component>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";
import { SET_GLOBAL_USER } from "@/actions/user";

// Components
import DefaultCell from "./cell/DefaultCell.vue";
import MetaCell from "./cell/MetaCell.vue";
import RelationCell from "./cell/RelationCell.vue";
import StreetCell from "./cell/StreetCell.vue";

export default {
    components: {
        DefaultCell,
        MetaCell,
        RelationCell,
        StreetCell,
    },

    props: {
        /**
         * HTML tag
         * Default <td>
         * When the column is fixed
         * we use <div> as tag
         */
        tag: {
            type: String,
            default: "td",
        },

        /**
         * Column
         */
        column: {
            type: Array,
        },

        /**
         * User
         */
        user: {
            type: Object,
        },
    },

    methods: {
        /**
         * Manage user project
         * See: @/components/global/user/project/Slide.vue
         */
        manageProjects() {
            store.commit(SET_GLOBAL_USER, this.user);
            store.commit(OPEN_SLIDE, "user-manage-projects");
        },

        showProject(project) {
            this.$router.push({
                name: "prospect",
                params: {
                    project: project.slug,
                },
            });
        },
    },

    computed: {
        ...mapGetters(["globalUsersParamExists"]),

        /**
         * Define the width of the column
         */
        style() {
            const style = {};

            if (this.column.width) {
                style.maxWidth = `${this.column.width}px`;
                style.minWidth = `${this.column.width}px`;
            }

            return style;
        },

        /**
         * Check if column is filtered
         */
        isFiltered() {
            // By calendars
            if (this.column.category == "calendars") {
                return (
                    this.globalUsersParamExists("withCalendars") ||
                    this.globalUsersParamExists("withCalendars")
                );
                // By categories
            } else if (this.column.category == "categories") {
                return (
                    this.globalUsersParamExists("withCategories") ||
                    this.globalUsersParamExists("withCategories")
                );
                // By documents
            } else if (this.column.category == "documents") {
                return (
                    this.globalUsersParamExists("withDocuments") ||
                    this.globalUsersParamExists("withDocuments")
                );
                // By folders
            } else if (this.column.category == "folders") {
                return (
                    this.globalUsersParamExists("withFolders") ||
                    this.globalUsersParamExists("withFolders")
                );
                // By groups
            } else if (this.column.category == "groups") {
                return (
                    this.globalUsersParamExists("withGroups") ||
                    this.globalUsersParamExists("withGroups")
                );
                // By menus
            } else if (this.column.category == "menus") {
                return (
                    this.globalUsersParamExists("withMenus") ||
                    this.globalUsersParamExists("withMenus")
                );
                // By order actions
            } else if (this.column.category == "order_actions") {
                return (
                    this.globalUsersParamExists("withOrderActions") ||
                    this.globalUsersParamExists("withoutUsers")
                );
                // By order steps
            } else if (this.column.category == "order_steps") {
                return (
                    this.globalUsersParamExists("withOrderSteps") ||
                    this.globalUsersParamExists("withOrderSteps")
                );
                // By roles
            } else if (this.column.category == "roles") {
                return (
                    this.globalUsersParamExists("withRoles") ||
                    this.globalUsersParamExists("withRoles")
                );
                // By threads
            } else if (this.column.category == "threads") {
                return (
                    this.globalUsersParamExists("withThreads") ||
                    this.globalUsersParamExists("withThreads")
                );
                // By users
            } else if (this.column.category == "users") {
                return (
                    this.globalUsersParamExists("withUsers") ||
                    this.globalUsersParamExists("withUsers")
                );
                // By meta fields
            } else if (this.column.category == "meta") {
                return this.globalUsersParamExists("meta_" + this.column.id);
            }

            // By default field
            return this.globalUsersParamExists("field_" + this.column.key);
        },
    },
};
</script>
