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
                    <item tag="label">
                        <icon class="fa fa-check-square" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('all')"
                        ></div>
                        <checkbox v-model="all" />
                    </item>
                    <user-row
                        v-for="user in filteredUsers"
                        :key="user.id"
                        :user="user"
                        :value="isUserChecked(user)"
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
    FETCH_PROSPECT_USERS,
    BULK_ADD_PROSPECT_USER,
    BULK_REMOVE_PROSPECT_USER,
    ADD_PROSPECT_USER,
    REMOVE_PROSPECT_USER,
} from "@/actions/project/prospect/user";
// import { FETCH_USERS } from "@/actions/project/user";
import { FETCH_PROSPECTS } from "@/actions/project/prospect";

// Components
import UserRow from "./UserRow.vue";
import RoleRow from "./RoleRow.vue";

export default {
    components: {
        UserRow,
        RoleRow,
    },

    data() {
        return {
            name: "prospect-manage-users",
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
        isUserChecked(user) {
            return this.prospectUsers.findIndex((l) => l.id == user.id) >= 0;
        },

        /**
         *
         * @param {*} user
         */
        fetchUsers() {
            // store.dispatch(FETCH_USERS);
        },

        /**
         *
         * @param {*} role
         */
        async selectRole(role) {
            this.userTab = 0;

            try {
                await store.dispatch(BULK_ADD_PROSPECT_USER, {
                    filters: {
                        ids: [this.prospect.id],
                    },
                    users: this.users
                        .filter((user) =>
                            user.roles.find((r) => r.id == role.id)
                        )
                        .map((user) => user.id),
                });
                store.dispatch(FETCH_PROSPECT_USERS);
                store.dispatch(FETCH_PROSPECTS);
            } finally {
            }
        },

        /**
         * Store new user
         */
        async addUser() {
            store.commit(OPEN_MODAL, "user-add");
        },
    },

    watch: {
        prospect(newValue) {
            if (this.slideOpen(this.name)) {
                store.dispatch(FETCH_PROSPECT_USERS);
            }
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "users",
            "roles",
            "prospect",
            "prospectUsers",
            "slideOpen",
            "can",
        ]),

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

        /**
         *
         */
        all: {
            get: function () {
                for (let i in this.filteredUsers) {
                    if (
                        !this.prospectUsers.find(
                            (user) => user.id == this.filteredUsers[i].id
                        )
                    ) {
                        return false;
                    }
                }

                return true;
            },
            set: async function (value) {
                if (value) {
                    this.filteredUsers.forEach((f) => {
                        store.commit(ADD_PROSPECT_USER, f);
                    });
                    store.dispatch(BULK_ADD_PROSPECT_USER, {
                        prospects: [this.prospect.id],
                        users: this.filteredUsers.map((f) => f.id),
                    });
                } else {
                    this.filteredUsers.forEach((f) => {
                        store.commit(REMOVE_PROSPECT_USER, f);
                    });
                    store.dispatch(BULK_REMOVE_PROSPECT_USER, {
                        prospects: [this.prospect.id],
                        users: this.filteredUsers.map((f) => f.id),
                    });
                }
            },
        },
    },
};
</script>
