<template>
    <component
        :is="tag"
        ref="resizable"
        :class="[
            resizeDragging ? 'hc-header-cell-resizing' : '',
            isFiltered ? 'filtered' : '',
        ]"
        :style="style"
        :id="id"
    >
        <!-- Users -->
        <relation-header-cell
            v-if="column.category == 'users'"
            :column="column"
            @click.stop="filterUser"
        />

        <!-- Groups -->
        <relation-header-cell
            v-else-if="column.category == 'groups'"
            :column="column"
            @click.stop="filterGroup"
        />

        <!-- Orders -->
        <relation-header-cell
            v-else-if="column.category == 'orders'"
            :column="column"
            @click.stop="filterOrder"
        />

        <!-- Import -->
        <relation-header-cell
            v-else-if="column.category == 'import'"
            :column="column"
            @click.stop="filterImport"
        />

        <!-- Pipedrive accounts -->
        <relation-header-cell
            v-else-if="column.category == 'pipedrive-accounts'"
            :column="column"
            @click.stop="filterPipedriveAccount"
        />

        <!-- Creator -->
        <relation-header-cell
            v-else-if="column.category == 'creator'"
            :column="column"
            @click.stop="filterCreator"
        />

        <!-- Labels -->
        <relation-header-cell
            v-else-if="column.category == 'category'"
            :column="column"
            @click.stop="filterCategory"
        />

        <!-- Events -->
        <event-header-cell
            v-else-if="column.category == 'events'"
            :column="column"
            @click.stop="filterEvent"
        />

        <!-- Events -->
        <event-header-cell
            v-else-if="column.category == 'event-field'"
            :column="column"
            @click.stop="filterEvent"
        />

        <!-- Interactions -->
        <interaction-header-cell
            v-else-if="column.category == 'interactions'"
            :column="column"
            @click.stop="filterInteraction"
        />

        <!-- Sms -->
        <sms-header-cell
            v-else-if="column.category == 'sms'"
            :column="column"
        />

        <!-- Thread -->
        <thread-header-cell
            v-else-if="column.category == 'thread'"
            :column="column"
        />

        <!-- Created at field -->
        <created-at-header-cell
            v-else-if="
                column.category == 'default' && column.id == 'created_at'
            "
            :column="column"
            @click.stop="filterCreatedAt"
        />

        <!-- Meta field -->
        <!-- Default field -->
        <default-header-cell
            v-else
            @focus="$emit('focus')"
            @blur="$emit('blur')"
            :column="column"
        />

        <div class="hc-header-cell-options">
            <!-- Remove column -->
            <span
                class="hc-header-cell-remove fa fa-times"
                @click.stop="removeColumn"
                v-tooltip="'Enlever cette colonne du tableau'"
            ></span>

            <!-- Pin column -->
            <span
                class="hc-header-cell-pin fa fa-thumbtack"
                @click.stop="togglePinColumn"
                v-tooltip="'Figer cette colonne'"
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
import { SET_PROSPECT_FILTER_CATEGORY } from "@/actions/project/prospect";

// Components
import DefaultHeaderCell from "./header-cell/DefaultHeaderCell.vue";
import CreatedAtHeaderCell from "./header-cell/CreatedAtHeaderCell.vue";
import EventHeaderCell from "./header-cell/EventHeaderCell.vue";
import InteractionHeaderCell from "./header-cell/InteractionHeaderCell.vue";
import RelationHeaderCell from "./header-cell/RelationHeaderCell.vue";
import SmsHeaderCell from "./header-cell/SmsHeaderCell.vue";
import ThreadHeaderCell from "./header-cell/ThreadHeaderCell.vue";

export default {
    components: {
        DefaultHeaderCell,
        CreatedAtHeaderCell,
        EventHeaderCell,
        InteractionHeaderCell,
        RelationHeaderCell,
        SmsHeaderCell,
        ThreadHeaderCell,
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
         * from the prospects table
         */
        removeColumn() {
            hcConfirm(
                this.$t("prospect.table.column.delete_confirm"),
                async () => {
                    let newSettings = this.projectUserSettingsProspectsTable;
                    newSettings.splice(this.column.index, 1);

                    store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                        key: "prospects-table",
                        value: newSettings,
                    });
                }
            );
        },

        /**
         * Pin or do not pin the column
         */
        togglePinColumn() {
            let settings = this.projectUserSettingsProspectsTable;
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

            // Update user prospects table setting
            store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "prospects-table",
                value: newSettings,
            });
        },

        /**
         * Auto width column
         */
        autoWidth() {
            this.$refs.resize.style.left = "100%";
            let newSettings = this.projectUserSettingsProspectsTable;
            const index = this.column.index;

            if (newSettings[index].width !== undefined) {
                delete newSettings[index].width;

                store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                    key: "prospects-table",
                    value: newSettings,
                });
            }
        },

        /**
         * When we finally resize the column width
         * update user propsects table setting
         */
        change() {
            let newSettings = this.projectUserSettingsProspectsTable;
            const index = this.column.index;
            newSettings[index].width = this.currentWidth;

            store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "prospects-table",
                value: newSettings,
            });
        },

        /**
         * Filter by labels
         * See: @/components/prospect/filters/label/Slide.vue
         */
        filterCategory() {
            store.commit(OPEN_SLIDE, "prospects-table-filter-label");
            store.commit(SET_PROSPECT_FILTER_CATEGORY, this.category);
        },

        /**
         * Filter by events
         * See: @/components/prospect/filters/event/Slide.vue
         */
        filterEvent() {
            store.commit(OPEN_SLIDE, "prospects-table-filter-event");
            if (this.column.id) {
            }
        },

        /**
         * Filter by interaction
         */
        filterInteraction() {
            store.commit(OPEN_SLIDE, "prospects-table-filter-interaction");
        },

        /**
         * Filter by users
         * See: @/components/prospect/filters/user/Slide.vue
         */
        filterUser() {
            store.commit(OPEN_SLIDE, "prospects-table-filter-user");
        },

        /**
         * Filter by groups
         * See: @/components/prospect/filters/group/Slide.vue
         */
        filterGroup() {
            store.commit(OPEN_SLIDE, "prospects-table-filter-group");
        },

        /**
         * Filter by orders
         * See: @/components/prospect/filters/group/Slide.vue
         */
        filterOrder() {
            store.commit(OPEN_SLIDE, "prospects-table-filter-order");
        },

        /**
         * Filter by imports
         * See: @/components/prospect/filters/import/Slide.vue
         */
        filterImport() {
            store.commit(OPEN_SLIDE, "prospects-table-filter-import");
        },

        /**
         * Filter by Pipedrive account
         * See: @/components/prospect/filters/pipedrive-account/Slide.vue
         */
        filterPipedriveAccount() {
            store.commit(
                OPEN_SLIDE,
                "prospects-table-filter-pipedrive-account"
            );
        },

        /**
         * Filter by creators
         * See: @/components/prospect/filters/creator/Slide.vue
         */
        filterCreator() {
            store.commit(OPEN_SLIDE, "prospects-table-filter-creator");
        },

        /**
         * Filter by created at
         */
        filterCreatedAt() {
            store.commit(OPEN_SLIDE, "prospects-table-filter-created-at");
        },
    },

    computed: {
        ...mapGetters([
            "projectUserSettingsProspectsTable",
            "categories",
            "prospectsParamExists",
        ]),

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
         * Only for category column
         * Gest associated category
         */
        category() {
            return this.categories.find(
                (f) => f.for == "prospect" && f.id == this.column.id
            );
        },

        /**
         * Define the width of the column
         */
        style() {
            const width = this.column.width ? this.column.width : 120;

            return {
                maxWidth: `${width}px`,
                minWidth: `${width}px`,
            };
        },

        /**
         * Check if column is filtered
         */
        isFiltered() {
            // By users
            if (this.column.category == "users") {
                return (
                    this.prospectsParamExists("withUsers") ||
                    this.prospectsParamExists("withoutUsers")
                );
                // By groups
            } else if (this.column.category == "groups") {
                return (
                    this.prospectsParamExists("withGroups") ||
                    this.prospectsParamExists("withoutGroups")
                );
                // By orders
            } else if (this.column.category == "orders") {
                return (
                    this.prospectsParamExists("withOrders") ||
                    this.prospectsParamExists("withoutOrders")
                );
                // By Import
            } else if (this.column.category == "import") {
                return (
                    this.prospectsParamExists("withImports") ||
                    this.prospectsParamExists("withoutImports")
                );
                // By Events
            } else if (this.column.category == "events") {
                return (
                    this.prospectsParamExists("withEvents") ||
                    this.prospectsParamExists("withoutEvents")
                );
                // By interactions
            } else if (this.column.category == "interactions") {
                return (
                    this.prospectsParamExists("withInteractions") ||
                    this.prospectsParamExists("withoutInteractions")
                );
                // By sms
            } else if (this.column.category == "sms") {
                return (
                    this.prospectsParamExists("withSms") ||
                    this.prospectsParamExists("withoutSms")
                );
                // By categories
            } else if (this.column.category == "category") {
                return (
                    this.prospectsParamExists(
                        "withCategory_" + this.column.id
                    ) ||
                    this.prospectsParamExists(
                        "withoutCategory_" + this.column.id
                    )
                );
                // By threads
            } else if (this.column.category == "thread") {
                return (
                    this.prospectsParamExists("thread_" + this.column.id) ||
                    this.prospectsParamExists("withMessages", this.column.id)
                );
                // By meta field
            } else if (this.column.category == "meta") {
                return (
                    this.prospectsParamExists("meta_" + this.column.id) ||
                    this.prospectsParamExists("withMeta_" + this.column.id)
                );
            }

            // By default field
            return (
                this.prospectsParamExists("field_" + this.column.key) ||
                this.prospectsParamExists("withField_" + this.column.id)
            );
        },

        id() {
            return (
                "hc-prospects-table-header-" +
                this.column.category +
                "-" +
                this.column.id
            );
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
