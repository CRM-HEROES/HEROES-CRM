<template>
    <tab-layout :count="3" :tab="tab" class="hc-flex-1" v-if="threadToUpdate">
        <template #1>
            <form
                class="hc-flex-column"
                style="height: 100%"
                @submit.prevent="storeThread"
            >
                <item-list gap="5px">
                    <v-field :label="$t('name')" required v-slot="{ label }"
                        ><input
                            :placeholder="label + ' ...'"
                            v-model="threadToUpdate.name"
                            :style="{
                                color: threadToUpdate.color,
                                backgroundColor: threadToUpdate.bgcolor,
                            }"
                            required
                    /></v-field>
                    <v-field :label="$t('color')"
                        ><input type="color" v-model="threadToUpdate.color"
                    /></v-field>
                    <color-palette v-model="threadToUpdate.color" />
                    <v-field :label="$t('bgcolor')"
                        ><input type="color" v-model="threadToUpdate.bgcolor"
                    /></v-field>
                    <color-palette v-model="threadToUpdate.bgcolor" />
                    <v-field :label="$t('thread.add.send_email_to')"
                        ><input
                            v-model="threadToUpdate.send_to"
                            placeholder="example@test.com ..."
                    /></v-field>
                    <item tag="label" style="padding: 0 5px">
                        <icon class="fa fa-users" :style="style" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('thread.add.send_email_to_prospect')"
                        ></div>
                        <checkbox v-model="withProspect" />
                    </item>
                    <item @click="tab = 1" style="padding: 0 5px">
                        <icon class="fa fa-user" :style="style" />
                        <div
                            class="hc-item-main-content"
                            v-text="
                                $t('thread.add.associate_to_the_user', {
                                    user: user
                                        ? user.name
                                        : 'l\'utilisateur ...',
                                })
                            "
                        ></div>
                        <icon class="fa fa-caret-right" />
                    </item>
                </item-list>
                <item @click="tab = 2">
                    <icon class="fa fa-users" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('label.affected_users')"
                    ></div>
                    <icon class="fa fa-caret-right" />
                </item>
                <buttons>
                    <button
                        v-if="can('all.project.thread.delete')"
                        @click.prevent="remove"
                        class="hc-button-danger"
                        v-text="$t('delete')"
                    ></button>
                    <button
                        @click.prevent="update"
                        v-text="$t('update')"
                    ></button>
                </buttons>
                <loading :loading="addingThread" />
            </form>
        </template>

        <template #2>
            <div class="hc-flex-column" style="height: 100%">
                <search v-model="emailUserKeyword" />
                <item-list class="hc-flex-1" padding="5px">
                    <item @click="(threadToUpdate.user_id = null), (tab = 0)">
                        <icon class="fa fa-times" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('none')"
                        ></div>
                    </item>
                    <email-user-row
                        v-for="user in filteredEmailUsers"
                        :key="user.id"
                        :user="user"
                        @click="(threadToUpdate.user_id = user.id), (tab = 0)"
                    />
                </item-list>
            </div>
        </template>

        <template #3>
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
                        v-for="user in filteredEmailUsers"
                        :key="user.id"
                        :user="user"
                        :thread="thread"
                        v-model="threadUsers"
                    />
                </item-list>
                <loading :loading="fetchingThreadUsers" />
            </div>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import UserThreadService from "@/apis/project/user/thread";

// Actions
import { UPDATE_THREAD, REMOVE_THREAD } from "@/actions/project/thread";
import { CLOSE_MODAL } from "@/actions/modal";
import {
    BULK_REMOVE_USER_THREAD,
    BULK_ADD_USER_THREAD,
} from "@/actions/project/user/thread";

import EmailUserRow from "./EmailUserRow.vue";
import UserRow from "./UserRow.vue";

export default {
    components: {
        EmailUserRow,
        UserRow,
    },

    data() {
        return {
            updatingThread: false,
            removingThread: false,
            fetchingThread: false,
            fetchingThreadUsers: false,
            threadToUpdate: this.thread,
            emailUserKeyword: "",
            userKeyword: "",
            threadUsers: [],
            tab: 0,
        };
    },

    created() {
        this.threadToUpdate = this.thread;
    },

    methods: {
        /**
         *
         */
        async update() {
            store.commit(CLOSE_MODAL);
            this.updatingThread = true;

            try {
                await store.dispatch(UPDATE_THREAD, this.threadToUpdate);
                // store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.updatingThread = false;
            }
        },

        /**
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingThread = true;

                try {
                    await store.dispatch(REMOVE_THREAD, this.threadToUpdate.id);
                    // store.dispatch(FETCH_PROSPECTS);
                } finally {
                    this.removingThread = false;
                    store.commit(CLOSE_MODAL);
                }
            });
        },

        /**
         *
         */
        async fetchThreadUsers() {
            this.fetchingThreadUsers = true;

            try {
                const { data } = await UserThreadService.users(
                    this.project.slug,
                    this.thread.id
                );
                this.threadUsers = data.map((user) => user.id);
            } finally {
                this.fetchingThreadUsers = false;
            }
        },
    },

    watch: {
        async thread(newValue) {
            if (newValue) {
                this.threadToUpdate = { ...newValue };

                /*this.fetchingThread = true;
                this.threadToUpdate = await store.dispatch(
                    SHOW_THREAD,
                    newValue.id
                );
                this.fetchingThread = false;*/
            }
        },

        tab(newValue) {
            if (newValue == 2) {
                this.fetchThreadUsers();
            }
        },
    },

    computed: {
        ...mapGetters(["project", "thread", "users", "can"]),

        /**
         *
         */
        all: {
            get: function () {
                return (
                    // this.can("all") ||
                    this.threadUsers.length == this.users.length
                );
            },
            set: async function (value) {
                if (value) {
                    const users = this.users.map((f) => f.id);

                    store.dispatch(BULK_ADD_USER_THREAD, {
                        users,
                        threads: [this.thread.id],
                    });
                    this.threadUsers = users;
                } else {
                    store.dispatch(BULK_REMOVE_USER_THREAD, {
                        users: this.users.map((f) => f.id),
                        threads: [this.thread.id],
                    });
                    this.threadUsers = [];
                }
            },
        },

        /**
         *
         */
        withProspect: {
            get() {
                return this.threadToUpdate && !this.threadToUpdate.private;
            },
            set(value) {
                this.threadToUpdate.private = !value;
            },
        },

        /**
         *
         */
        user() {
            return this.users.find((u) => u.id == this.threadToUpdate.user_id);
        },

        /**
         *
         */
        filteredEmailUsers() {
            const keyword = removeStringAccent(this.emailUserKeyword);

            return this.users.filter(
                (user) => removeStringAccent(user.name).indexOf(keyword) >= 0
            );
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
