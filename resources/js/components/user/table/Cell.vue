<template>
    <component :is="tag" :class="[isFiltered ? 'filtered' : '']" :style="style">
        <relation-cell
            v-if="column.category == 'calendars'"
            :user="user"
            :items="user.calendars ? user.calendars : []"
            @click="manageCalendars"
        />
        <relation-cell
            v-else-if="column.category == 'categories'"
            :user="user"
            :items="user.categories ? user.categories : []"
            @click="manageCategories"
        />
        <relation-cell
            v-else-if="column.category == 'documents'"
            :user="user"
            :items="user.documents ? user.documents : []"
            @click="manageDocuments"
        />
        <relation-cell
            v-else-if="column.category == 'folders'"
            :user="user"
            :items="user.folders ? user.folders : []"
            @click="manageFolders"
        />
        <relation-cell
            v-else-if="column.category == 'groups'"
            :user="user"
            :items="user.groups ? user.groups : []"
            @click="manageGroups"
        />
        <relation-cell
            v-else-if="column.category == 'menus'"
            :user="user"
            :items="user.menus ? user.menus : []"
            @click="manageMenus"
        />
        <relation-cell
            v-else-if="column.category == 'order_actions'"
            :user="user"
            :items="user.order_actions ? user.order_actions : []"
            @click="manageOrderActions"
        />
        <relation-cell
            v-else-if="column.category == 'order_steps'"
            :user="user"
            :items="user.order_steps ? user.order_steps : []"
            @click="manageOrderSteps"
        />
        <relation-cell
            v-else-if="column.category == 'questionnaires'"
            :user="user"
            :items="user.questionnaires ? user.questionnaires : []"
            @click="manageQuestionnaires"
        />
        <relation-cell
            v-else-if="column.category == 'roles'"
            :user="user"
            :items="user.roles ? user.roles : []"
            @click="manageRoles"
        />
        <relation-cell
            v-else-if="column.category == 'threads'"
            :user="user"
            :items="user.threads ? user.threads : []"
            @click="manageThreads"
        />
        <relation-cell
            v-else-if="column.category == 'assignable_users'"
            :user="user"
            :items="user.assignable_users ? user.assignable_users : []"
            @click="manageUsers"
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
import { SET_USER } from "@/actions/project/user";

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
         * Manage user calendars
         * See: @/components/user/calendar/Slide.vue
         */
        manageCalendars() {
            if (this.disabled || this.user.id <= 0) {
                return;
            }

            store.commit(SET_USER, this.user);
            store.commit(OPEN_SLIDE, "user-manage-calendars");
        },

        /**
         * Manage user categories
         * See: @/components/user/categorie/Slide.vue
         */
        manageCategories() {
            if (this.disabled || this.user.id <= 0) {
                return;
            }

            store.commit(SET_USER, this.user);
            store.commit(OPEN_SLIDE, "user-manage-categories");
        },

        /**
         * Manage user documents
         * See: @/components/user/document/Slide.vue
         */
        manageDocuments() {
            if (this.disabled || this.user.id <= 0) {
                return;
            }

            store.commit(SET_USER, this.user);
            store.commit(OPEN_SLIDE, "user-manage-documents");
        },

        /**
         * Manage user folders
         * See: @/components/user/folder/Slide.vue
         */
        manageFolders() {
            if (this.disabled || this.user.id <= 0) {
                return;
            }

            store.commit(SET_USER, this.user);
            store.commit(OPEN_SLIDE, "user-manage-folders");
        },

        /**
         * Manage user groups
         * See: @/components/user/group/Slide.vue
         */
        manageGroups() {
            if (this.disabled || this.user.id <= 0) {
                return;
            }

            store.commit(SET_USER, this.user);
            store.commit(OPEN_SLIDE, "user-manage-groups");
        },

        /**
         * Manage user menus
         * See: @/components/user/menu/Slide.vue
         */
        manageMenus() {
            if (this.disabled || this.user.id <= 0) {
                return;
            }

            store.commit(SET_USER, this.user);
            store.commit(OPEN_SLIDE, "user-manage-menus");
        },

        /**
         * Manage user order actions
         * See: @/components/user/order-action/Slide.vue
         */
        manageOrderActions() {
            if (this.disabled || this.user.id <= 0) {
                return;
            }

            store.commit(SET_USER, this.user);
            store.commit(OPEN_SLIDE, "user-manage-order-actions");
        },

        /**
         * Manage user order steps
         * See: @/components/user/order-step/Slide.vue
         */
        manageOrderSteps() {
            if (this.disabled || this.user.id <= 0) {
                return;
            }

            store.commit(SET_USER, this.user);
            store.commit(OPEN_SLIDE, "user-manage-order-steps");
        },

        /**
         * Manage user questionnaires
         * See: @/components/user/questionnaire/Slide.vue
         */
        manageQuestionnaires() {
            if (this.disabled || this.user.id <= 0) {
                return;
            }

            store.commit(SET_USER, this.user);
            store.commit(OPEN_SLIDE, "user-manage-questionnaires");
        },

        /**
         * Manage user roles
         * See: @/components/user/role/Slide.vue
         */
        manageRoles() {
            if (this.disabled || this.user.id <= 0) {
                return;
            }

            store.commit(SET_USER, this.user);
            store.commit(OPEN_SLIDE, "user-manage-roles");
        },

        /**
         * Manage user threads
         * See: @/components/user/thread/Slide.vue
         */
        manageThreads() {
            if (this.disabled || this.user.id <= 0) {
                return;
            }

            store.commit(SET_USER, this.user);
            store.commit(OPEN_SLIDE, "user-manage-threads");
        },

        /**
         * Manage user users
         * See: @/components/user/user/Slide.vue
         */
        manageUsers() {
            if (this.disabled || this.user.id <= 0) {
                return;
            }

            store.commit(SET_USER, this.user);
            store.commit(OPEN_SLIDE, "user-manage-users");
        },
    },

    computed: {
        ...mapGetters(["usersParamExists"]),

        /**
         * Do not allow user
         * to edit field
         * on certain conditions applied to the user
         */
        disabled() {
            return false;
        },

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
                    this.usersParamExists("withCalendars") ||
                    this.usersParamExists("withCalendars")
                );
                // By categories
            } else if (this.column.category == "categories") {
                return (
                    this.usersParamExists("withCategories") ||
                    this.usersParamExists("withCategories")
                );
                // By documents
            } else if (this.column.category == "documents") {
                return (
                    this.usersParamExists("withDocuments") ||
                    this.usersParamExists("withDocuments")
                );
                // By folders
            } else if (this.column.category == "folders") {
                return (
                    this.usersParamExists("withFolders") ||
                    this.usersParamExists("withFolders")
                );
                // By groups
            } else if (this.column.category == "groups") {
                return (
                    this.usersParamExists("withGroups") ||
                    this.usersParamExists("withGroups")
                );
                // By menus
            } else if (this.column.category == "menus") {
                return (
                    this.usersParamExists("withMenus") ||
                    this.usersParamExists("withMenus")
                );
                // By order actions
            } else if (this.column.category == "order_actions") {
                return (
                    this.usersParamExists("withOrderActions") ||
                    this.usersParamExists("withoutUsers")
                );
                // By order steps
            } else if (this.column.category == "order_steps") {
                return (
                    this.usersParamExists("withOrderSteps") ||
                    this.usersParamExists("withOrderSteps")
                );
                // By roles
            } else if (this.column.category == "roles") {
                return (
                    this.usersParamExists("withRoles") ||
                    this.usersParamExists("withRoles")
                );
                // By threads
            } else if (this.column.category == "threads") {
                return (
                    this.usersParamExists("withThreads") ||
                    this.usersParamExists("withThreads")
                );
                // By users
            } else if (this.column.category == "users") {
                return (
                    this.usersParamExists("withUsers") ||
                    this.usersParamExists("withUsers")
                );
                // By meta fields
            } else if (this.column.category == "meta") {
                return this.usersParamExists("meta_" + this.column.id);
            }

            // By default field
            return this.usersParamExists("field_" + this.column.key);
        },
    },
};
</script>
