<template>
    <item @click="addOrRemove">
        <icon
            :class="column.icon"
            :style="column.iconColor ? { color: column.iconColor } : {}"
        />
        <div class="hc-item-main-content" v-text="column.name"></div>
        <slot></slot>
        <template v-if="existingColumn && !existingColumn.hidden">
            <icon
                :class="
                    existingColumn.fixed
                        ? 'fa fa-thumbtack icon-blue'
                        : 'fa fa-thumbtack icon-grey'
                "
                @click.prevent.stop="togglePinColumn"
            />
            <icon
                tag="a"
                :class="'fa fa-eye icon-blue'"
                @click.prevent.stop="
                    scrollToCorrespondingColumn(), closeModal()
                "
            />
            <icon class="fa fa-times icon-red" />
        </template>
        <icon v-else class="fa fa-plus" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { CLOSE_MODAL } from "@/actions/modal";
import { FETCH_USERS } from "@/actions/project/user";
import { UPDATE_USER_SETTING } from "@/actions/user/setting";

export default {
    props: {
        /**
         * Column
         */
        column: {
            type: Object,
        },
    },

    methods: {
        /**
         * Add thread as column
         * to the users table
         * for the current user
         */
        async addOrRemove() {
            if (this.existingColumn && !this.existingColumn.hidden) {
                this.removeColumn();
            } else {
                this.addColumn();
            }
        },

        /**
         * Add column
         * to the users table
         * for the current user
         */
        async addColumn() {
            // Get user users table settings
            let newSettings = this.userSettingsUsersTable;

            const index = newSettings.findIndex(
                (column) => column.key === this.column.columnId
            );

            if (index >= 0) {
                delete newSettings[index].hidden;
            } else {
                // Add new column
                newSettings.push({
                    key: this.column.columnId,
                });
            }

            try {
                // Update setting
                await store.dispatch(UPDATE_USER_SETTING, {
                    key: "global-users-table",
                    value: newSettings,
                });
                // And refresh users list
                store.dispatch(FETCH_USERS);

                await this.$nextTick();
                this.scrollToCorrespondingColumn();
            } finally {
            }
        },

        /**
         * Remove column
         * from the users table
         * for the current user
         */
        async removeColumn() {
            // Get user users table settings
            let newSettings = this.userSettingsUsersTable.map((column) =>
                column.key === this.column.columnId
                    ? { ...column, hidden: true }
                    : column
            );

            // Update setting
            store.dispatch(UPDATE_USER_SETTING, {
                key: "global-users-table",
                value: newSettings,
            });
        },

        /**
         * Pin or do not pin the column
         */
        togglePinColumn() {
            let settings = this.userSettingsUsersTable;
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

            // Update user users table setting
            store.dispatch(UPDATE_USER_SETTING, {
                key: "global-users-table",
                value: newSettings,
            });
        },

        /**
         * When we're inside the users table
         * Let scroll to the column
         * corresponding to the first filter
         * from the menu
         */
        scrollToCorrespondingColumn() {
            const header = document.getElementById(this.column.headerId);

            if (!header) {
                return;
            }

            header.scrollIntoView();
        },

        /**
         *
         */
        closeModal() {
            store.commit(CLOSE_MODAL);
        },
    },

    computed: {
        ...mapGetters(["userSettingsUsersTable", "userSettingsUsersTableHas"]),

        existingColumn() {
            return this.userSettingsUsersTable.find(
                (c) => c.key == this.column.columnId
            );
        },

        existing() {
            return this.userSettingsUsersTableHas(this.column.columnId);
        },
    },
};
</script>
