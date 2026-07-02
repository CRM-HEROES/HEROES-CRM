<template>
    <tab-layout :count="1" :tab="tab" class="hc-flex-1">
        <template #1>
            <div class="hc-flex-column" style="height: 100%">
                <item>
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
                        :file="file"
                        v-model="fileUsers"
                    />
                </item-list>
                <loading :loading="fetchingFileUsers" />
            </div>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import ProspectFileService from "@/apis/project/prospect/file.js";

import UserRow from "./UserRow.vue";
import { BULK_ADD_USER_FILE, BULK_REMOVE_USER_FILE } from "@/actions/project/prospect/file";

export default {
    components: {
        UserRow,
    },
    data() {
        return {
            fetchingFileUsers: false,
            fileToUpdate: this.file,
            userKeyword: "",
            fileUsers: [],
            tab: 0,
        };
    },
    created() {
        this.fileToUpdate = this.file;
    },
    mounted() {
        this.fetchFileUsers();
    },
    methods: {
        async fetchFileUsers() {
            this.fetchingFileUsers = true;

            try {
                const { data } = await ProspectFileService.users(
                    this.project.slug,
                    this.prospect.id,
                    this.folder.id,
                    this.file.id
                );
                this.fileUsers = data.map((user) => user.id);
            } finally {
                this.fetchingFileUsers = false;
            }
        },
    },
    watch: {
        async file() {
           this.fetchFileUsers()
        }
    },
        computed: {
        ...mapGetters(["project", "prospect", "folder", "file", "users"]),

        /**
         *
         */
        all: {
            get: function () {
                return (
                    this.fileUsers.length === this.users.length
                );
            },
            set: async function (value) {
                if (value) {
                    const users = this.users.map((f) => f.id);

                    store.dispatch(BULK_ADD_USER_FILE, {
                        users,
                        files: [this.file.id],
                    });
                    this.fileUsers = users;
                } else {
                    store.dispatch(BULK_REMOVE_USER_FILE, {
                        users: this.users.map((f) => f.id),
                        files: [this.file.id],
                    });
                    this.fileUsers = [];
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
