<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <form
                class="hc-flex-column"
                style="height: 100%"
                v-if="groupToUpdate"
                @submit.prevent="update"
            >
                <item-list gap="5px" class="hc-flex-1" padding="10px 0">
                    <v-field :label="$t('name')" required v-slot="{ label }"
                        ><input
                            ref="groupName"
                            :placeholder="label + ' ...'"
                            :style="{
                                color: groupToUpdate.color,
                                backgroundColor: groupToUpdate.bgcolor,
                            }"
                            v-model="groupToUpdate.name"
                            required
                    /></v-field>
                    <v-field :label="$t('color')"
                        ><input type="color" v-model="groupToUpdate.color"
                    /></v-field>
                    <v-field :label="$t('bgcolor')"
                        ><input type="color" v-model="groupToUpdate.bgcolor"
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
                        v-if="can('all.project.group.delete')"
                        @click.prevent="remove"
                        class="hc-button-danger"
                        v-text="$t('delete')"
                    ></button>
                    <button v-text="$t('update')"></button>
                </buttons>
                <loading :loading="updatingGroup || removingGroup" />
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
                    <!-- All -->
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
                        :group="group"
                        v-model="groupUsers"
                    />
                </item-list>
                <loading :loading="fetchingGroupUsers" />
            </div>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import UserGroupService from "@/apis/project/user/group";

// Actions
import {
    SHOW_GROUP,
    UPDATE_GROUP,
    REMOVE_GROUP,
} from "@/actions/project/group";
import { CLOSE_MODAL } from "@/actions/modal";
import { FETCH_PROSPECTS } from "@/actions/project/prospect";
import {
    BULK_REMOVE_USER_GROUP,
    BULK_ADD_USER_GROUP,
} from "@/actions/project/user/group";

import UserRow from "./UserRow.vue";

export default {
    components: {
        UserRow,
    },

    data() {
        return {
            updatingGroup: false,
            removingGroup: false,
            fetchingGroup: false,
            fetchingGroupUsers: false,
            groupToUpdate: this.group,
            userKeyword: "",
            groupUsers: [],
            tab: 0,
        };
    },

    created() {
        this.groupToUpdate = this.group;
    },

    methods: {
        /**
         *
         */
        async update() {
            this.updatingGroup = true;

            try {
                await store.dispatch(UPDATE_GROUP, this.groupToUpdate);
                store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.updatingGroup = false;
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingGroup = true;

                try {
                    await store.dispatch(REMOVE_GROUP, this.groupToUpdate.id);
                    store.dispatch(FETCH_PROSPECTS);
                } finally {
                    this.removingGroup = false;
                    store.commit(CLOSE_MODAL);
                }
            });
        },

        /**
         *
         */
        async fetchGroupUsers() {
            this.fetchingGroupUsers = true;

            try {
                const { data } = await UserGroupService.users(
                    this.project.slug,
                    this.group.id
                );
                this.groupUsers = data.map((user) => user.id);
            } finally {
                this.fetchingGroupUsers = false;
            }
        },
    },

    watch: {
        async group(newValue) {
            if (newValue) {
                this.groupToUpdate = newValue;

                this.fetchingGroup = true;

                try {
                    this.groupToUpdate = await store.dispatch(
                        SHOW_GROUP,
                        newValue.id
                    );
                } finally {
                    this.fetchingGroup = false;
                }
            }
        },

        tab(newValue) {
            if (newValue == 1) {
                this.fetchGroupUsers();
            }
        },
    },

    computed: {
        ...mapGetters(["project", "group", "users", "can"]),

        /**
         *
         */
        all: {
            get: function () {
                return (
                    // this.can("all") ||
                    this.groupUsers.length == this.users.length
                );
            },
            set: async function (value) {
                if (value) {
                    const users = this.users.map((f) => f.id);

                    store.dispatch(BULK_ADD_USER_GROUP, {
                        users,
                        groups: [this.group.id],
                    });
                    this.groupUsers = users;
                } else {
                    store.dispatch(BULK_REMOVE_USER_GROUP, {
                        users: this.users.map((f) => f.id),
                        groups: [this.group.id],
                    });
                    this.groupUsers = [];
                }
            },
        },

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
