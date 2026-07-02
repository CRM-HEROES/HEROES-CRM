<template>
    <card-menu-list>
        <template v-if="bulkDisabled">
            <card-menu
                v-if="filtersActivated"
                icon="fa fa-filter"
                :label="$t('user.table.footer.init_filters')"
                title="Réinitialiser les filtres"
                style="background-color: #e6effd"
                color="#1a73e8"
                @click="initParams"
            />
            <card-menu
                icon="fa fa-ellipsis-h"
                :label="$t('user.table.footer.toggle_user_menus')"
                @click="toggleUsersOptions"
            />
            <div class="hc-users-table-footer-select">
                <select v-model="count">
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                    <option value="300">300</option>
                    <option value="400">400</option>
                    <option value="500">500</option>
                </select>
                <loading :loading="settingUsersCount" />
            </div>
            <card-menu
                icon="fa fa-plus"
                :label="$t('user.table.footer.add_user')"
                @click="addUser"
                color="#12a0f3"
            />
            <card-menu
                icon="fa fa-table"
                :label="$t('user.table.footer.add_column')"
                @click="addColumn"
            />
            <card-menu
                :disabled="fetchingPreviousPage || globalUsersPage <= 1"
                icon="fa fa-caret-left"
                :label="$t('user.table.footer.previous')"
                @click="previousPage"
            >
                <loading :loading="fetchingPreviousPage" />
            </card-menu>
            <card-menu
                :disabled="fetchingNextPage || users.length < globalUsersCount"
                icon="fa fa-caret-right"
                :label="$t('user.table.footer.next')"
                @click="nextPage"
            >
                <loading :loading="fetchingNextPage" />
            </card-menu>
        </template>

        <template v-else>
            <card-menu
                icon="fa fa-check-square"
                :label="$t('user.table.footer.deselect')"
                @click="bulkDeselect"
            />
            <card-menu
                icon="fa fa-trash"
                :label="$t('user.table.footer.bulk_delete')"
                color="#d9402c"
                @click="bulkRemove"
            >
                <loading :loading="bulkingRemove" />
            </card-menu>

            <card-menu
                icon="fa fa-calendar"
                :label="$t('user.table.footer.bulk_calendars')"
                @click="bulkCalendar"
            />
            <card-menu
                icon="fa fa-tags"
                :label="$t('user.table.footer.bulk_categories')"
                @click="bulkCategory"
            />
            <card-menu
                icon="fa fa-file-pdf"
                :label="$t('user.table.footer.bulk_documents')"
                @click="bulkDocument"
            />
            <card-menu
                icon="fa fa-folder"
                :label="$t('user.table.footer.bulk_folders')"
                @click="bulkFolder"
            />
            <card-menu
                icon="fa fa-users"
                :label="$t('user.table.footer.bulk_groups')"
                @click="bulkGroup"
            />
            <card-menu
                icon="fa fa-list"
                :label="$t('user.table.footer.bulk_menus')"
                @click="bulkMenu"
            />
            <card-menu
                icon="fa fa-person-digging"
                :label="$t('user.table.footer.bulk_order_actions')"
                @click="bulkOrderAction"
            />
            <card-menu
                icon="fa fa-step-forward"
                :label="$t('user.table.footer.bulk_order_steps')"
                @click="bulkOrderStep"
            />
            <card-menu
                icon="fa fa-user-tie"
                :label="$t('user.table.footer.bulk_roles')"
                @click="bulkRole"
            />
            <card-menu
                icon="fa fa-envelope"
                :label="$t('user.table.footer.bulk_threads')"
                @click="bulkThread"
            />
            <card-menu
                icon="fa fa-user-plus"
                :label="$t('user.table.footer.bulk_users')"
                @click="bulkUser"
            />
        </template>

        <!--card-menu icon="fa fa-search" :label="$t('user.table.footer.search_and_replace')" /-->
    </card-menu-list>
</template>

<style>
.hc-users-table-footer-select {
    position: relative;
    width: unset !important;
    min-width: unset !important;
}

.hc-users-table-footer-select > select {
    border: 1px solid #dddddd;
    background: none;
    padding: 3px 20px 3px 8px;
    border-radius: 7px;
    font-size: 12px;
    -webkit-appearance: none;
    -moz-appearance: none;
    height: 40px;
    width: auto;
}

.hc-users-table-footer-select:after {
    content: "";
    position: absolute;
    right: 7px;
    top: 17px;
    width: 0;
    height: 0;
    border-radius: 2px;
    border-top: 5px solid #555555;
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { OPEN_MODAL } from "@/actions/modal";
import { OPEN_SLIDE } from "@/actions/slide";
import {
    BULK_GLOBAL_REMOVE_USER,
    FETCH_GLOBAL_USERS,
    UPDATE_GLOBAL_SELECTED_USERS,
    INIT_GLOBAL_USER_PARAMS,
    SET_GLOBAL_USERS_PAGE,
    TOGGLE_GLOBAL_USERS_OPTIONS,
    SET_GLOBAL_USERS_COUNT,
} from "@/actions/user";
import { API_URL } from "@/apis/common";

export default {
    data() {
        return {
            createdUsers: 0,
            bulkingRemove: false,
            bulkingRestore: false,
            bulkingProcessed: false,
            bulkingNotProcessed: false,
            fetchingPreviousPage: false,
            fetchingNextPage: false,
            settingUsersCount: false,
        };
    },

    mounted() {
        if (!this.globalUsersOptions) {
            this.toggleUsersOptions();
        }
    },

    methods: {
        /**
         * Deselect selected user
         */
        bulkDeselect() {
            store.commit(UPDATE_GLOBAL_SELECTED_USERS, []);
        },

        /**
         * Store new user
         */
        async addUser() {
            store.commit(OPEN_MODAL, "global-user-add");
        },

        /**
         * Add other columns
         * in the table
         */
        addColumn() {
            store.commit(OPEN_MODAL, "global-users-table-add-column");
        },

        /**
         * Previous page
         */
        async previousPage() {
            store.commit(
                SET_GLOBAL_USERS_PAGE,
                parseInt(this.globalUsersPage) - 1
            );

            // Refresh users list
            this.fetchingPreviousPage = true;

            try {
                await store.dispatch(FETCH_GLOBAL_USERS);
            } finally {
                this.fetchingPreviousPage = false;
            }
        },

        /**
         * Next page
         */
        async nextPage() {
            store.commit(
                SET_GLOBAL_USERS_PAGE,
                parseInt(this.globalUsersPage) + 1
            );

            // Refresh users list
            this.fetchingNextPage = true;

            try {
                await store.dispatch(FETCH_GLOBAL_USERS);
            } finally {
                this.fetchingNextPage = false;
            }
        },

        /**
         * Init filters
         */
        initParams() {
            store.commit(INIT_GLOBAL_USER_PARAMS);
            store.dispatch(FETCH_GLOBAL_USERS);
        },

        /**
         * Toggle users options
         * Show or hide each user menus (sms, phone, order, ..)
         * from the users table
         */
        toggleUsersOptions() {
            store.commit(TOGGLE_GLOBAL_USERS_OPTIONS);
        },

        /**
         * Bulk calendars for selected users
         * See: @/components/user/bulk/calendar/Slide.vue
         */
        bulkCalendar() {
            store.commit(OPEN_SLIDE, "user-bulk-manage-calendars");
        },

        /**
         * Bulk categories for selected users
         * See: @/components/user/bulk/category/Slide.vue
         */
        bulkCategory() {
            store.commit(OPEN_SLIDE, "user-bulk-manage-categories");
        },

        /**
         * Bulk documents for selected users
         * See: @/components/user/bulk/document/Slide.vue
         */
        bulkDocument() {
            store.commit(OPEN_SLIDE, "user-bulk-manage-documents");
        },

        /**
         * Bulk folders for selected users
         * See: @/components/user/bulk/folder/Slide.vue
         */
        bulkFolder() {
            store.commit(OPEN_SLIDE, "user-bulk-manage-folders");
        },

        /**
         * Bulk groups for selected users
         * See: @/components/user/bulk/group/Slide.vue
         */
        bulkGroup() {
            store.commit(OPEN_SLIDE, "user-bulk-manage-groups");
        },

        /**
         * Bulk menus for selected users
         * See: @/components/user/bulk/menu/Slide.vue
         */
        bulkMenu() {
            store.commit(OPEN_SLIDE, "user-bulk-manage-menus");
        },

        /**
         * Bulk order actions for selected users
         * See: @/components/user/bulk/order-action/Slide.vue
         */
        bulkOrderAction() {
            store.commit(OPEN_SLIDE, "user-bulk-manage-order-actions");
        },

        /**
         * Bulk order_steps for selected users
         * See: @/components/user/bulk/order-step/Slide.vue
         */
        bulkOrderStep() {
            store.commit(OPEN_SLIDE, "user-bulk-manage-order-steps");
        },

        /**
         * Bulk users for selected roles
         * See: @/components/user/bulk/role/Slide.vue
         */
        bulkRole() {
            store.commit(OPEN_SLIDE, "user-bulk-manage-roles");
        },

        /**
         * Bulk threads for selected users
         * See: @/components/user/bulk/thread/Slide.vue
         */
        bulkThread() {
            store.commit(OPEN_SLIDE, "user-bulk-manage-threads");
        },

        /**
         * Bulk users for selected users
         * See: @/components/user/bulk/user/Slide.vue
         */
        bulkUser() {
            store.commit(OPEN_SLIDE, "user-bulk-manage-users");
        },

        /**
         * Remove multiple users
         */
        async bulkRemove() {
            this.bulkingRemove = true;

            // Selected users
            const globalUsersSelected = this.globalUsersSelected;
            store.commit(UPDATE_GLOBAL_SELECTED_USERS, []);

            try {
                // Dispatch bulk remove
                await store.dispatch(
                    BULK_GLOBAL_REMOVE_USER,
                    globalUsersSelected
                );

                // Refresh users list
                store.dispatch(FETCH_GLOBAL_USERS);
            } finally {
                this.bulkingRemove = false;
            }
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "globalUsersSelected",
            "users",
            "globalUsersParamExists",
            "fields",
            "globalUsersPage",
            "globalUsersOptions",
            "globalUsersCount",
            "usersParams",
            "globalUsersParamsUrl",
            "usersSortOrder",
            "usersSortBy",
        ]),

        /**
         * Check if there are
         * selected users
         */
        bulkDisabled() {
            return this.globalUsersSelected.length == 0;
        },

        /**
         * Check if
         * all selected users
         * is processed
         */
        selectedUsersProcessed() {
            return (
                this.users.filter(
                    (p) =>
                        this.globalUsersSelected.indexOf(p.id) >= 0 &&
                        p.processed_at
                ).length == this.globalUsersSelected.length
            );
        },

        /**
         * Check if
         * all selected users
         * is not processed
         */
        selectedUsersNotProcessed() {
            return (
                this.users.filter(
                    (p) =>
                        this.globalUsersSelected.indexOf(p.id) >= 0 &&
                        !p.processed_at
                ).length == this.globalUsersSelected.length
            );
        },

        /**
         * Check if
         * there are activated filters
         */
        filtersActivated() {
            return (
                this.globalUsersParamExists() ||
                this.usersSortOrder ||
                this.usersSortBy
            );
        },

        count: {
            get: function () {
                return this.globalUsersCount;
            },
            set: async function (value) {
                store.commit(SET_GLOBAL_USERS_COUNT, value);
                this.settingUsersCount = true;

                try {
                    await store.dispatch(FETCH_GLOBAL_USERS);
                } finally {
                    this.settingUsersCount = false;
                }
            },
        },
    },
};
</script>
