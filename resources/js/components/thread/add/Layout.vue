<template>
    <tab-layout :count="3" :tab="tab" class="hc-flex-1">
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
                            v-model="thread.name"
                            :style="{
                                color: thread.color,
                                backgroundColor: thread.bgcolor,
                            }"
                            required
                    /></v-field>
                    <v-field :label="$t('color')"
                        ><input type="color" v-model="thread.color"
                    /></v-field>
                    <color-palette v-model="thread.color" />
                    <v-field :label="$t('bgcolor')"
                        ><input type="color" v-model="thread.bgcolor"
                    /></v-field>
                    <color-palette v-model="thread.bgcolor" />
                    <v-field :label="$t('thread.add.send_email_to')"
                        ><input
                            v-model="thread.send_to"
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
                    <div
                        v-if="threadUsers.length"
                        class="hc-item-count"
                        v-text="threadUsers.length"
                    ></div>
                    <icon class="fa fa-caret-right" />
                </item>
                <buttons>
                    <button v-text="$t('add')"></button>
                </buttons>
                <loading :loading="addingThread" />
            </form>
        </template>

        <template #2>
            <div class="hc-flex-column" style="height: 100%">
                <search v-model="userKeyword" />
                <item-list class="hc-flex-1" padding="5px">
                    <item @click="(thread.user_id = null), (tab = 0)">
                        <icon class="fa fa-times" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('none')"
                        ></div>
                    </item>
                    <to-user-row
                        v-for="user in filteredUsers"
                        :key="user.id"
                        :user="user"
                        @click="(thread.user_id = user.id), (tab = 0)"
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
                    <user-row
                        v-for="user in filteredUsers"
                        :key="user.id"
                        :user="user"
                        v-model="threadUsers"
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
import { ADD_THREAD } from "@/actions/project/thread";
import { CLOSE_MODAL } from "@/actions/modal";
import { BULK_ADD_USER_THREAD } from "@/actions/project/user/thread";

import UserRow from "./UserRow.vue";
import ToUserRow from "./ToUserRow.vue";

export default {
    components: {
        UserRow,
        ToUserRow,
    },

    data() {
        return {
            tab: 0,
            thread: this.newThread(),
            addingThread: false,
            userKeyword: "",
            threadUsers: [],
        };
    },

    methods: {
        /**
         *
         */
        newThread() {
            return {
                name: "",
                send_to: "",
                user_id: null,
                private: true,
                color: "#000000",
                bgcolor: "#FFFFFF",
            };
        },

        /**
         *
         */
        async storeThread() {
            this.addingThread = true;

            try {
                const thread = await store.dispatch(ADD_THREAD, this.thread);

                if (this.threadUsers.length > 0) {
                    await store.dispatch(BULK_ADD_USER_THREAD, {
                        users: this.threadUsers,
                        threads: [thread.id],
                    });
                }
            } finally {
                this.addingThread = false;
                this.thread = this.newThread();
                this.threadUsers = [];
                store.commit(CLOSE_MODAL);
            }
        },
    },

    computed: {
        ...mapGetters(["users"]),

        /**
         *
         */
        withProspect: {
            get() {
                return this.thread && !this.thread.private;
            },
            set(value) {
                this.thread.private = !value;
            },
        },

        /**
         *
         */
        user() {
            return this.users.find((u) => u.id == this.thread.user_id);
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
