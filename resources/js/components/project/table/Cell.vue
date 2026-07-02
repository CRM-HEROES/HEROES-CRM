<template>
    <component :is="tag" :class="[isFiltered ? 'filtered' : '']" :style="style">
        <relation-cell
            v-if="column.category == 'active-users'"
            :project="project"
            :items="project.active_users ? project.active_users : []"
        />
        <relation-cell
            v-else-if="column.category == 'users'"
            :project="project"
            :items="project.users ? project.users : []"
        />
        <relation-cell
            v-else-if="column.category == 'groups'"
            :project="project"
            :items="project.groups ? project.groups : []"
        />
        <relation-cell
            v-else-if="column.category == 'import'"
            :project="project"
            :items="project.import ? [project.import] : []"
        />
        <relation-cell
            v-else-if="column.category == 'category'"
            :project="project"
            :items="categoryLabels(column.id)"
        />
        <meta-cell
            v-else-if="column.category == 'meta'"
            :project="project"
            :field="column.id"
            :type="column.type"
        />
        <street-cell
            v-else-if="column.key == 'street'"
            :project="project"
            :field="column.key"
        />
        <default-cell v-else :project="project" :field="column.key" />
    </component>
</template>

<script>
import { mapGetters } from "vuex";

// Actions

// Components
import DefaultCell from "./cell/DefaultCell.vue";
import MetaCell from "./cell/MetaCell.vue";
import RelationCell from "./cell/RelationCell.vue";

export default {
    components: {
        DefaultCell,
        MetaCell,
        RelationCell,
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
         * Project
         */
        project: {
            type: Object,
        },
    },

    methods: {},

    computed: {
        ...mapGetters(["categories", "projectsParamExists"]),

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
            // By users
            if (this.column.category == "users") {
                return (
                    this.projectsParamExists("withUsers") ||
                    this.projectsParamExists("withoutUsers")
                );
                // By groups
            } else if (this.column.category == "groups") {
                return (
                    this.projectsParamExists("withGroups") ||
                    this.projectsParamExists("withoutGroups")
                );
                // By imports
            } else if (this.column.category == "import") {
                return (
                    this.projectsParamExists("withImports") ||
                    this.projectsParamExists("withoutImports")
                );
                // By Events
            } else if (this.column.category == "events") {
                return (
                    this.projectsParamExists("withEvents") ||
                    this.projectsParamExists("withoutEvents")
                );
                // By interactions
            } else if (this.column.category == "interactions") {
                return (
                    this.projectsParamExists("withInteractions") ||
                    this.projectsParamExists("withoutInteractions")
                );
                // By sms
            } else if (this.column.category == "sms") {
                return (
                    this.projectsParamExists("withSms") ||
                    this.projectsParamExists("withoutSms")
                );
                // By categories
            } else if (this.column.category == "category") {
                return (
                    this.projectsParamExists(
                        "withCategory_" + this.column.id
                    ) ||
                    this.projectsParamExists(
                        "withoutCategory_" + this.column.id
                    )
                );
                // By threads
            } else if (this.column.category == "thread") {
                return (
                    this.projectsParamExists("thread_" + this.column.id) ||
                    this.projectsParamExists("withMessages", this.column.id)
                );
                // By meta field
            } else if (this.column.category == "meta") {
                return this.projectsParamExists("meta_" + this.column.id);
            }

            // By default field
            return this.projectsParamExists("field_" + this.column.key);
        },
    },
};
</script>
