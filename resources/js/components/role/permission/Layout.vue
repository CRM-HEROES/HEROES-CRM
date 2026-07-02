<template>
    <div
        class="hc-flex-column"
        style="height: 100%"
        v-if="permissionGroups.length > 0"
    >
        <item v-if="permissionGroups.length > 1" tag="a" @click.prevent="back">
            <icon class="fa fa-caret-left" />
            <div
                class="hc-item-main-content"
                v-text="currentPermissionGroup.name"
            ></div>
            <checkbox
                @click.stop
                :model-value="isPermissionChecked() || isPermissionDisabled()"
                :disabled="isPermissionDisabled()"
                @change="change"
            />
        </item>
        <item v-else>
            <icon class="fa fa-hand-paper" />
            <div
                class="hc-item-main-content"
                v-text="$t('permission.all')"
            ></div>
            <checkbox
                @click.stop
                :model-value="isPermissionChecked() || isPermissionDisabled()"
                :disabled="isPermissionDisabled()"
                @change="change"
            />
        </item>
        <search v-model="permissionKeyword" />
        <tab-layout
            :count="permissionGroups.length"
            :tab="permissionGroups.length - 1"
            class="hc-flex-1"
        >
            <template
                v-for="(permissionGroup, i) in permissionGroups"
                v-slot:[i+1]
            >
                <item-list class="hc-flex-1" padding="5px">
                    <permission-row
                        v-for="permission in permissionGroup.sub"
                        :key="permission.id"
                        :permission="permission"
                        :permission-value="currentPermissionValue(permission)"
                        :value="
                            isPermissionChecked(permission) ||
                            isPermissionDisabled(permission)
                        "
                        :disabled="isPermissionDisabled(permission)"
                        @click="setCurrentPermissionGroup(permission)"
                    />
                </item-list>
            </template>
        </tab-layout>
    </div>
</template>

<script>
import store from "@/store";
import { mapGetters } from "vuex";
import permissionsList from "@/utils/role-permissions";

// Actions
import {
    FETCH_ROLE_PERMISSIONS,
    ADD_ROLE_PERMISSION,
    REMOVE_ROLE_PERMISSION,
} from "@/actions/project/role/permission";

import { OPEN_SUB_SLIDE } from "@/actions/slide";

// Components
import PermissionRow from "./PermissionRow.vue";

export default {
    components: {
        PermissionRow,
    },

    data() {
        return {
            name: "role-manage-permissions",
            tab: 0,
            // Search keyword
            permissionKeyword: "",
            permissionGroups: [],
            // List of permissions
            permissions: permissionsList,
        };
    },

    created() {
        this.permissionGroups = [this.permissions];
    },

    methods: {
        /**
         * Go back
         * to the parent permission group
         */
        back() {
            if (this.permissionGroups.length > 1) {
                this.permissionGroups.pop();
            }
        },

        /**
         * Select a permission group
         * as current
         */
        setCurrentPermissionGroup(permission) {
            // Permission
            if (permission.sub) {
                this.currentPermissionGroup = permission;
                // Affected items like: affected categories, calendars, ...
            } else if (permission.action) {
                store.commit(OPEN_SUB_SLIDE, permission.action);
            }
        },

        /**
         * Concat the current selected permission group
         * and the permission key
         * Ex:
         * Permission group: "all.categories"
         * Permissison.key: "add"
         *
         * It returns "all.categories.add"
         */
        currentPermissionValue(permission) {
            return (
                this.permissionGroups.map((p) => p.key).join(".") +
                (permission ? "." + permission.key : "")
            );
        },

        /**
         * Fetch all permissions
         * of the current role
         */
        fetchRolePermissions() {
            if (this.role) {
                store.dispatch(FETCH_ROLE_PERMISSIONS, this.role);
            }
        },

        /**
         * Check if a permission name
         * is associated to a role
         * @param {*} permission
         */
        isPermissionNameChecked(permissionName) {
            return (
                this.rolePermissions.findIndex((l) => l == permissionName) >= 0
            );
        },

        /**
         * Check if a permission
         * is associated to a role
         * @param {*} permission
         */
        isPermissionChecked(permission) {
            const key = this.currentPermissionValue(permission);

            return this.isPermissionNameChecked(key);
        },

        /**
         * Check if a permission should be disabled
         * if a parent permission is alread selected
         * @param {*} permission
         */
        isPermissionDisabled(permission) {
            const key = this.currentPermissionValue(permission);

            let keys = [];
            for (let i in this.permissionGroups) {
                const permissionGroup = this.permissionGroups[i];
                keys.push(permissionGroup.key);
                const permissionGroupName = keys.join(".");

                if (permissionGroupName == key) {
                    return false;
                }

                if (this.isPermissionNameChecked(permissionGroupName)) {
                    return true;
                }
            }

            return false;
        },

        /**
         * When a permission is being selected
         * or deselected
         */
        change(event) {
            store.dispatch(
                event.target.checked
                    ? ADD_ROLE_PERMISSION
                    : REMOVE_ROLE_PERMISSION,
                this.currentPermissionValue()
            );
            this.$emit("change", event);
        },
    },

    computed: {
        ...mapGetters(["role", "rolePermissions", "roleFullName"]),

        currentPermissionGroup: {
            get() {
                return this.permissionGroups.length > 0
                    ? this.permissionGroups[this.permissionGroups.length - 1]
                    : null;
            },
            set(value) {
                this.permissionGroups.push(value);
            },
        },

        currentPermission() {
            const { key, name, icon } = this.currentPermissionGroup;
            return { key, name, icon };
        },

        currentAllPermissionValue() {
            return this.permissionGroups.map((p) => p.key).join(".");
        },

        /**
         * Filtered permissions
         */
        filteredPermissions: function (permissionGroup) {
            if (this.permissionKeyword) {
                let permissionGroups = [permissionGroup];

                for (let i = 0; i < permissionGroups.length; ++i) {
                    let permission = permissionGroups[i];

                    if (permission.sub) {
                        let keys = Object.keys(permission.sub);

                        for (let j = 0; j < keys.length; ++j) {
                            permissionGroups.push(permission.sub[keys[j]]);
                        }
                    }
                }

                let permissions = [];

                for (let i = 0; i < permissionGroups.length; ++i) {
                    let permission = permissionGroups[i];

                    if (permission.permissions) {
                        for (
                            let j = 0;
                            j < permission.permissions.length;
                            ++j
                        ) {
                            permissions.push(permission.permissions[j]);
                        }
                    }
                }

                let keyword = removeStringAccent(this.permissionKeyword);

                return permissions.filter(
                    (permission) =>
                        removeStringAccent(permission.description).indexOf(
                            keyword
                        ) >= 0
                );
            }

            return permissionGroup.permissions;
        },

        /**
         * Filtered permissions
         */
        filteredPermissionGroups: function (permissionGroup) {
            if (this.permissionKeyword) {
                let permissionGroups = [permissionGroup];

                for (let i = 0; i < permissionGroups.length; ++i) {
                    let permission = permissionGroups[i];

                    if (permission.sub) {
                        let keys = Object.keys(permission.sub);

                        for (let j = 0; j < keys.length; ++j) {
                            permissionGroups.push(permission.sub[keys[j]]);
                        }
                    }
                }

                let keyword = removeStringAccent(this.permissionKeyword);

                return permissionGroups.filter(
                    (pg) =>
                        pg !== permissionGroup &&
                        removeStringAccent(pg.name).indexOf(keyword) >= 0
                );
            }

            return permissionGroup.sub;
        },
    },
};
</script>
