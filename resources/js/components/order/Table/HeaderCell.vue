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
        <!-- Actions -->
        <relation-header-cell
            v-if="column.category == 'commissions'"
            :column="column"
            @click="filterActions"
        />

        <!-- Products -->
        <relation-header-cell
            v-else-if="column.category == 'products'"
            :column="column"
            @click="filterProducts"
        />

        <!-- Status -->
        <relation-header-cell
            v-else-if="column.category == 'status'"
            :column="column"
            @click="filterStatus"
        />

        <!-- Steps -->
        <relation-header-cell
            v-else-if="column.category == 'steps'"
            :column="column"
            @click="filterStep"
        />

        <!-- Labels -->
        <relation-header-cell
            v-else-if="column.category == 'category'"
            :column="column"
            @click="filterCategory"
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
         * from the orders table
         */
        removeColumn() {
            hcConfirm(
                this.$t("order.table.column.delete_confirm"),
                async () => {
                    let newSettings = this.projectUserSettingsOrdersTable;
                    newSettings.splice(this.column.index, 1);

                    store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                        key: "orders-table",
                        value: newSettings,
                    });
                }
            );
        },

        /**
         * Pin or do not pin the column
         */
        togglePinColumn() {
            let settings = this.projectUserSettingsOrdersTable;
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

            // Update user orders table setting
            store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "orders-table",
                value: newSettings,
            });
        },

        /**
         * Auto width column
         */
        autoWidth() {
            this.$refs.resize.style.left = "100%";
            let newSettings = this.projectUserSettingsOrdersTable;
            const index = this.column.index;

            if (newSettings[index].width !== undefined) {
                delete newSettings[index].width;

                store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                    key: "orders-table",
                    value: newSettings,
                });
            }
        },

        /**
         * When we finally resize the column width
         * update user propsects table setting
         */
        change() {
            let newSettings = this.projectUserSettingsOrdersTable;
            const index = this.column.index;
            newSettings[index].width = this.currentWidth;

            store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "orders-table",
                value: newSettings,
            });
        },

        /**
         * Filter by actions
         * See: @/components/order/filters/action/Slide.vue
         */
        filterActions() {
            store.commit(OPEN_SLIDE, "orders-table-filter-action");
        },

        /**
         * Filter by products
         * See: @/components/order/filters/status/Slide.vue
         */
        filterProducts() {
            store.commit(OPEN_SLIDE, "orders-table-filter-product");
        },

        /**
         * Filter by status
         * See: @/components/order/filters/status/Slide.vue
         */
        filterStatus() {
            store.commit(OPEN_SLIDE, "orders-table-filter-status");
        },

        /**
         * Filter by steps
         * See: @/components/order/filters/step/Slide.vue
         */
        filterStep() {
            store.commit(OPEN_SLIDE, "orders-table-filter-step");
        },

        /**
         * Filter by labels
         * See: @/components/order/filters/label/Slide.vue
         */
        filterCategory() {
            store.commit(OPEN_SLIDE, "orders-table-filter-label");
            store.commit(SET_ORDER_FILTER_CATEGORY, this.category);
        },

        /**
         * Filter by events
         * See: @/components/order/filters/event/Slide.vue
         */
        filterEvent() {
            store.commit(OPEN_SLIDE, "orders-table-filter-event");
        },

        /**
         * Filter by interaction
         */
        filterInteraction() {
            store.commit(OPEN_SLIDE, "orders-table-filter-interaction");
        },

        /**
         * Filter by users
         * See: @/components/order/filters/user/Slide.vue
         */
        filterUser() {
            store.commit(OPEN_SLIDE, "orders-table-filter-user");
        },

        /**
         * Filter by groups
         * See: @/components/order/filters/group/Slide.vue
         */
        filterGroup() {
            store.commit(OPEN_SLIDE, "orders-table-filter-group");
        },

        /**
         * Filter by orders
         * See: @/components/order/filters/group/Slide.vue
         */
        filterOrder() {
            store.commit(OPEN_SLIDE, "orders-table-filter-order");
        },

        /**
         * Filter by imports
         * See: @/components/order/filters/import/Slide.vue
         */
        filterImport() {
            store.commit(OPEN_SLIDE, "orders-table-filter-import");
        },
    },

    computed: {
        ...mapGetters([
            "projectUserSettingsOrdersTable",
            "categories",
            "ordersParamExists",
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
                (f) => f.for == "order" && f.id == this.column.id
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
            if (this.column.category == "commissions") {
                return (
                    this.ordersParamExists("withActions") ||
                    this.ordersParamExists("withoutActions")
                );
            } else if (this.column.category == "products") {
                return (
                    this.ordersParamExists("withProducts") ||
                    this.ordersParamExists("withoutProducts")
                );
            } else if (this.column.category == "status") {
                return (
                    this.ordersParamExists("withStatuses") ||
                    this.ordersParamExists("withoutStatuses")
                );
            } else if (this.column.category == "steps") {
                return (
                    this.ordersParamExists("withSteps") ||
                    this.ordersParamExists("withoutSteps")
                );
            } else if (this.column.category == "meta") {
                return this.ordersParamExists("meta_" + this.column.id);
            }

            // By default field
            return this.ordersParamExists("field_" + this.column.key);
        },

        id() {
            return (
                "hc-orders-table-header-" +
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
