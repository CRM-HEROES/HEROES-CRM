<template>
    <draggable
        id="hc-prospects-table-header"
        tag="thead"
        :list="notFixedColumns"
        :disabled="!headerDraggable"
        item-key="key"
        fallback-axis="x"
        @end="notFixedColumnMoved"
        v-tuto="
            isDesktop
                ? {
                      key: 'project.prospect.table.header',
                      name: 'Tableau de prospects - Entête',
                      body: 'Il est possible de réordonner, rédimensionner ou figer les colonnes dans le tableau.<br><img style=&quot;width: 100%;margin-top: 10px;border-radius: 5px;&quot; src=&quot;/images/tutorial/prospects.table.header.gif&quot; />',
                  }
                : null
        "
    >
        <template #header>
            <th class="fixed">
                <label class="hc-table-selector">
                    <input type="checkbox" v-model="selectAll" />
                    <span></span>
                </label>

                <div class="hc-table-row-options">
                    <template
                        v-for="menu in projectUserSettingsProspectsTableMenus"
                    >
                        <a v-if="menu == 'view'"></a>
                        <a v-else-if="menu == 'document'"></a>
                        <a v-else-if="menu == 'duplicate'"></a>
                        <a
                            v-else-if="menu == 'file' && canFile"
                            :class="[
                                isOptionFiltered('file') ? 'filtered' : '',
                            ]"
                            @click.prevent.stop="filterFile"
                            ><i class="fa fa-folder"></i
                        ></a>
                        <a
                            v-else-if="menu == 'message' && canMessage"
                            :class="[
                                isOptionFiltered('message') ? 'filtered' : '',
                            ]"
                            @click.prevent.stop="filterMessage"
                            ><i class="fa fa-envelope"></i
                        ></a>
                        <a
                            v-else-if="
                                menu == 'order' &&
                                can('all.prospect.order.view')
                            "
                            :class="[
                                isOptionFiltered('order') ? 'filtered' : '',
                            ]"
                            @click.prevent.stop="filterOrder"
                            ><i class="fa fa-shopping-cart"></i
                        ></a>
                        <a
                            v-else-if="
                                menu == 'interaction' &&
                                can('all.prospect.interaction.view')
                            "
                            :class="[
                                isOptionFiltered('interaction')
                                    ? 'filtered'
                                    : '',
                            ]"
                            @click.prevent.stop="filterInteraction"
                            ><i class="fa fa-phone"></i
                        ></a>
                        <a
                            v-else-if="
                                menu == 'sms' && can('all.prospect.sms.view')
                            "
                            :class="[isOptionFiltered('sms') ? 'filtered' : '']"
                            @click.prevent.stop="filterSms"
                            ><i class="fa fa-comment"></i
                        ></a>
                        <a
                            v-else-if="menu == 'questionnaire'"
                            :class="[
                                isOptionFiltered('questionnaire')
                                    ? 'filtered'
                                    : '',
                            ]"
                            @click.prevent.stop="filterQuestionnaire"
                            ><i class="fa fa-clipboard"></i
                        ></a>
                        <a
                            v-else-if="menu == 'user'"
                            :class="[
                                isOptionFiltered('user') ? 'filtered' : '',
                            ]"
                            @click.prevent.stop="filterUser"
                            ><i class="fa fa-user-plus"></i
                        ></a>
                        <a
                            v-else-if="menu == 'group'"
                            :class="[
                                isOptionFiltered('group') ? 'filtered' : '',
                            ]"
                            @click.prevent.stop="filterGroup"
                            ><i class="fa fa-users"></i
                        ></a>
                        <a v-else-if="menu == 'revision'"></a>
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
import { UPDATE_SELECTED_PROSPECTS } from "@/actions/project/prospect";
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
            let settings = this.projectUserSettingsProspectsTable;
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
                    key: "prospects-table",
                    value: newSettings,
                });
            }
        },

        /**
         * Filter by file
         */
        filterFile() {
            store.commit(OPEN_SLIDE, "prospects-table-filter-file");
        },

        /**
         * Filter by message
         */
        filterMessage() {
            store.commit(OPEN_SLIDE, "prospects-table-filter-message");
        },

        /**
         * Filter by order
         */
        filterOrder() {
            store.commit(OPEN_SLIDE, "prospects-table-filter-order");
        },

        /**
         * Filter by interaction
         */
        filterInteraction() {
            store.commit(OPEN_SLIDE, "prospects-table-filter-interaction");
        },

        /**
         * Filter by sms
         */
        filterSms() {
            store.commit(OPEN_SLIDE, "prospects-table-filter-sms");
        },

        /**
         * Filter by questionnaire
         */
        filterQuestionnaire() {
            store.commit(OPEN_SLIDE, "prospects-table-filter-questionnaire");
        },

        /**
         * Filter by user
         */
        filterUser() {
            store.commit(OPEN_SLIDE, "prospects-table-filter-user");
        },

        /**
         * Filter by group
         */
        filterGroup() {
            store.commit(OPEN_SLIDE, "prospects-table-filter-group");
        },

        /**
         * Check if option (file, message, ...) is filtered
         * @param {*} option
         */
        isOptionFiltered(option) {
            // By files
            if (option == "file") {
                return (
                    this.prospectsParamExists("withFiles") ||
                    this.prospectsParamExists("withoutFiles")
                );
                // By messages
            } else if (option == "message") {
                return (
                    this.prospectsParamExists("withMessages") ||
                    this.prospectsParamExists("withoutMessages")
                );
                // By orders
            } else if (option == "order") {
                return (
                    this.prospectsParamExists("withOrders") ||
                    this.prospectsParamExists("withoutOrders")
                );
                // By interactions
            } else if (option == "interaction") {
                return (
                    this.prospectsParamExists("withInteractions") ||
                    this.prospectsParamExists("withoutInteractions")
                );
                // By sms
            } else if (option == "sms") {
                return (
                    this.prospectsParamExists("withSms") ||
                    this.prospectsParamExists("withoutSms")
                );
                // By questionnaires
            } else if (option == "questionnaire") {
                return (
                    this.prospectsParamExists("withQuestionnaires") ||
                    this.prospectsParamExists("withoutQuestionnaires")
                );
                // By users
            } else if (option == "user") {
                return (
                    this.prospectsParamExists("withUsers") ||
                    this.prospectsParamExists("withoutUsers")
                );
                // By groups
            } else if (option == "group") {
                return (
                    this.prospectsParamExists("withGroups") ||
                    this.prospectsParamExists("withoutGroups")
                );
            }

            return false;
        },
    },

    computed: {
        ...mapGetters([
            "projectUserSettingsProspectsTable",
            "prospectsParamExists",
            "prospectsSelected",
            "prospects",
            "projectUserSettingsProspectsTableMenus",
            "can",
            "canMessage",
            "canFile",
            "canEvent",
        ]),

        /**
         *
         */
        selectAll: {
            get() {
                return this.prospects.length == this.prospectsSelected.length;
            },
            set(value) {
                store.commit(
                    UPDATE_SELECTED_PROSPECTS,
                    value ? this.prospects.map((p) => p.id) : []
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
