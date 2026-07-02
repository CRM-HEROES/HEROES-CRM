<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <form
                class="hc-flex-column"
                style="height: 100%"
                v-if="folderToUpdate"
                @submit.prevent="update"
            >
                <item-list gap="5px" class="hc-flex-1" padding="10px 0">
                    <v-field :label="$t('name')" required v-slot="p"
                        ><input
                            ref="folderName"
                            :placeholder="p.folder + ' ...'"
                            v-model="folderToUpdate.name"
                            :style="{
                                color: folderToUpdate.color,
                                backgroundColor: folderToUpdate.bgcolor,
                            }"
                            required
                    /></v-field>
                    <v-field :label="$t('color')"
                        ><input type="color" v-model="folderToUpdate.color"
                    /></v-field>
                    <color-palette v-model="folderToUpdate.color" />
                    <v-field :label="$t('bgcolor')"
                        ><input type="color" v-model="folderToUpdate.bgcolor"
                    /></v-field>
                    <color-palette v-model="folderToUpdate.bgcolor" />
                </item-list>
                <item @click="tab = 1" v-if="folder.for == 'prospect'">
                    <icon class="fa fa-users" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('label.affected_users')"
                    ></div>
                    <icon class="fa fa-caret-right" />
                </item>
                <buttons>
                    <button
                        v-if="can('all.project.folder.delete')"
                        @click.prevent="remove"
                        class="hc-button-danger"
                        v-text="$t('delete')"
                    ></button>
                    <button v-text="$t('update')"></button>
                </buttons>
                <loading :loading="updatingFolder || removingFolder" />
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
                        :folder="folder"
                        v-model="folderUsers"
                    />
                </item-list>
                <loading :loading="fetchingFolderUsers" />
            </div>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import UserFolderService from "@/apis/project/user/folder";

// Actions
import { UPDATE_FOLDER, REMOVE_FOLDER } from "@/actions/project/folder";
import { CLOSE_MODAL } from "@/actions/modal";
import { FETCH_PROSPECTS } from "@/actions/project/prospect";
import {
    BULK_REMOVE_USER_FOLDER,
    BULK_ADD_USER_FOLDER,
} from "@/actions/project/user/folder";

import UserRow from "./UserRow.vue";

export default {
    components: {
        UserRow,
    },

    data() {
        return {
            updatingFolder: false,
            removingFolder: false,
            fetchingFolder: false,
            fetchingFolderUsers: false,
            folderToUpdate: this.folder,
            userKeyword: "",
            folderUsers: [],
            tab: 0,
        };
    },

    created() {
        this.folderToUpdate = this.folder;
    },

    methods: {
        /**
         *
         */
        async update() {
            this.updatingFolder = true;

            try {
                await store.dispatch(UPDATE_FOLDER, this.folderToUpdate);
                store.dispatch(FETCH_PROSPECTS);
            } finally {
                this.updatingFolder = false;
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingFolder = true;

                try {
                    await store.dispatch(REMOVE_FOLDER, this.folderToUpdate.id);
                    store.dispatch(FETCH_PROSPECTS);
                } finally {
                    this.removingFolder = false;
                    store.commit(CLOSE_MODAL);
                }
            });
        },

        /**
         *
         */
        async fetchFolderUsers() {
            this.fetchingFolderUsers = true;

            try {
                const { data } = await UserFolderService.users(
                    this.project.slug,
                    this.folder.id
                );
                this.folderUsers = data.map((user) => user.id);
            } finally {
                this.fetchingFolderUsers = false;
            }
        },
    },

    watch: {
        async folder(newValue) {
            if (newValue) {
                this.folderToUpdate = { ...newValue };

                /*this.fetchingFolder = true;
                this.folderToUpdate = await store.dispatch(
                    SHOW_FOLDER,
                    newValue.id
                );
                this.fetchingFolder = false;*/
            }
        },

        tab(newValue) {
            if (newValue == 1) {
                this.fetchFolderUsers();
            }
        },
    },

    computed: {
        ...mapGetters(["project", "folder", "users", "can"]),

        /**
         *
         */
        all: {
            get: function () {
                return (
                    // this.can("all") ||
                    this.folderUsers.length == this.users.length
                );
            },
            set: async function (value) {
                if (value) {
                    const users = this.users.map((f) => f.id);

                    store.dispatch(BULK_ADD_USER_FOLDER, {
                        users,
                        folders: [this.folder.id],
                    });
                    this.folderUsers = users;
                } else {
                    store.dispatch(BULK_REMOVE_USER_FOLDER, {
                        users: this.users.map((f) => f.id),
                        folders: [this.folder.id],
                    });
                    this.folderUsers = [];
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
