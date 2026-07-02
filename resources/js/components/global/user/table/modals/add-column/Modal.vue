<template>
    <modal
        :title="'Paramètre des colonnes'"
        name="global-users-table-add-column"
        :width="380"
    >
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
    </modal>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { FETCH_GLOBAL_USERS } from "@/actions/user";
import { UPDATE_USER_SETTING } from "@/actions/user/setting";

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

                await store.dispatch(UPDATE_USER_SETTING, {
                    key: "users-table",
                    value: settings,
                });
                store.dispatch(FETCH_GLOBAL_USERS);
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
            let settings = this.userSettingsUsersTable.filter((c) =>
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
                store.dispatch(UPDATE_USER_SETTING, {
                    key: "users-table",
                    value: newSettings,
                });
            }
        },
    },

    computed: {
        ...mapGetters(["userSettingsUsersTable", "userSettingsUsersTableHas"]),

        columns() {
            return [
                // Projects
                {
                    type: "other",
                    name: "Projects",
                    icon: "fa fa-columns icon-purple",
                    columnId: "projects",
                    headerId: "hc-users-table-header-projects-projects",
                },
                // Last project
                {
                    type: "other",
                    name: "Connecté sur le project",
                    icon: "fa fa-columns icon-purple",
                    columnId: "last-project",
                    headerId: "hc-users-table-header-last-project-last-project",
                },
                // Default fields
                ...[
                    {
                        slug: "name",
                        name: "Prénom",
                    },
                    {
                        slug: "last_name",
                        name: "Nom",
                    },
                    {
                        slug: "email",
                        name: "Email",
                    },
                    {
                        slug: "street",
                        name: "Rue",
                    },
                    {
                        slug: "street_bis",
                        name: "Rue 2",
                    },
                    {
                        slug: "postal_code",
                        name: "Code postal",
                    },
                    {
                        key: "city",
                        name: "Ville",
                    },
                    {
                        slug: "country",
                        name: "Pays",
                    },
                    {
                        slug: "ip_address",
                        name: "Adresse IP",
                    },
                    {
                        slug: "ip_city",
                        name: "IP Ville",
                    },
                    {
                        slug: "ip_postal_code",
                        name: "IP Code postal",
                    },
                    {
                        slug: "ip_state",
                        name: "IP Région",
                    },
                    {
                        slug: "ip_country",
                        name: "IP Pays",
                    },
                    {
                        slug: "ip_latitude",
                        name: "IP Latitude",
                    },
                    {
                        slug: "ip_longitude",
                        name: "IP Longitude",
                    },
                    {
                        slug: "last_activity",
                        name: "Dernière activité",
                    },
                ].map((field) => ({
                    type: "default-field",
                    name: field.name,
                    icon: "fa fa-columns",
                    columnId: field.slug,
                    headerId: "hc-users-table-header-default-" + field.slug,
                })),
            ]
                .map((column, i) => {
                    const index = this.userSettingsUsersTable.findIndex(
                        (c) => c.key == column.columnId
                    );
                    const c = this.userSettingsUsersTable.find(
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
                    removeStringAccent(column.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
