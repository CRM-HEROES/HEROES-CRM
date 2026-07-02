<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <form
                class="hc-flex-column"
                style="height: 100%"
                @submit.prevent="storeRole"
            >
                <item-list gap="5px">
                    <v-field :label="$t('name')" required v-slot="{ label }"
                        ><input
                            :placeholder="label + ' ...'"
                            v-model="role.name"
                            required
                    /></v-field>
                </item-list>
                <item @click="tab = 1">
                    <icon class="fa fa-users" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('label.affected_users')"
                    ></div>
                    <div
                        v-if="roleUsers.length"
                        class="hc-item-count"
                        v-text="roleUsers.length"
                    ></div>
                    <icon class="fa fa-caret-right" />
                </item>
                <buttons>
                    <button v-text="$t('add')"></button>
                </buttons>
                <loading :loading="addingRole" />
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
                        v-model="roleUsers"
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
import { ADD_ROLE, SET_ROLE } from "@/actions/project/role";
import { CLOSE_MODAL } from "@/actions/modal";
import { OPEN_SLIDE } from "@/actions/slide";
import { BULK_ADD_USER_ROLE } from "@/actions/project/user/role";

import UserRow from "./UserRow.vue";

export default {
    components: {
        UserRow,
    },

    data() {
        return {
            role: this.newRole(),
            addingRole: false,
            userKeyword: "",
            roleUsers: [],
            tab: 0,
        };
    },

    methods: {
        /**
         *
         */
        newRole() {
            return {
                name: "",
            };
        },

        /**
         *
         */
        async storeRole() {
            this.addingRole = true;

            try {
                const role = await store.dispatch(ADD_ROLE, this.role);

                store.commit(OPEN_SLIDE, "manage-roles");
                store.commit(SET_ROLE, role);

                if (this.roleUsers.length > 0) {
                    await store.dispatch(BULK_ADD_USER_ROLE, {
                        users: this.roleUsers,
                        roles: [role.id],
                    });
                }
            } finally {
                this.addingRole = false;
                this.role = this.newRole();
                this.roleUsers = [];
                store.commit(CLOSE_MODAL);
            }
        },
    },

    computed: {
        ...mapGetters(["users"]),

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
