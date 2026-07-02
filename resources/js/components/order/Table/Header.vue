<template>
    <draggable
        id="hc-orders-table-header"
        tag="thead"
        :list="notFixedColumns"
        :disabled="!headerDraggable"
        item-key="key"
        @end="notFixedColumnMoved"
    >
        <template #header>
            <th class="fixed">
                <label class="hc-table-selector">
                    <input type="checkbox" v-model="selectAll" />
                    <span></span>
                </label>

                <div class="hc-table-row-options">
                    <template
                        v-for="menu in projectUserSettingsOrdersTableMenus"
                    >
                        <a v-if="menu == 'view'"></a>
                        <a
                            v-else-if="menu == 'commission'"
                            @click.prevent.stop=""
                            ><i class="fa fa-money"></i
                        ></a>
                        <a v-else-if="menu == 'document'" @click.prevent.stop=""
                            ><i class="fa fa-file-pdf"></i
                        ></a>
                    </template>
                </div>

                <draggable
                    tag="div"
                    :list="fixedColumns"
                    class="hc-table-fixed-headers"
                    item-key="key"
                    :disabled="!headerDraggable"
                    @end="fixedColumnMoved"
                >
                    <template #item="{ element }">
                        <header-cell
                            tag="div"
                            :column="element"
                            @focus="draggable = false"
                            @blur="draggable = true"
                        />
                    </template>
                </draggable>
            </th>
        </template>

        <template #item="{ element }">
            <header-cell
                :column="element"
                @focus="draggable = false"
                @blur="draggable = true"
            />
        </template>
    </draggable>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { OPEN_SLIDE } from "@/actions/slide";
import { UPDATE_SELECTED_ORDERS } from "@/actions/project/order";
import { UPDATE_PROJECT_USER_SETTING } from "@/actions/project/user/setting";

import HeaderCell from "./HeaderCell.vue";

export default {
    components: {
        HeaderCell,
    },

    props: {
        /**
         * List of columns
         */
        columns: {
            type: Array,
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
    },

    data() {
        return {
            draggable: true,
        };
    },

    methods: {
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
            let settings = this.projectUserSettingsOrdersTable;
            let newSettings;

            const oldIndex = fixed
                ? this.columns.filter((c) => c.fixed && !c.hidden)[
                      e.oldDraggableIndex
                  ].index
                : this.columns.filter((c) => !c.fixed && !c.hidden)[
                      e.oldDraggableIndex
                  ].index;
            const newIndex = fixed
                ? this.columns.filter((c) => c.fixed && !c.hidden)[
                      e.newDraggableIndex
                  ].index
                : this.columns.filter((c) => !c.fixed && !c.hidden)[
                      e.newDraggableIndex
                  ].index;

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
                store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                    key: "orders-table",
                    value: newSettings,
                });
            }
        },

        /**
         * Check if option (file, message, ...) is filtered
         * @param {*} option
         */
        isOptionFiltered(option) {
            return false;
        },
    },

    computed: {
        ...mapGetters([
            "projectUserSettingsOrdersTable",
            "ordersParamExists",
            "ordersSelected",
            "orders",
            "projectUserSettingsOrdersTableMenus",
            "can",
        ]),

        /**
         *
         */
        selectAll: {
            get() {
                return this.orders.length == this.ordersSelected.length;
            },
            set(value) {
                store.commit(
                    UPDATE_SELECTED_ORDERS,
                    value ? this.orders.map((p) => p.id) : []
                );
            },
        },

        headerDraggable() {
            return deviceType() == "desktop" && this.draggable;
        },

        isDesktop() {
            return deviceType() == "desktop";
        },
    },
};
</script>
