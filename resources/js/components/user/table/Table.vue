<template>
    <div :class="['hc-table', usersOptions ? 'hc-table-show-options' : '']">
        <table v-if="this.columns.length > 0">
            <draggable
                tag="thead"
                :list="notFixedColumns"
                item-key="key"
                :disabled="!headerDraggable"
                @end="notFixedColumnMoved"
            >
                <template #header>
                    <th class="fixed">
                        <label class="hc-table-selector">
                            <input type="checkbox" v-model="selectAll" />
                            <span></span>
                        </label>

                        <div class="hc-table-row-options">
                            <a></a>
                            <a></a>
                            <a></a>
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
                                <header-cell tag="div" :column="element" />
                            </template>
                        </draggable>
                    </th>
                </template>

                <template #item="{ element }">
                    <header-cell :column="element" />
                </template>
            </draggable>

            <tbody>
                <row
                    v-for="(user, index) in users"
                    :key="user.id"
                    :index="index"
                    :user="user"
                    :fixed-columns="fixedColumns"
                    :not-fixed-columns="notFixedColumns"
                />
            </tbody>
        </table>

        <!--loading :loading="loading" /-->
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { OPEN_SLIDE } from "@/actions/slide";
import { UPDATE_PROJECT_USER_SETTING } from "@/actions/project/user/setting";
import { FETCH_FIELDS } from "@/actions/project/field";
import {
    FETCH_USERS,
    UPDATE_SELECTED_USERS,
    INIT_USER_PARAMS,
    SET_USER_PARAMS,
    SET_USERS_PAGE,
    SET_USERS_COUNT,
    SET_USERS_FIELDS,
    SET_USERS_SORT_ORDER,
    SET_USERS_SORT_BY,
} from "@/actions/project/user";

// Components
import Row from "./Row.vue";
import HeaderCell from "./HeaderCell.vue";

export default {
    components: {
        Row,
        HeaderCell,
    },

    data() {
        return {
            loading: false,
        };
    },

    created() {
        this.updateParamsFromUrl();
        this.getUsers();
    },

    methods: {
        /**
         * Update params from URL
         */
        updateParamsFromUrl() {
            store.commit(INIT_USER_PARAMS);
            store.commit(SET_USERS_FIELDS, null);
            store.commit(SET_USERS_SORT_BY, "id");
            store.commit(SET_USERS_SORT_ORDER, "desc");

            const url = new URL(window.location.href);
            const searchParams = new URLSearchParams(url.search);
            const filters = {};

            let usersCount = 50;

            searchParams.forEach(function (value, param) {
                if (param == "page") {
                    store.commit(SET_USERS_PAGE, parseInt(value));
                } else if (param == "count") {
                    usersCount = parseInt(value);
                } else if (param == "fields") {
                    store.commit(SET_USERS_FIELDS, value);
                } else if (param == "sortOrder") {
                    store.commit(SET_USERS_SORT_ORDER, value);
                } else if (param == "sortBy") {
                    store.commit(SET_USERS_SORT_BY, value);
                } else if (param == "filters") {
                    try {
                        value = JSON.parse(value);

                        for (let key in value) {
                            filters[key] = value[key];
                        }
                    } catch (e) {}
                }
            });

            store.commit(SET_USER_PARAMS, filters);
            store.commit(SET_USERS_COUNT, usersCount);
        },

        /**
         * Get project fields
         */
        getProjectFields() {
            store.dispatch(FETCH_FIELDS);
        },

        /**
         * Get project categories
         */
        getProjectCategories() {
            store.dispatch(FETCH_CATEGORIES);
        },

        /**
         * Import users (XLSX, CSV)
         */
        importUsers() {
            store.commit(OPEN_SLIDE, "import");
            store.commit(OPEN_MODAL, "import-add");
        },

        /**
         * Get project users
         */
        async getUsers() {
            this.loading = true;

            try {
                await store.dispatch(FETCH_USERS);

                // Show add import modal
                // when project does not
                // have user
                if (
                    !this.usersParamExists() &&
                    this.users.length == 0 &&
                    this.usersPage == 1
                ) {
                    this.importUsers();
                }
            } finally {
                this.loading = false;
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
            let settings = this.projectUserSettingsUsersTable;
            let newSettings;

            const oldIndex = fixed
                ? this.columns.filter((c) => c.fixed)[e.oldDraggableIndex].index
                : this.columns.filter((c) => !c.fixed)[e.oldDraggableIndex]
                      .index;
            const newIndex = fixed
                ? this.columns.filter((c) => c.fixed)[e.newDraggableIndex].index
                : this.columns.filter((c) => !c.fixed)[e.newDraggableIndex]
                      .index;

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
            "projectUserSettingsUsersTable",
            "fields",
            "categories",
            "threads",
            "users",
            "project",
            "usersSelected",
            "usersOptions",
            "usersParamExists",
            "usersParamsUrl",
            "usersPage",
            "usersCount",
        ]),

        /**
         *
         */
        selectAll: {
            get() {
                return this.users.length == this.usersSelected.length;
            },
            set(value) {
                store.commit(
                    UPDATE_SELECTED_USERS,
                    value ? this.users.map((p) => p.id) : []
                );
            },
        },

        /**
         * Columns
         */
        columns: function () {
            return this.projectUserSettingsUsersTable
                .map((column, index) => {
                    const key = column.key;
                    let type = "list";
                    let category = key;
                    let id = key;
                    let name;

                    // Calendar
                    if (key == "calendars") {
                        name = this.$t("user.table.column.others.calendars");
                        // Category
                    } else if (key == "categories") {
                        name = this.$t("user.table.column.others.categories");
                        // Document
                    } else if (key == "documents") {
                        name = this.$t("user.table.column.others.documents");
                        // Folder
                    } else if (key == "folders") {
                        name = this.$t("user.table.column.others.folders");
                        // Group
                    } else if (key == "groups") {
                        name = this.$t("user.table.column.others.groups");
                        // Menu
                    } else if (key == "menus") {
                        name = this.$t("user.table.column.others.menus");
                        // Order Action
                    } else if (key == "order_actions") {
                        name = this.$t(
                            "user.table.column.others.order_actions"
                        );
                        // Order Step
                    } else if (key == "order_steps") {
                        name = this.$t("user.table.column.others.order_steps");
                        // Questionnaire
                    } else if (key == "questionnaires") {
                        name = this.$t(
                            "user.table.column.others.questionnaires"
                        );
                        // Role
                    } else if (key == "roles") {
                        name = this.$t("user.table.column.others.roles");
                        // Thread
                    } else if (key == "threads") {
                        name = this.$t("user.table.column.others.threads");
                        // User
                    } else if (key == "assignable_users") {
                        name = this.$t("user.table.column.others.users");
                        // Meta field
                    } else if (key.indexOf("meta->") == 0) {
                        const slug = key.replace("meta->", "");

                        const field = this.fields.find(
                            (field) =>
                                field.for == "user" &&
                                field.meta &&
                                field.slug == slug
                        );

                        if (!field) {
                            return null;
                        }

                        name = field.name;
                        type = field.type;
                        category = "meta";
                        id = field.slug;
                    } else {
                        const slug = key;

                        const field = this.fields.find(
                            (field) =>
                                field.for == "user" &&
                                !field.meta &&
                                field.slug == slug
                        );

                        if (!field) {
                            return null;
                        }

                        name = field.name;
                        type = field.type;
                        category = "default";
                    }

                    return {
                        ...column,
                        name: name,
                        type: type,
                        index: index,
                        id: id,
                        category: category,
                    };
                })
                .filter((column) => column);
        },

        /**
         *
         */
        fixedColumns() {
            return this.columns.filter((c) => c.fixed && !c.hidden);
        },

        /**
         *
         */
        notFixedColumns() {
            return this.columns.filter((c) => !c.fixed && !c.hidden);
        },

        headerDraggable() {
            return deviceType() == "desktop";
        },
    },

    watch: {
        usersParamsUrl(newValue) {
            let url = "?page=" + this.usersPage + "&count=" + this.usersCount;

            if (newValue) {
                url += "&filters=" + newValue;
            }

            history.pushState(null, null, url);
        },
    },
};
</script>
