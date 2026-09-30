<template>
    <!-- body -->
    <div style="width: 100%; height: auto">
        <div
            style="
                width: 100%;
                height: auto;
                text-align: center;
                padding-top: 16px;
            "
        >
            <img style="width: 25px" src="/images/email/rocket.png" />
            <br />
            <span
                style="
                    font-size: 18px;
                    font-weight: bold;
                    display: inline-block;
                    margin-top: 16px;
                "
                v-text="project.name"
            ></span>
            <br />
            <span
                style="
                    font-size: 14px;
                    text-align: center;
                    display: inline-block;
                    margin-top: 8px;
                    margin-bottom: 16px;
                    line-height: 24px;
                    width: 100%;
                    padding: 0 30px;
                    box-sizing: border-box;
                "
                >Que souhaitez-vous avoir comme colonnes dans le tableau de
                prospects du projet Nethel ?</span
            >
            <br />
            <div style="width: 100%; text-align: left">
                <draggable
                    tag="item-list"
                    :disabled="!movable"
                    :list="availableColumns.filter((c) => c.fixed)"
                    item-key="columnId"
                    style="
                        border-bottom: 5px solid #deeff5;
                        height: auto;
                        overflow: visible;
                    "
                    group="fixed-columns"
                    @end="fixedColumnMoved"
                >
                    <template #item="{ element }">
                        <column-row
                            :column="element"
                            :existing-column="existingColumn(element)"
                            :new-column="false"
                            @add="addColumn"
                            @remove="removeColumn"
                            @toggle-pin="togglePinColumn"
                        />
                    </template>
                </draggable>

                <!-- Not fixed columns -->

                <draggable
                    tag="item-list"
                    :disabled="!movable"
                    :list="availableColumns.filter((c) => c.fixed === false)"
                    item-key="columnId"
                    style="
                        border-bottom: 5px solid #deeff5;
                        height: auto;
                        overflow: visible;
                    "
                    group="not-fixed-columns"
                    @end="notFixedColumnMoved"
                >
                    <template #item="{ element }">
                        <column-row
                            :column="element"
                            :existing-column="existingColumn(element)"
                            :new-column="false"
                            @add="addColumn"
                            @remove="removeColumn"
                            @toggle-pin="togglePinColumn"
                        />
                    </template>
                </draggable>

                <!-- Not added columns -->

                <column-row
                    v-for="element in availableColumns.filter(
                        (c) => c.fixed === null
                    )"
                    :column="element"
                    :new-column="true"
                    @add="addColumn"
                />
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div style="width: 100%; height: auto">
        <div
            style="
                width: 100%;
                height: auto;
                box-sizing: border-box;
                padding: 30px 10px;
                text-align: center;
            "
        >
            <router-link
                :to="{
                    name: 'prospect',
                    params: {
                        project: project.slug,
                    },
                }"
                style="
                    padding: 12px 16px;
                    background-color: #49bde1;
                    color: white;
                    font-size: 15px;
                    border-radius: 8px;
                    text-decoration: none;
                "
                target="_blank"
                >Vor le projet</router-link
            >
        </div>
    </div>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import ColumnRow from "./ColumnRow";
import { UPDATE_PROJECT_USER_SETTING } from "@/actions/project/user/setting";
import { FETCH_PROSPECTS } from "@/actions/project/prospect";

export default {
    components: {
        ColumnRow,
    },

    data() {
        return {
            prospectsTableSetting: [],
            keyword: "",
            movable: deviceType() == "desktop",
            /*columns: [
                {
                    name: "Prénom",
                    description: "Prénom du prospect",
                    key: "last_name",
                },
                { name: "Nom", key: "first_name" },
                { name: "RDV", key: "events" },
                { name: "Devis", key: "orders", hidden: true },
                { name: "Email", key: "email" },
                { name: "Appels", key: "interactions", hidden: true },
                { name: "SMS", key: "sms", hidden: true },
                { name: "Téléphone fixe", key: "phone_number" },
                { name: "Téléphone Mobile", key: "mobile_phone_number" },
                { name: "Rue", key: "street" },
                { name: "Ville", key: "city" },
                { name: "Code postal", key: "postal_code" },
                { name: "Pays", key: "country" },
                {
                    name: "Date de création",
                    description:
                        "Date à laquelle le prospect a été créé sur le CRM",
                    key: "created_at",
                },
                { name: "Date de modification", key: "updated_at" },
                { name: "Suivi Nethel", key: "category->12" },
                { name: "Num TVA", key: "meta->num_tva" },
                {
                    name: "Surface habitable en m2",
                    key: "meta->surface_habitable_en_m",
                },
                {
                    name: "orientation_de_la_toiture",
                    key: "meta->orientation_de_la_toiture",
                },
                { name: "Zone d'ombre", key: "meta->zone_dombre" },
                { name: "Champ test", key: "meta->champ_test" },
                { name: "sss", key: "meta->sss" },
                { name: "Position", key: "meta->position" },
                { name: "date_of_birth", key: "date_of_birth" },
                { name: "manque_doc", key: "meta->manque_doc" },
            ],*/
        };
    },

    created() {
        this.prospectsTableSetting = this.projectUserSettingsProspectsTable;
    },

    methods: {
        async fetchProspectsTableSetting() {
            this.fetchingProspectsTableSetting = true;

            try {
                this.prospectsTableSetting = await store.dispatch(
                    GET_PROJECT_USER_SETTING,
                    {
                        key: "prospects-table",
                        user: this.user.id,
                    }
                );
            } finally {
                this.fetchingProspectsTableSetting = false;
            }
        },

        async setProspectsTableSetting(settings, refetchProspects) {
            this.prospectsTableSetting = settings;
            await store.dispatch(UPDATE_PROJECT_USER_SETTING, {
                key: "prospects-table",
                value: settings,
                user: this.user.id,
            });

            if (refetchProspects) {
                store.dispatch(SHOW_PROJECT, this.project.slug);
                store.dispatch(FETCH_PROSPECTS);
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
            let settings = this.prospectsTableSetting.filter((c) =>
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
                this.setProspectsTableSetting(newSettings);
            }
        },

        /**
         * Add column
         * to the prospects table
         * for the current user
         */
        async addColumn(c) {
            // Get user prospects table settings
            let newSettings = this.prospectsTableSetting;

            const index = newSettings.findIndex(
                (column) => column.key === c.columnId
            );

            if (index >= 0) {
                delete newSettings[index].hidden;
            } else {
                // Add new column
                newSettings.push({
                    key: c.columnId,
                });
            }

            this.prospectsTableSetting = newSettings;

            try {
                // Update setting
                this.setProspectsTableSetting(newSettings, true);

                await this.$nextTick();
                this.scrollToCorrespondingColumn(c);
            } finally {
            }
        },

        /**
         * Remove column
         * from the prospects table
         * for the current user
         */
        async removeColumn(c) {
            // Get user prospects table settings
            let newSettings = this.prospectsTableSetting.map((column) =>
                column.key === c.columnId ? { ...column, hidden: true } : column
            );

            // Update setting
            this.setProspectsTableSetting(newSettings);
        },

        /**
         * Pin or do not pin the column
         */
        togglePinColumn(column) {
            let settings = this.prospectsTableSetting;
            const count = settings.filter((c) => c.fixed).length;
            const index = column.index;

            let newSettings;

            // Do not fix the column
            if (column.fixed) {
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
            this.setProspectsTableSetting(newSettings);
        },

        /**
         */
        existingColumn(column) {
            return this.prospectsTableSetting.find(
                (c) => c.key == column.columnId
            );
        },
    },

    watch: {
        projectUserSettingsProspectsTable(newValue) {
            this.prospectsTableSetting = newValue;
        },
    },

    computed: {
        ...mapGetters("auth", ["user"]),
        ...mapGetters([
            "projectUserSettingsProspectsTable",
            "fields",
            "categories",
            "threads",
            "project",
        ]),

        columns() {
            return [
                // Events
                {
                    type: "other",
                    name: this.$t("prospect.table.column.others.appointments"),
                    icon: "fa fa-calendar icon-purple",
                    columnId: "events",
                },
                // Interactions
                {
                    type: "other",
                    name: this.$t("prospect.table.column.others.calls"),
                    icon: "fa fa-phone icon-orange",
                    columnId: "interactions",
                },
                // Sms
                {
                    type: "other",
                    name: this.$t("prospect.table.column.others.sms"),
                    icon: "fa fa-comments icon-purple",
                    columnId: "sms",
                },
                // Users
                {
                    type: "users",
                    name: this.$t("prospect.table.column.others.users"),
                    icon: "fa fa-user icon-brown",
                    columnId: "users",
                },
                // Groups
                {
                    type: "other",
                    name: this.$t("prospect.table.column.others.groups"),
                    icon: "fa fa-users icon-brown",
                    columnId: "groups",
                },
                // Orders
                {
                    type: "other",
                    name: this.$t("prospect.table.column.others.orders"),
                    icon: "fa fa-shopping-cart icon-cyan",
                    columnId: "orders",
                },
                // Import
                {
                    type: "other",
                    name: this.$t("prospect.table.column.others.import"),
                    icon: "fa fa-file-download icon-brown",
                    columnId: "import",
                },
                // Default fields
                ...this.fields
                    .filter((field) => field.for == "prospect" && !field.meta)
                    .map((field) => ({
                        type: "default-field",
                        name: field.name,
                        description: field.description,
                        icon: "fa fa-columns",
                        columnId: field.slug,
                    })),
                // Meta fields
                ...this.fields
                    .filter((field) => field.for == "prospect" && field.meta)
                    .map((field) => ({
                        type: "meta-field",
                        name: field.name,
                        description: field.description,
                        icon: "fa fa-plus-square",
                        columnId: "meta->" + field.slug,
                    })),
                // Categories
                ...this.categories.map((category) => ({
                    type: "category",
                    icon: "fa fa-tags",
                    iconColor: category.bgcolor,
                    columnId: "category->" + category.id,
                    ...category,
                })),
                // Threads
                ...this.threads.map((thread) => ({
                    type: "thread",
                    name: thread.name,
                    description: thread.description,
                    icon: thread.private
                        ? "fa fa-envelope"
                        : "fa fa-envelope-open",
                    iconColor: thread.bgcolor,
                    columnId: "thread->" + thread.id,
                })),
            ]
                .map((column, i) => {
                    const index = this.prospectsTableSetting.findIndex(
                        (c) => c.key == column.columnId
                    );
                    const c = this.prospectsTableSetting.find(
                        (c) => c.key == column.columnId
                    );

                    return {
                        ...column,
                        index: index >= 0 ? index : 1000 + i,
                        fixed: c ? (c.fixed ? true : false) : null,
                        hidden: c ? (c.hidden ? true : false) : null,
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
