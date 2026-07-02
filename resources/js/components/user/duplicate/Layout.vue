<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <div class="hc-flex-column" style="height: 100%">
                <tab
                    :count="2"
                    @tab:change="
                        (t) => {
                            duplicateOriginTab = t;
                            tab = 0;
                        }
                    "
                >
                    <template #1>{{ $t("user.duplicate.from") }}</template>
                    <template #2>{{ $t("user.duplicate.for") }}</template>
                </tab>
                <tab-layout
                    :count="2"
                    :tab="duplicateOriginTab"
                    class="hc-flex-1"
                >
                    <template #1>
                        <div class="hc-flex-column" style="height: 100%">
                            <search v-model="userKeyword" />
                            <item-list class="hc-flex-1" padding="5px">
                                <user-row
                                    v-for="user in filteredUsers"
                                    :key="user.id"
                                    :user="user"
                                    v-model="duplicate.users"
                                />
                            </item-list>
                            <buttons>
                                <button
                                    @click.prevent="tab = 1"
                                    v-text="$t('next')"
                                    :disabled="duplicate.users.length == 0"
                                ></button>
                            </buttons>
                        </div>
                    </template>

                    <template #2>
                        <div class="hc-flex-column" style="height: 100%">
                            <search v-model="userKeyword" />
                            <item-list class="hc-flex-1" padding="5px">
                                <user-from-row
                                    v-for="user in filteredUsers"
                                    :key="user.id"
                                    :user="user"
                                    @click.prevent="
                                        (userFrom = user), (tab = 1)
                                    "
                                />
                            </item-list>
                        </div>
                    </template>
                </tab-layout>
            </div>
        </template>

        <template #2>
            <div class="hc-flex-column" style="height: 100%">
                <item v-if="tab == 1" class="bordered" @click.prevent="tab = 0">
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content"
                        v-text="
                            duplicateOriginTab == 0
                                ? 'Dupliquer les ...'
                                : 'Dupliquer depuis ' + userFrom.name
                        "
                    ></div>
                </item>
                <search v-model="duplicatableKeyword" />
                <item tag="label">
                    <icon class="fa fa-copy" />
                    <div class="hc-item-main-content" v-text="$t('all')"></div>
                    <checkbox v-model="allDuplicatables" />
                </item>
                <item-list class="hc-flex-1" padding="5px">
                    <duplicatable-row
                        v-for="duplicatable in filteredDuplicatables"
                        :key="duplicatable.key"
                        :duplicatable="duplicatable"
                        v-model="duplicate.duplicatables"
                    />
                    <loading :loading="duplicating" />
                </item-list>
                <buttons>
                    <button
                        @click.prevent="duplicateProfile"
                        v-text="'Dupliquer'"
                        :disabled="duplicate.duplicatables.length == 0"
                    ></button>
                </buttons>
            </div>
        </template>
    </tab-layout>
</template>

<script>
import UserDuplicateService from "@/apis/project/user/duplicate";
import store from "@/store";
import { mapGetters } from "vuex";

import { SHOW_USER } from "@/actions/project/user";

import DuplicatableRow from "./DuplicatableRow.vue";
import UserFromRow from "./UserFromRow.vue";
import UserRow from "./UserRow.vue";

export default {
    components: {
        DuplicatableRow,
        UserFromRow,
        UserRow,
    },

    data() {
        return {
            tab: 0,
            duplicateOriginTab: 0,
            userKeyword: "",
            duplicatableKeyword: "",

            userFrom: null,

            duplicate: {
                users: [],
                duplicatables: [],
            },

            duplicating: false,

            /**
             */
            duplicatables: [
                {
                    key: "roles",
                    name: this.$t("user.roles.title"),
                    icon: "fa fa-user-md icon-garnet",
                },
                {
                    key: "permissions",
                    name: this.$t("user.permissions.title"),
                    icon: "fa fa-hand-paper icon-red",
                },
                {
                    key: "categories",
                    name: this.$t("user.categories.title"),
                    icon: "fa fa-tags icon-cyan",
                },
                {
                    key: "users",
                    name: this.$t("user.users.title"),
                    icon: "fa fa-user icon-blue",
                },
                {
                    key: "menus",
                    name: this.$t("user.menus.title"),
                    icon: "fa fa-list icon-red",
                },
                {
                    key: "folders",
                    name: this.$t("user.folders.title"),
                    icon: "fa fa-folder icon-garnet",
                },
                {
                    key: "threads",
                    name: this.$t("user.threads.title"),
                    icon: "fa fa-envelope icon-green",
                },
                {
                    key: "groups",
                    name: this.$t("user.groups.title"),
                    icon: "fa fa-users icon-blue",
                },
                {
                    key: "documents",
                    name: this.$t("user.documents.title"),
                    icon: "fa fa-file-pdf icon-garnet",
                },
                {
                    key: "questionnaires",
                    name: this.$t("user.questionnaires.title"),
                    icon: "fa fa-clipboard icon-cyan",
                },
                {
                    key: "calendars",
                    name: this.$t("user.calendars.title"),
                    icon: "fa fa-calendar icon-blue",
                },
                {
                    key: "setting->prospects-table",
                    name: this.$t("user.settings.prospects_table.title"),
                    icon: "fa fa-columns icon-green",
                },
                {
                    key: "setting->prospects-table.menus",
                    name: this.$t("user.settings.prospects_table_menus.title"),
                    icon: "fa fa-columns icon-green",
                },
                {
                    key: "setting->prospect-profile",
                    name: this.$t("user.settings.prospect_profile.title"),
                    icon: "fa fa-address-card icon-blue",
                },
                {
                    key: "prospects",
                    name: "Prospects",
                    icon: "fa fa-users icon-brown",
                },
            ],
        };
    },

    methods: {
        async duplicateProfile() {
            this.duplicating = true;

            try {
                const { data } = await UserDuplicateService.duplicate(
                    this.project.slug,
                    this.duplicateOriginTab == 0
                        ? this.user.id
                        : this.userFrom.id,
                    this.duplicateOriginTab == 0
                        ? this.duplicate
                        : {
                              ...this.duplicate,
                              users: [this.user.id],
                          }
                );

                if (this.duplicateOriginTab == 1) {
                    store.dispatch(SHOW_USER, this.user.id);
                }
            } finally {
                this.duplicating = false;
            }
        },
    },

    computed: {
        ...mapGetters(["project", "user", "users"]),

        allDuplicatables: {
            get() {
                return (
                    this.duplicate.duplicatables.length ==
                    this.duplicatables.length
                );
            },
            set(value) {
                this.duplicate.duplicatables = value
                    ? this.duplicatables.map((d) => d.key)
                    : [];
            },
        },

        /**
         *
         */
        filteredUsers() {
            const keyword = removeStringAccent(this.userKeyword);

            return this.users.filter(
                (user) =>
                    user.id != this.user.id &&
                    removeStringAccent(user.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredDuplicatables() {
            const keyword = removeStringAccent(this.duplicatableKeyword);

            return this.duplicatables.filter(
                (duplicatable) =>
                    removeStringAccent(duplicatable.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
