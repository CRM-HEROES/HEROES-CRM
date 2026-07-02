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
                    <template #1>{{ $t("role.duplicate.from") }}</template>
                    <template #2>{{ $t("role.duplicate.for") }}</template>
                </tab>
                <tab-layout
                    :count="2"
                    :tab="duplicateOriginTab"
                    class="hc-flex-1"
                >
                    <template #1>
                        <div class="hc-flex-column" style="height: 100%">
                            <search v-model="roleKeyword" />
                            <item-list class="hc-flex-1" padding="5px">
                                <role-row
                                    v-for="role in filteredRoles"
                                    :key="role.id"
                                    :role="role"
                                    v-model="duplicate.roles"
                                />
                            </item-list>
                            <buttons>
                                <button
                                    @click.prevent="tab = 1"
                                    v-text="$t('next')"
                                    :disabled="duplicate.roles.length == 0"
                                ></button>
                            </buttons>
                        </div>
                    </template>

                    <template #2>
                        <div class="hc-flex-column" style="height: 100%">
                            <search v-model="roleKeyword" />
                            <item-list class="hc-flex-1" padding="5px">
                                <role-from-row
                                    v-for="role in filteredRoles"
                                    :key="role.id"
                                    :role="role"
                                    @click.prevent="
                                        (roleFrom = role), (tab = 1)
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
                                : 'Dupliquer depuis ' + roleFrom.name
                        "
                    ></div>
                </item>
                <search v-model="duplicatableKeyword" />
                <item-list class="hc-flex-1" padding="5px">
                    <item tag="label">
                        <icon class="fa fa-copy" />
                        <div
                            class="hc-item-main-content"
                            v-text="$t('all')"
                        ></div>
                        <checkbox v-model="allDuplicatables" />
                    </item>
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
import RoleDuplicateService from "@/apis/project/role/duplicate";
import store from "@/store";
import { mapGetters } from "vuex";

import { SHOW_ROLE } from "@/actions/project/role";

import DuplicatableRow from "./DuplicatableRow.vue";
import RoleFromRow from "./RoleFromRow.vue";
import RoleRow from "./RoleRow.vue";

export default {
    components: {
        DuplicatableRow,
        RoleFromRow,
        RoleRow,
    },

    data() {
        return {
            name: "role-duplicate",

            tab: 0,
            duplicateOriginTab: 0,
            roleKeyword: "",
            duplicatableKeyword: "",

            roleFrom: null,

            duplicate: {
                roles: [],
                duplicatables: [],
            },

            duplicating: false,

            /**
             */
            duplicatables: [
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
            ],
        };
    },

    methods: {
        async duplicateProfile() {
            this.duplicating = true;

            try {
                const { data } = await RoleDuplicateService.duplicate(
                    this.project.slug,
                    this.duplicateOriginTab == 0
                        ? this.role.id
                        : this.roleFrom.id,
                    this.duplicateOriginTab == 0
                        ? this.duplicate
                        : {
                              ...this.duplicate,
                              roles: [this.role.id],
                          }
                );

                if (this.duplicateOriginTab == 1) {
                    store.dispatch(SHOW_ROLE, this.role.id);
                }
            } finally {
                this.duplicating = false;
            }
        },
    },

    computed: {
        ...mapGetters(["project", "role", "roles"]),

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
        filteredRoles() {
            const keyword = removeStringAccent(this.roleKeyword);

            return this.roles.filter(
                (role) =>
                    role.id != this.role.id &&
                    removeStringAccent(role.name).indexOf(keyword) >= 0
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
