<template>
    <component
        :is="tag"
        ref="resizable"
        :class="[
            resizeDragging ? 'hc-header-cell-resizing' : '',
            isFiltered ? 'filtered' : '',
        ]"
        :style="style"
    >
        <!-- Calendars -->
        <relation-header-cell
            v-if="column.category == 'calendars'"
            :column="column"
            @click="filterCalendar"
        />

        <!-- Categories -->
        <relation-header-cell
            v-else-if="column.category == 'categories'"
            :column="column"
            @click="filterCategory"
        />

        <!-- Documents -->
        <relation-header-cell
            v-else-if="column.category == 'documents'"
            :column="column"
            @click="filterDocument"
        />

        <!-- Folders -->
        <relation-header-cell
            v-else-if="column.category == 'folders'"
            :column="column"
            @click="filterFolder"
        />

        <!-- Groups -->
        <relation-header-cell
            v-else-if="column.category == 'groups'"
            :column="column"
            @click="filterGroup"
        />

        <!-- Menus -->
        <relation-header-cell
            v-else-if="column.category == 'menus'"
            :column="column"
            @click="filterMenu"
        />

        <!-- Order actions -->
        <relation-header-cell
            v-else-if="column.category == 'order_actions'"
            :column="column"
            @click="filterOrderAction"
        />

        <!-- Order steps -->
        <relation-header-cell
            v-else-if="column.category == 'order_steps'"
            :column="column"
            @click="filterOrderStep"
        />

        <!-- Roles -->
        <relation-header-cell
            v-else-if="column.category == 'roles'"
            :column="column"
            @click="filterRole"
        />

        <!-- Threads -->
        <relation-header-cell
            v-else-if="column.category == 'threads'"
            :column="column"
            @click="filterThread"
        />

        <!-- Users -->
        <relation-header-cell
            v-else-if="column.category == 'users'"
            :column="column"
            @click="filterUser"
        />

        <!-- Meta field -->
        <default-header-cell
            v-else-if="column.category == 'meta'"
            :column="column"
        />

        <!-- Default field -->
        <default-header-cell v-else :column="column" />

        <div class="hc-header-cell-options">
            <!-- Remove column -->
            <span
                class="hc-header-cell-remove fa fa-times"
                @click.stop="removeColumn"
                title="Enlever cette colonne du tableau"
            ></span>

            <!-- Pin column -->
            <span
                class="hc-header-cell-pin fa fa-thumbtack"
                @click.stop="togglePinColumn"
                title="Figer cette colonne"
            ></span>
        </div>

        <!-- Resize column -->
        <span
            class="hc-header-cell-resize"
            ref="resize"
            @dblclick="autoWidth"
        ></span>
    </component>
</template>

<script>
import { useDrag } from "@/composables/mouse";
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";
import { UPDATE_PROJECT_USER_SETTING } from "@/actions/project/user/setting";

// Components
import DefaultHeaderCell from "./header-cell/DefaultHeaderCell.vue";
import RelationHeaderCell from "./header-cell/RelationHeaderCell.vue";

export default {
    components: {
        DefaultHeaderCell,
        RelationHeaderCell,
    },

    props: {
        /**
         * HTML tag
         * Default <th>
         * When the column is fixed
         * we use <div> as tag
         */
        tag: {
            type: String,
            default: "th",
        },

        /**
         * Column
         */
        column: {
            type: Array,
        },
    },

    data() {
        return {
            /**
             * Resize handler
             */
            resize: null,
            initialWidth: null,
            currentWidth: null,
        };
    },

    mounted() {
        const resize = this.$refs.resize;
        this.resize = useDrag(resize);
    },

    methods: {
        /**
         * Current column offet width
         */
        getCurrentWidth() {
            return this.$refs.resizable.offsetWidth;
        },

        /**
         * Remove the column
         * from the users table
         */
        removeColumn() {
            let newSettings = this.projectUserSettingsUsersTable;
            newSettings.splice(this.column.index, 1);

            store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "users-table",
                value: newSettings,
            });
        },

        /**
         * Pin or do not pin the column
         */
        togglePinColumn() {
            let settings = this.projectUserSettingsUsersTable;
            const count = settings.filter((c) => c.fixed).length;
            const index = this.column.index;

            let newSettings;

            // Do not fix the column
            if (this.column.fixed) {
                delete settings[index].fixed;
                // Put the column
                // At the start
                // of the list of none fixed columns
                newSettings = [
                    ...settings.slice(0, index),
                    ...settings.slice(index + 1, count),
                    settings[index],
                    ...settings.slice(count),
                ];
                // Fix the column
            } else {
                settings[index].fixed = true;
                // Put the column
                // At the end
                // of the list of fixed columns
                newSettings = [
                    ...settings.slice(0, count),
                    settings[index],
                    ...settings.slice(count, index),
                    ...settings.slice(index + 1),
                ];
            }

            // Update user users table setting
            store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "users-table",
                value: newSettings,
            });
        },

        /**
         * Auto width column
         */
        autoWidth() {
            this.$refs.resize.style.left = "100%";
            let newSettings = this.projectUserSettingsUsersTable;
            const index = this.column.index;

            if (newSettings[index].width !== undefined) {
                delete newSettings[index].width;

                store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                    key: "users-table",
                    value: newSettings,
                });
            }
        },

        /**
         * When we finally resize the column width
         * update user propsects table setting
         */
        change() {
            let newSettings = this.projectUserSettingsUsersTable;
            const index = this.column.index;
            newSettings[index].width = this.currentWidth;

            store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "users-table",
                value: newSettings,
            });
        },

        /**
         * Filter by calendars
         * See: @/components/user/filters/calendar/Slide.vue
         */
        filterCalendar() {
            store.commit(OPEN_SLIDE, "users-table-filter-calendar");
        },

        /**
         * Filter by categories
         * See: @/components/user/filters/categorie/Slide.vue
         */
        filterCategory() {
            store.commit(OPEN_SLIDE, "users-table-filter-category");
        },

        /**
         * Filter by documents
         * See: @/components/user/filters/document/Slide.vue
         */
        filterDocument() {
            store.commit(OPEN_SLIDE, "users-table-filter-document");
        },

        /**
         * Filter by folders
         * See: @/components/user/filters/folder/Slide.vue
         */
        filterFolder() {
            store.commit(OPEN_SLIDE, "users-table-filter-folder");
        },

        /**
         * Filter by groups
         * See: @/components/user/filters/group/Slide.vue
         */
        filterGroup() {
            store.commit(OPEN_SLIDE, "users-table-filter-group");
        },

        /**
         * Filter by menus
         * See: @/components/user/filters/menu/Slide.vue
         */
        filterMenu() {
            store.commit(OPEN_SLIDE, "users-table-filter-menu");
        },

        /**
         * Filter by order actions
         * See: @/components/user/filters/order-action/Slide.vue
         */
        filterOrderAction() {
            store.commit(OPEN_SLIDE, "users-table-filter-order-action");
        },

        /**
         * Filter by order steps
         * See: @/components/user/filters/order-step/Slide.vue
         */
        filterOrderStep() {
            store.commit(OPEN_SLIDE, "users-table-filter-order-step");
        },

        /**
         * Filter by roles
         * See: @/components/user/filters/role/Slide.vue
         */
        filterRole() {
            store.commit(OPEN_SLIDE, "users-table-filter-role");
        },

        /**
         * Filter by threads
         * See: @/components/user/filters/thread/Slide.vue
         */
        filterThread() {
            store.commit(OPEN_SLIDE, "users-table-filter-thread");
        },

        /**
         * Filter by users
         * See: @/components/user/filters/user/Slide.vue
         */
        filterUser() {
            store.commit(OPEN_SLIDE, "users-table-filter-user");
        },
    },

    computed: {
        ...mapGetters(["projectUserSettingsUsersTable", "usersParamExists"]),

        /**
         */
        resizeDragging() {
            return this.resize && this.resize.dragging;
        },

        /**
         */
        resizePosition() {
            if (!this.resize || !this.resize.position) {
                return null;
            }

            return this.resize.position;
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

    watch: {
        /**
         *
         */
        resizeDragging(newValue) {
            if (newValue) {
                this.initialWidth = this.getCurrentWidth();
            } else {
                if (
                    this.initialWidth !== null &&
                    this.initialWidth != this.currentWidth
                ) {
                    this.change();
                }
                this.initialWidth = null;
            }
        },

        /**
         */
        resizePosition(newValue) {
            if (!newValue || this.initialWidth === null) {
                return;
            }

            const deltaX = this.resize.position.x - this.resize.origin.x;
            this.currentWidth = Math.max(60, deltaX + this.initialWidth);
            this.$refs.resize.style.left = `${this.currentWidth}px`;
        },
    },
};
</script>
