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
                    />
                </item-list>
                <buttons>
                    <button
                        @click.prevent="detach"
                        :disabled="disabled"
                        class="hc-button-danger"
                        v-text="$t('prospect.table.bulk.remove')"
                    ></button>
                    <button
                        @click.prevent="attach"
                        :disabled="disabled"
                        v-text="$t('prospect.table.bulk.add')"
                    ></button>
                </buttons>
                <loading :loading="bulking" />
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
import {
    BULK_ADD_PROSPECT_USER,
    BULK_REMOVE_PROSPECT_USER,
} from "@/actions/project/prospect/user";
import {
    FETCH_PROSPECTS,
    UPDATE_SELECTED_PROSPECTS,
    UPDATE_PROSPECT_BULK_USERS,
} from "@/actions/project/prospect";
import { FETCH_PROSPECT_USERS } from "@/actions/project/prospect/user";
import { FETCH_USERS } from "@/actions/project/user";
import { CLOSE_SLIDE } from "@/actions/slide";

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
            userTab: 0,
            bulking: false,
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

        /**
         *
         */
        async attach() {
            const params = { ...this.prospectsParams };
            const action = async () => {
                this.bulking = true;
                store.commit(UPDATE_SELECTED_PROSPECTS, []);

                try {
                    await store.dispatch(BULK_ADD_PROSPECT_USER, {
                        filters: params,
                        users: this.prospectBulkUsers,
                    });
                    store.dispatch(FETCH_PROSPECTS);
                } finally {
                    this.bulking = false;
                    store.commit(UPDATE_PROSPECT_BULK_USERS, []);
                    store.commit(CLOSE_SLIDE, "prospect-bulk-manage-users");
                }
            };

            if (this.prospectsSelected.length > 0) {
                params.ids = this.prospectsSelected;
                action();
            } else {
                hcConfirm(
                    "Vous n'avez sélectionné aucun prospect.<br>Tous les prospects correspondant à vos filtres seront affectés aux utilisateurs sélectionés.<br>Etes-vous sûr d'effectuer cette action ?",
                    action
                );
            }
        },

        /**
         *
         */
        async detach() {
            const params = { ...this.prospectsParams };
            const action = async () => {
                this.bulking = true;
                await store.dispatch(BULK_REMOVE_PROSPECT_USER, {
                    filters: params,
                    users: this.prospectBulkUsers,
                });
                this.bulking = false;
                store.commit(UPDATE_SELECTED_PROSPECTS, []);
                store.dispatch(FETCH_PROSPECTS);
                store.commit(UPDATE_PROSPECT_BULK_USERS, []);
                store.commit(CLOSE_SLIDE, "prospect-bulk-manage-users");
            };

            if (this.prospectsSelected.length > 0) {
                params.ids = this.prospectsSelected;
                action();
            } else {
                hcConfirm(
                    "Vous n'avez sélectionné aucun prospect.<br>Tous les prospects correspondant à vos filtres seront retirés aux utilisateurs sélectionés.<br>Etes-vous sûr d'effectuer cette action ?",
                    action
                );
            }
        },

        /**
         *
         * @param {*} role
         */
        async selectRole(role) {
            store.commit(
                UPDATE_PROSPECT_BULK_USERS,
                this.users
                    .filter((user) => user.roles.find((r) => r.id == role.id))
                    .map((user) => user.id)
            );

            this.userTab = 0;
        },
    },

    watch: {
        prospect(newValue) {
            store.dispatch(FETCH_PROSPECT_USERS, newValue);
        },
    },

    computed: {
        ...mapGetters([
            "users",
            "roles",
            "prospectsSelected",
            "prospectBulkUsers",
            "prospectsParams",
        ]),

        /**
         *
         */
        disabled() {
            return this.prospectBulkUsers.length == 0;
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

        /**
         *
         */
        all: {
            get: function () {
                for (let i in this.filteredUsers) {
                    if (
                        !this.prospectBulkUsers.find(
                            (user) => user == this.filteredUsers[i].id
                        )
                    ) {
                        return false;
                    }
                }

                return true;
            },
            set: async function (value) {
                if (value) {
                    store.commit(
                        UPDATE_PROSPECT_BULK_USERS,
                        this.filteredUsers.map((user) => user.id)
                    );
                } else {
                    store.commit(UPDATE_PROSPECT_BULK_USERS, []);
                }
            },
        },
    },
};
</script>
