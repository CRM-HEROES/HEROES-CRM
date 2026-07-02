<template>
    <tab-layout :count="2" :tab="userTab" style="width: 100%">
        <!-- Users -->
        <template #1>
            <div class="hc-flex-column" style="height: 100%">
                <search v-model="userKeyword" />
                <!-- Choose a role -->
                <item
                    class="hc-item.bordered"
                    tag="a"
                    @click.prevent="userTab = 1"
                >
                    <icon class="fa fa-user-tie" />
                    <div
                        class="hc-item-main-content"
                        v-text="'Choisir un rôle ...'"
                    ></div>
                    <icon class="fa fa-caret-right" />
                </item>
                <item-list class="hc-flex-1" padding="5px">
                    <item tag="label" class="hc-prospects-table-filter-user">
                        <icon class="fa fa-filter" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('prospect.table.filters.users.with')"
                        ></div>
                        <icon
                            tag="a"
                            class="fa fa-thumbs-down"
                            :style="excludeUserStyle"
                            :title="$t('prospect.table.filters.users.without')"
                            @click.prevent="toggleExcludeUser"
                        />
                        <checkbox
                            :model-value="isCheckedUser"
                            @change="changeUser"
                        />
                    </item>
                    <user-row
                        v-for="user in filteredUsers"
                        :key="user.id"
                        :user="user"
                    />
                </item-list>
                <buttons v-if="can('all')">
                    <a @click.prevent="addUser" v-text="$t('add')"></a>
                </buttons>
            </div>
        </template>

        <!-- Roles -->
        <template #2>
            <div class="hc-flex-column" style="height: 100%">
                <!-- Back -->
                <item
                    class="hc-item.bordered"
                    tag="a"
                    @click.prevent="userTab = 0"
                >
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content"
                        v-text="'Choisir un rôle'"
                    ></div>
                </item>
                <!-- Search role -->
                <search v-model="roleKeyword" />
                <!-- Roles list -->
                <item-list class="hc-flex-1" padding="12px">
                    <role-row
                        v-for="role in filteredRoles"
                        :key="role.id"
                        :role="role"
                        @select="selectRole"
                    />
                </item-list>
            </div>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import {
    REMOVE_PROSPECT_PARAMS,
    ADD_PROSPECT_PARAMS,
    FETCH_PROSPECTS,
} from "@/actions/project/prospect";
// import { FETCH_USERS } from "@/actions/project/user";

import UserRow from "./UserRow.vue";
import RoleRow from "./RoleRow.vue";

export default {
    components: {
        UserRow,
        RoleRow,
    },

    data() {
        return {
            tab: 0,
            userTab: 0,
            userKeyword: "",
            roleKeyword: "",
        };
    },

    methods: {
        /**
         *
         * @param {*} user
         */
        fetchUsers() {
            // store.dispatch(FETCH_USERS);
        },

        changeUser(event) {
            // Remove without param
            store.commit(REMOVE_PROSPECT_PARAMS, {
                key: this.withoutKeyUser,
            });

            // Add or remove with param
            store.commit(
                event.target.checked
                    ? ADD_PROSPECT_PARAMS
                    : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKeyUser,
                }
            );
            store.dispatch(FETCH_PROSPECTS);
        },

        toggleExcludeUser() {
            // Add or remove with param
            store.commit(
                this.isExcludedUser
                    ? ADD_PROSPECT_PARAMS
                    : REMOVE_PROSPECT_PARAMS,
                {
                    key: this.withKeyUser,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcludedUser
                    ? REMOVE_PROSPECT_PARAMS
                    : ADD_PROSPECT_PARAMS,
                {
                    key: this.withoutKeyUser,
                }
            );

            store.dispatch(FETCH_PROSPECTS);
        },

        /**
         *
         * @param {*} role
         */
        async selectRole(role) {
            this.users
                .filter((user) => user.roles.find((r) => r.id == role.id))
                .forEach((user) => {
                    store.commit(ADD_PROSPECT_PARAMS, {
                        key: "withUsers",
                        value: user.id,
                        multiple: true,
                    });
                });

            store.dispatch(FETCH_PROSPECTS);
            this.userTab = 0;
        },

        /**
         * Store new user
         */
        async addUser() {
            store.commit(OPEN_MODAL, "user-add");
        },
    },

    computed: {
        ...mapGetters([
            "users",
            "roles",
            "prospect",
            "prospectFullName",
            "prospectsParamValue",
            "can",
        ]),

        /**
         *
         */
        withKeyUser() {
            return "withUsers";
        },

        /**
         *
         */
        withoutKeyUser() {
            return "withoutUsers";
        },

        /**
         *
         */
        isCheckedUser() {
            return (
                this.prospectsParamValue(this.withKeyUser) === "" ||
                this.prospectsParamValue(this.withoutKeyUser) === ""
            );
        },

        /**
         *
         */
        isExcludedUser() {
            return this.prospectsParamValue(this.withoutKeyUser) === "";
        },

        /**
         *
         */
        excludeUserStyle() {
            return {
                color: this.isExcludedUser ? "#CC0000" : "#CCCCCC",
            };
        },

        /**
         *
         */
        filteredUsers() {
            const keyword = removeStringAccent(this.userKeyword);

            return this.users
                .filter(
                    (user) =>
                        removeStringAccent(user.name).indexOf(keyword) >= 0
                )
                .sort((user1, user2) =>
                    user1.pivot &&
                    user2.pivot &&
                    user1.pivot.relevance_prospect >
                        user2.pivot.relevance_prospect
                        ? -1
                        : 1
                );
        },

        /**
         *
         */
        filteredRoles() {
            const keyword = removeStringAccent(this.roleKeyword);

            return this.roles.filter(
                (role) => removeStringAccent(role.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
