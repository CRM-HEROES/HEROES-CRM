<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <form
                class="hc-flex-column"
                style="height: 100%"
                v-if="roleToUpdate"
                @submit.prevent="update"
            >
                <item-list gap="5px" class="hc-flex-1" padding="10px 0">
                    <v-field :label="$t('name')" required v-slot="{ label }"
                        ><input
                            ref="roleName"
                            :placeholder="label + ' ...'"
                            v-model="roleToUpdate.name"
                            required
                    /></v-field>
                </item-list>
                <item @click="tab = 1">
                    <icon class="fa fa-users" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('label.affected_users')"
                    ></div>
                    <icon class="fa fa-caret-right" />
                </item>
                <buttons>
                    <button
                        v-if="can('all.project.role.delete')"
                        @click.prevent="remove"
                        class="hc-button-danger"
                        v-text="$t('delete')"
                    ></button>
                    <button v-text="$t('update')"></button>
                </buttons>
                <loading :loading="updatingRole || removingRole" />
            </form>
        </template>

        <template #2>
            <div class="hc-flex-column" style="height: 100%">
                <item @click="tab = 0">
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('label.affected_users')"
                    ></div>
                </item>
                <search v-model="userKeyword" />
                <item-list
                    class="hc-flex-1"
                    padding="5px"
                    style="max-height: 400px"
                >
                    <user-row
                        v-for="user in filteredUsers"
                        :key="user.id"
                        :user="user"
                        :role="role"
                        v-model="roleUsers"
                    />
                </item-list>
                <loading :loading="fetchingRoleUsers" />
            </div>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import UserRoleService from "@/apis/project/user/role";

// Actions
import { SHOW_ROLE, UPDATE_ROLE, REMOVE_ROLE } from "@/actions/project/role";
import { CLOSE_MODAL } from "@/actions/modal";
import { FETCH_PROSPECTS } from "@/actions/project/prospect";

import UserRow from "./UserRow.vue";

export default {
    components: {
        UserRow,
    },

    data() {
        return {
            updatingRole: false,
            removingRole: false,
            fetchingRole: false,
            fetchingRoleUsers: false,
            roleToUpdate: this.role,
            userKeyword: "",
            roleUsers: [],
            tab: 0,
        };
    },

    created() {
        this.roleToUpdate = this.role;
    },

    methods: {
        /**
         *
         */
        async update() {
            this.updatingRole = true;

            try {
                await store.dispatch(UPDATE_ROLE, this.roleToUpdate);
                store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.updatingRole = false;
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingRole = true;

                try {
                    await store.dispatch(REMOVE_ROLE, this.roleToUpdate.id);
                    store.dispatch(FETCH_PROSPECTS);
                } finally {
                    this.removingRole = false;
                    store.commit(CLOSE_MODAL);
                }
            });
        },

        /**
         *
         */
        async fetchRoleUsers() {
            this.fetchingRoleUsers = true;

            try {
                const { data } = await UserRoleService.users(
                    this.project.slug,
                    this.role.id
                );
                this.roleUsers = data.map((user) => user.id);
            } finally {
                this.fetchingRoleUsers = false;
            }
        },
    },

    watch: {
        async role(newValue) {
            if (newValue) {
                this.roleToUpdate = newValue;

                this.fetchingRole = true;

                try {
                    this.roleToUpdate = await store.dispatch(
                        SHOW_ROLE,
                        newValue.id
                    );
                } finally {
                    this.fetchingRole = false;
                }
            }
        },

        tab(newValue) {
            if (newValue == 1) {
                this.fetchRoleUsers();
            }
        },
    },

    computed: {
        ...mapGetters(["project", "role", "users", "can"]),

        /**
         *
         */
        filteredUsers() {
            const keyword = removeStringAccent(this.userKeyword);

            return this.users.filter(
                (user) => removeStringAccent(user.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
