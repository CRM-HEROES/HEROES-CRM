<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <form
                class="hc-flex-column"
                style="height: 100%"
                @submit.prevent="storeGroup"
            >
                <item-list gap="5px">
                    <v-field :label="$t('name')" required v-slot="{ label }"
                        ><input
                            :placeholder="label + ' ...'"
                            v-model="group.name"
                            :style="{
                                color: group.color,
                                backgroundColor: group.bgcolor,
                            }"
                            required
                    /></v-field>
                    <v-field :label="$t('color')"
                        ><input type="color" v-model="group.color"
                    /></v-field>
                    <color-palette v-model="group.color" />
                    <v-field :label="$t('bgcolor')"
                        ><input type="color" v-model="group.bgcolor"
                    /></v-field>
                    <color-palette v-model="group.bgcolor" />
                </item-list>
                <item @click="tab = 1">
                    <icon class="fa fa-users" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('label.affected_users')"
                    ></div>
                    <div
                        v-if="groupUsers.length"
                        class="hc-item-count"
                        v-text="groupUsers.length"
                    ></div>
                    <icon class="fa fa-caret-right" />
                </item>
                <buttons>
                    <button v-text="$t('add')"></button>
                </buttons>
                <loading :loading="addingGroup" />
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
                        v-model="groupUsers"
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
import { ADD_GROUP } from "@/actions/project/group";
import { CLOSE_MODAL } from "@/actions/modal";
import { BULK_ADD_USER_GROUP } from "@/actions/project/user/group";

import UserRow from "./UserRow.vue";

export default {
    components: {
        UserRow,
    },

    data() {
        return {
            group: this.newGroup(),
            addingGroup: false,
            userKeyword: "",
            groupUsers: [],
            tab: 0,
        };
    },

    methods: {
        /**
         *
         */
        newGroup() {
            return {
                name: "",
                color: "#000000",
                bgcolor: "#FFFFFF",
            };
        },

        /**
         *
         */
        async storeGroup() {
            this.addingGroup = true;

            try {
                const group = await store.dispatch(ADD_GROUP, this.group);

                if (this.groupUsers.length > 0) {
                    await store.dispatch(BULK_ADD_USER_GROUP, {
                        users: this.groupUsers,
                        groups: [group.id],
                    });
                }
            } finally {
                this.addingGroup = false;
                this.group = this.newGroup();
                this.groupUsers = [];
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
