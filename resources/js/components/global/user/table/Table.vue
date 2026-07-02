<template>
    <div
        :class="['hc-table', globalUsersOptions ? 'hc-table-show-options' : '']"
    >
        <table v-if="this.columns.length > 0">
            <draggable
                tag="thead"
                :list="notFixedColumns"
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
                            <a></a>
                        </div>

                        <draggable
                            tag="div"
                            :list="fixedColumns"
                            class="hc-table-fixed-headers"
                            item-key="key"
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
                    v-for="(user, index) in globalUsers"
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
import { GET_USER_SETTING, UPDATE_USER_SETTING } from "@/actions/user/setting";
import {
    FETCH_GLOBAL_USERS,
    UPDATE_GLOBAL_SELECTED_USERS,
    INIT_GLOBAL_USER_PARAMS,
    SET_GLOBAL_USER_PARAMS,
    SET_GLOBAL_USERS_PAGE,
    SET_GLOBAL_USERS_COUNT,
    SET_GLOBAL_USERS_FIELDS,
    SET_GLOBAL_USERS_SORT_ORDER,
    SET_GLOBAL_USERS_SORT_BY,
} from "@/actions/user";

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
            fields: [
                {
                    slug: "name",
                    name: "Nom",
                    meta: false,
                },
                {
                    slug: "last_name",
                    name: "Prénom",
                    meta: false,
                },
                {
                    slug: "email",
                    name: "Email",
                    meta: false,
                },
                {
                    slug: "street",
                    name: "Rue",
                    meta: false,
                },
                {
                    slug: "street_bis",
                    name: "Rue 2",
                    meta: false,
                },
                {
                    slug: "postal_code",
                    name: "CP",
                    meta: false,
                },
                {
                    slug: "city",
                    name: "Ville",
                    meta: false,
                },
                {
                    slug: "country",
                    name: "Pays",
                    meta: false,
                },
                {
                    slug: "ip_city",
                    name: "IP Ville",
                    meta: false,
                },
                {
                    slug: "ip_country",
                    name: "IP Pays",
                    meta: false,
                },
                {
                    slug: "ip_state",
                    name: "IP Région",
                    meta: false,
                },
                {
                    slug: "ip_postal_code",
                    name: "IP Code postal",
                    meta: false,
                },
                {
                    slug: "ip_latitude",
                    name: "IP Latitude",
                    meta: false,
                },
                {
                    slug: "ip_longitude",
                    name: "IP Longitude",
                    meta: false,
                },
                {
                    slug: "last_activity",
                    name: "Dernière activité",
                    meta: false,
                },
                {
                    slug: "ip_address",
                    name: "Adresse IP",
                    meta: false,
                },
            ],
        };
    },

    created() {
        this.updateParamsFromUrl();
        this.getColumns();
        this.getUsers();
    },

    methods: {
        /**
         * Update params from URL
         */
        updateParamsFromUrl() {
            store.commit(INIT_GLOBAL_USER_PARAMS);
            store.commit(SET_GLOBAL_USERS_FIELDS, null);
            store.commit(SET_GLOBAL_USERS_SORT_BY, "id");
            store.commit(SET_GLOBAL_USERS_SORT_ORDER, "desc");

            const url = new URL(window.location.href);
            const searchParams = new URLSearchParams(url.search);
            const filters = {};

            let globalUsersCount = 50;

            searchParams.forEach(function (value, param) {
                if (param == "page") {
                    store.commit(SET_GLOBAL_USERS_PAGE, parseInt(value));
                } else if (param == "count") {
                    globalUsersCount = parseInt(value);
                } else if (param == "fields") {
                    store.commit(SET_GLOBAL_USERS_FIELDS, value);
                } else if (param == "sortOrder") {
                    store.commit(SET_GLOBAL_USERS_SORT_ORDER, value);
                } else if (param == "sortBy") {
                    store.commit(SET_GLOBAL_USERS_SORT_BY, value);
                } else if (param == "filters") {
                    try {
                        value = JSON.parse(value);

                        for (let key in value) {
                            filters[key] = value[key];
                        }
                    } catch (e) {}
                }
            });

            store.commit(SET_GLOBAL_USER_PARAMS, filters);
            store.commit(SET_GLOBAL_USERS_COUNT, globalUsersCount);
        },

        /**
         * Get user columns
         */
        getColumns() {
            store.dispatch(GET_USER_SETTING, "global-users-table");
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
         * Get project users
         */
        async getUsers() {
            this.loading = true;

            try {
                await store.dispatch(FETCH_GLOBAL_USERS);
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
            let settings = this.userSettingsUsersTable;
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
                store.dispatch(UPDATE_USER_SETTING, {
                    key: "global-users-table",
                    value: newSettings,
                });
            }
        },
    },

    computed: {
        ...mapGetters([
            "userSettingsUsersTable",
            "globalUsers",
            "globalUsersSelected",
            "globalUsersOptions",
            "globalUsersParamExists",
            "globalUsersParamsUrl",
            "globalUsersPage",
            "globalUsersCount",
        ]),

        /**
         *
         */
        selectAll: {
            get() {
                return (
                    this.globalUsers.length == this.globalUsersSelected.length
                );
            },
            set(value) {
                store.commit(
                    UPDATE_GLOBAL_SELECTED_USERS,
                    value ? this.globalUsers.map((p) => p.id) : []
                );
            },
        },

        /**
         * Columns
         */
        columns: function () {
            return this.userSettingsUsersTable
                .map((column, index) => {
                    const key = column.key;
                    let type = "list";
                    let category = key;
                    let id = key;
                    let name;

                    // Project
                    if (key == "projects") {
                        name = this.$t("user.table.column.others.projects");
                        // Meta field
                    } else if (key == "last-project") {
                        name = this.$t("user.table.column.others.last_project");
                        // Meta field
                    } else {
                        const slug = key;

                        const field = this.fields.find(
                            (field) =>
                                /*field.for == "user" &&
                                !field.meta &&*/
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
    },

    watch: {
        globalUsersParamsUrl(newValue) {
            let url =
                "?page=" +
                this.globalUsersPage +
                "&count=" +
                this.globalUsersCount;

            if (newValue) {
                url += "&filters=" + newValue;
            }

            history.pushState(null, null, url);
        },
    },
};
</script>
