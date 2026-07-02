<template>
    <div class="hc-flex-column" style="height: 100%; overflow: hidden">
        <search v-model="keyword" />

        <item-list class="hc-flex-1" style="overflow: auto">
            <draggable
                tag="item-list"
                :disabled="!movable"
                :list="availableColumns.filter((c) => c.fixed)"
                item-key="columnId"
                style="
                    border-bottom: 1px solid #1e6ee5;
                    height: auto;
                    overflow: visible;
                "
                @end="fixedColumnMoved"
            >
                <template #item="{ element }">
                    <column-row :column="element" />
                </template>
            </draggable>
            <draggable
                tag="item-list"
                :disabled="!movable"
                :list="availableColumns.filter((c) => c.fixed === false)"
                item-key="columnId"
                style="
                    border-bottom: 1px solid #1e6ee5;
                    height: auto;
                    overflow: visible;
                "
                @end="notFixedColumnMoved"
            >
                <template #item="{ element }">
                    <column-row :column="element" />
                </template>
            </draggable>
            <column-row
                v-for="element in availableColumns.filter(
                    (c) => c.fixed === null
                )"
                :column="element"
            />
        </item-list>

        <item style="border-bottom: 1px solid #eee">
            <div class="hc-item-main-content hc-flex-column"></div>

            <!-- Movable -->
            <icon
                :class="[
                    'fa',
                    'fa-arrows',
                    movable ? 'icon-blue' : 'icon-grey',
                ]"
                @click.prevent.stop="movable = !movable"
            />
        </item>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { SHOW_IMPORT } from "@/actions/project/import";
import { FETCH_USERS } from "@/actions/project/user";
import { UPDATE_PROJECT_USER_SETTING } from "@/actions/project/user/setting";
import { SHOW_PROJECT } from "@/actions/project";

// Components
import ColumnRow from "./ColumnRow.vue";

export default {
    components: {
        ColumnRow,
    },

    data() {
        return {
            frameTab: 0,

            // keyword search
            keyword: "",

            movable: deviceType() == "desktop",
            settingUsersTable: false,
        };
    },

    methods: {
        async setUsersTableSetting(userImport) {
            this.settingUsersTable = true;

            try {
                userImport = await store.dispatch(SHOW_IMPORT, userImport.id);

                const settings = userImport.mapping
                    .filter((s) => s)
                    .map((s) => ({
                        key: s,
                    }));

                await store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                    key: "users-table",
                    value: settings,
                });
                store.dispatch(SHOW_PROJECT, this.project.slug);
                store.dispatch(FETCH_USERS);
            } finally {
                this.settingUsersTable = false;
            }
        },

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
            let settings = this.projectUserSettingsUsersTable.filter((c) =>
                this.columns.find((column) => column.columnId == c.key)
            );
            let newSettings;

            const oldIndex = fixed
                ? this.availableColumns.filter((c) => c.fixed)[
                      e.oldDraggableIndex
                  ].index
                : this.availableColumns.filter((c) => !c.fixed)[
                      e.oldDraggableIndex
                  ].index;
            const newIndex = fixed
                ? this.availableColumns.filter((c) => c.fixed)[
                      e.newDraggableIndex
                  ].index
                : this.availableColumns.filter((c) => !c.fixed)[
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
                    key: "users-table",
                    value: newSettings,
                });
            }
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "fields",
            "projectUserSettingsUsersTable",
            "projectUserSettingsUsersTableHas",
            "can",
        ]),

        columns() {
            return [
                // Calendars
                {
                    type: "other",
                    name: this.$t("user.table.column.others.calendars"),
                    icon: "fa fa-calendar icon-purple",
                    columnId: "calendars",
                    headerId: "hc-users-table-header-calendars-calendars",
                },
                // Category
                {
                    type: "other",
                    name: this.$t("user.table.column.others.categories"),
                    icon: "fa fa-tags icon-purple",
                    columnId: "categories",
                    headerId: "hc-users-table-header-categories-categories",
                },
                // Document
                {
                    type: "other",
                    name: this.$t("user.table.column.others.documents"),
                    icon: "fa fa-file-pdf icon-garnet",
                    columnId: "documents",
                    headerId: "hc-users-table-header-documents-documents",
                },
                // Folder
                {
                    type: "other",
                    name: this.$t("user.table.column.others.folders"),
                    icon: "fa fa-folder icon-blue",
                    columnId: "folders",
                    headerId: "hc-users-table-header-folders-folders",
                },
                // Groups
                {
                    type: "other",
                    name: this.$t("user.table.column.others.groups"),
                    icon: "fa fa-users icon-purple",
                    columnId: "groups",
                    headerId: "hc-users-table-header-groups-groups",
                },
                // Menu
                {
                    type: "other",
                    name: this.$t("user.table.column.others.menus"),
                    icon: "fa fa-list icon-garnet",
                    columnId: "menus",
                    headerId: "hc-users-table-header-menus-menus",
                },
                // Order step
                {
                    type: "other",
                    name: this.$t("user.table.column.others.order_steps"),
                    icon: "fa fa-calendar icon-cyan",
                    columnId: "order_steps",
                    headerId: "hc-users-table-header-order_steps-order_steps",
                },
                // Questionnaire
                {
                    type: "other",
                    name: this.$t("user.table.column.others.questionnaires"),
                    icon: "fa fa-clipboard icon-purple",
                    columnId: "questionnaires",
                    headerId:
                        "hc-users-table-header-questionnaires-questionnaires",
                },
                // Roles
                {
                    type: "other",
                    name: this.$t("user.table.column.others.roles"),
                    icon: "fa fa-user-tie icon-purple",
                    columnId: "roles",
                    headerId: "hc-users-table-header-roles-roles",
                },
                // Thread
                {
                    type: "other",
                    name: this.$t("user.table.column.others.threads"),
                    icon: "fa fa-envelope icon-green",
                    columnId: "threads",
                    headerId: "hc-users-table-header-threads-threads",
                },
                // Users
                {
                    type: "other",
                    name: this.$t("user.table.column.others.users"),
                    icon: "fa fa-user icon-purple",
                    columnId: "assignable_users",
                    headerId:
                        "hc-users-table-header-assignable_users-assignable_users",
                },
                // Default fields
                ...this.fields
                    .filter((field) => field.for == "user" && !field.meta)
                    .map((field) => ({
                        type: "default-field",
                        name: field.name,
                        icon: "fa fa-columns",
                        columnId: field.slug,
                        headerId: "hc-users-table-header-default-" + field.slug,
                    })),
                // Meta fields
                ...this.fields
                    .filter((field) => field.for == "user" && field.meta)
                    .map((field) => ({
                        type: "meta-field",
                        name: field.name,
                        icon: "fa fa-plus-square",
                        columnId: "meta->" + field.slug,
                        headerId: "hc-users-table-header-meta-" + field.slug,
                    })),
            ]
                .map((column, i) => {
                    const index = this.projectUserSettingsUsersTable.findIndex(
                        (c) => c.key == column.columnId
                    );
                    const c = this.projectUserSettingsUsersTable.find(
                        (c) => c.key == column.columnId
                    );

                    return {
                        ...column,
                        index: index >= 0 ? index : 1000 + i,
                        fixed: c ? (c.fixed ? true : false) : null,
                    };
                })
                .sort((column1, column2) => {
                    return !column1.fixed && column2.fixed
                        ? 1
                        : column1.fixed && !column2.fixed
                        ? -1
                        : column1.index > column2.index
                        ? 1
                        : -1;
                })
                .map((column, index) => ({
                    ...column,
                    index,
                }));
        },

        /**
         *
         */
        availableColumns() {
            const keyword = removeStringAccent(this.keyword);

            return this.columns.filter(
                (column) =>
                    removeStringAccent(column.name).indexOf(keyword) >= 0 ||
                    (column.type == "category" &&
                        column.labels.find(
                            (label) =>
                                removeStringAccent(label.name).indexOf(
                                    keyword
                                ) >= 0
                        ))
            );
        },
    },
};
</script>
