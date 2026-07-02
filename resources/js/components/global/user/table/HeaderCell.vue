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
        <!-- Projects -->
        <relation-header-cell
            v-if="column.category == 'projects'"
            :column="column"
            @click="filterProject"
        />

        <!-- Last project -->
        <relation-header-cell
            v-else-if="column.category == 'last-project'"
            :column="column"
        />

        <!-- Meta field -->
        <default-header-cell
            v-else-if="column.category == 'meta'"
            :column="column"
        />

        <!-- Default field -->
        <default-header-cell v-else :column="column" />

        <!--div class="hc-header-cell-options"-->
        <!-- Remove column -->
        <!--span
                class="hc-header-cell-remove fa fa-times"
                @click.stop="removeColumn"
                title="Enlever cette colonne du tableau"
            ></span-->

        <!-- Pin column -->
        <!--span
                class="hc-header-cell-pin fa fa-thumbtack"
                @click.stop="togglePinColumn"
                title="Figer cette colonne"
            ></!--span>
        </div--->

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
import { UPDATE_USER_SETTING } from "@/actions/user/setting";

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
            let newSettings = this.userSettingsUsersTable;
            newSettings.splice(this.column.index, 1);

            store.dispatch(UPDATE_USER_SETTING, {
                key: "global-users-table",
                value: newSettings,
            });
        },

        /**
         * Pin or do not pin the column
         */
        togglePinColumn() {
            let settings = this.userSettingsUsersTable;
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
            store.dispatch(UPDATE_USER_SETTING, {
                key: "global-users-table",
                value: newSettings,
            });
        },

        /**
         * Auto width column
         */
        autoWidth() {
            this.$refs.resize.style.left = "100%";
            let newSettings = this.userSettingsUsersTable;
            const index = this.column.index;

            if (newSettings[index].width !== undefined) {
                delete newSettings[index].width;

                store.dispatch(UPDATE_USER_SETTING, {
                    key: "global-users-table",
                    value: newSettings,
                });
            }
        },

        /**
         * When we finally resize the column width
         * update user propsects table setting
         */
        change() {
            let newSettings = this.userSettingsUsersTable;
            const index = this.column.index;
            newSettings[index].width = this.currentWidth;

            store.dispatch(UPDATE_USER_SETTING, {
                key: "global-users-table",
                value: newSettings,
            });
        },

        /**
         * Filter by project
         * See: @/components/global/user/filters/project/Slide.vue
         */
        filterProject() {
            store.commit(OPEN_SLIDE, "global-users-table-filter-project");
        },
    },

    computed: {
        ...mapGetters(["userSettingsUsersTable", "globalUsersParamExists"]),

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
