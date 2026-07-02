<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <form
                class="hc-flex-column"
                style="height: 100%"
                @submit.prevent="storeFolder"
            >
                <item-list gap="5px">
                    <v-field :label="$t('name')" required v-slot="{ label }"
                        ><input
                            :placeholder="label + ' ...'"
                            v-model="folder.name"
                            :style="{
                                color: folder.color,
                                backgroundColor: folder.bgcolor,
                            }"
                            required
                    /></v-field>

                    <v-field :label="$t('color')"
                        ><input type="color" v-model="folder.color"
                    /></v-field>
                    <color-palette v-model="folder.color" />

                    <v-field :label="$t('bgcolor')"
                        ><input type="color" v-model="folder.bgcolor"
                    /></v-field>
                    <color-palette v-model="folder.bgcolor" />

                    <v-field :label="$t('for')" required>
                        <select v-model="folder.for">
                            <option
                                value="prospect"
                                v-text="$t('prospects')"
                            ></option>
                            <option value="user" v-text="$t('users')"></option>
                        </select>
                    </v-field>
                </item-list>

                <item @click="tab = 1">
                    <icon class="fa fa-users" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('label.affected_users')"
                    ></div>
                    <div
                        v-if="folderUsers.length"
                        class="hc-item-count"
                        v-text="folderUsers.length"
                    ></div>
                    <icon class="fa fa-caret-right" />
                </item>
                <buttons>
                    <button v-text="$t('add')"></button>
                </buttons>
                <loading :loading="addingFolder" />
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
                        v-model="folderUsers"
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
import { ADD_FOLDER } from "@/actions/project/folder";
import { CLOSE_MODAL } from "@/actions/modal";
import { BULK_ADD_USER_FOLDER } from "@/actions/project/user/folder";

import UserRow from "./UserRow.vue";

export default {
    components: {
        UserRow,
    },

    data() {
        return {
            folder: this.newFolder(),
            addingFolder: false,
            userKeyword: "",
            folderUsers: [],
            tab: 0,
        };
    },

    methods: {
        /**
         *
         */
        newFolder() {
            return {
                name: "",
                color: "#000000",
                bgcolor: "#FFFFFF",
                for: "prospect",
            };
        },

        /**
         *
         */
        async storeFolder() {
            this.addingFolder = true;

            try {
                const folder = await store.dispatch(ADD_FOLDER, this.folder);

                if (this.folderUsers.length > 0) {
                    await store.dispatch(BULK_ADD_USER_FOLDER, {
                        users: this.folderUsers,
                        folders: [folder.id],
                    });
                }
            } finally {
                this.addingFolder = false;
                this.folder = this.newFolder();
                this.folderUsers = [];
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
