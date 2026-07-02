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
            v-if="permissionKeyword.length == 0"
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
                        :count="checkedPermissionsCount(permission)"
                        :disabled="isPermissionDisabled(permission)"
                        @click="setCurrentPermissionGroup(permission)"
                    />
                </item-list>
            </template>
        </tab-layout>
        <item-list v-else class="hc-flex-1" padding="5px">
            <permission-row
                v-for="permission in filteredPermissions(
                    currentPermissionGroup
                )"
                :key="permission.id"
                :permission="permission"
                :permission-value="currentPermissionValue(permission)"
                :value="
                    isPermissionChecked(permission) ||
                    isPermissionDisabled(permission)
                "
                :count="checkedPermissionsCount(permission)"
                :disabled="isPermissionDisabled(permission)"
                @click="setCurrentPermissionGroup(permission)"
            />
        </item-list>
    </div>
</template>

<script>
import store from "@/store";
import { mapGetters } from "vuex";
import permissionsList from "@/utils/permissions";

// Actions
import {
    FETCH_USER_PERMISSIONS,
    ADD_USER_PERMISSION,
    REMOVE_USER_PERMISSION,
} from "@/actions/project/user/permission";

import { OPEN_SUB_SLIDE } from "@/actions/slide";

// Components
import PermissionRow from "./PermissionRow.vue";

export default {
    components: {
        PermissionRow,
    },

    data() {
        return {
            name: "user-manage-permissions",
            tab: 0,

            permissionKeyword: "",
            permissionGroups: [],

            permissions: permissionsList,
        };
    },

    created() {
        this.permissionGroups = [this.permissions];
        this.computePermissionsList(permissionsList);
    },

    methods: {
        back() {
            if (this.permissionGroups.length > 1) {
                this.permissionGroups.pop();
            }
        },

        computePermissionsList(list, key) {
            list.value = (key ? key + "." : "") + list.key;

            if (list.sub) {
                list.sub.forEach((sub) =>
                    this.computePermissionsList(sub, list.value)
                );
            }
        },

        setCurrentPermissionGroup(permission) {
            if (permission.sub) {
                this.currentPermissionGroup = permission;
            } else if (permission.action) {
                store.commit(OPEN_SUB_SLIDE, permission.action);
            }
        },

        currentPermissionValue(permission) {
            return permission ? permission.value : ""; /*
            return (
                this.permissionGroups.map((p) => p.key).join(".") +
                (permission ? "." + permission.key : "")
            );*/
        },

        /**
         *
         * @param {*} permission
         */
        isPermissionNameChecked(permissionName) {
            return (
                this.userPermissions.findIndex((l) => l == permissionName) >= 0
            );
        },

        /**
         *
         * @param {*} permission
         */
        checkedPermissionsCount(permission) {
            const key = this.currentPermissionValue(permission) + ".";

            return this.userPermissions.filter((p) => p.indexOf(key) >= 0)
                .length;
        },

        /**
         *
         * @param {*} permission
         */
        isPermissionChecked(permission) {
            const key = this.currentPermissionValue(permission);

            return this.isPermissionNameChecked(key);
        },

        /**
         *
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

        change(event) {
            store.dispatch(
                event.target.checked
                    ? ADD_USER_PERMISSION
                    : REMOVE_USER_PERMISSION,
                this.currentPermissionValue()
            );
            this.$emit("change", event);
        },

        getPermissionGroups(permissionGroup) {
            let permissionGroups = [permissionGroup];

            for (let i = 0; i < permissionGroups.length; ++i) {
                let permission = permissionGroups[i];

                for (let j = 0; j < permission.sub.length; ++j) {
                    if (permission.sub[j].sub) {
                        permissionGroups.push(permission.sub[j]);
                    }
                }
            }

            return permissionGroups;
        },

        /**
         * Filtered permissions
         */
        filteredPermissions(permissionGroup) {
            if (this.permissionKeyword) {
                let permissionGroups =
                    this.getPermissionGroups(permissionGroup);

                let permissions = permissionGroups
                    .map((permissionGroup) =>
                        permissionGroup.sub.filter((s) => !s.sub)
                    )
                    .reduce((carry, value) => [...carry, ...value], []);

                let keyword = removeStringAccent(this.permissionKeyword);

                return permissions.filter(
                    (permission) =>
                        removeStringAccent(permission.name).indexOf(keyword) >=
                        0
                );
            }

            return permissionGroup.permissions;
        },

        /**
         * Filtered permissions
         */
        filteredPermissionGroups(permissionGroup) {
            if (this.permissionKeyword) {
                let permissionGroups =
                    this.getPermissionGroups(permissionGroup);
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

    computed: {
        ...mapGetters(["user", "userPermissions", "userFullName"]),

        currentPermissionGroup: {
            get() {
                return this.permissionGroups.length > 0
                    ? this.permissionGroups[this.permissionGroups.length - 1]
                    : null;
            },
            set(value) {
                this.permissionGroups.push(value);
                this.permissionKeyword = "";
            },
        },

        currentPermission() {
            const { key, name, icon } = this.currentPermissionGroup;
            return { key, name, icon };
        },

        currentAllPermissionValue() {
            return this.permissionGroups.map((p) => p.key).join(".");
        },
    },
};
</script>
