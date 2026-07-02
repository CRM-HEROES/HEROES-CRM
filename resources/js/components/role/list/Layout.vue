<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <div class="hc-flex-column" style="height: 100%">
                <search v-model="roleKeyword" />
                <item-list class="hc-flex-1" padding="5px">
                    <role-row
                        v-for="role in filteredRoles"
                        :key="role.id"
                        :role="role"
                        @click.prevent="manageRole(role)"
                    />
                </item-list>
                <buttons v-if="can('all.project.role.add')">
                    <a @click.prevent="addRole" v-text="$t('add')"></a>
                </buttons>
            </div>
        </template>
        <template #2>
            <div class="hc-flex-column" style="height: 100%" v-if="role">
                <item tag="a" @click.prevent="manageRole(null)">
                    <icon class="fa fa-caret-left" />
                    <div class="hc-item-main-content" v-text="role.name"></div>
                </item>
                <item-list class="hc-flex-1" padding="5px">
                    <item
                        tag="a"
                        class="hc-role-relation"
                        @click.prevent="managePermissions"
                    >
                        <icon class="fa fa-hand-paper" />
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                class="hc-role-relation-title"
                                v-text="$t('user.permissions.title')"
                            ></span>
                            <span v-text="$t('user.permissions.title')"></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item tag="a" @click.prevent="duplicate">
                        <icon class="fa fa-copy" />
                        <div
                            class="hc-item-main-content"
                            v-text="'Dupliquer'"
                        ></div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item
                        tag="a"
                        class="hc-role-relation"
                        @click.prevent="manageCalendars"
                    >
                        <icon class="fa fa-calendar" />
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                class="hc-role-relation-title"
                                v-text="$t('user.calendars.title')"
                            ></span>
                            <span
                                :class="[
                                    'hc-role-relation-value',
                                    roleCalendarsNames ? 'with-value' : '',
                                ]"
                                v-text="
                                    roleCalendarsNames
                                        ? roleCalendarsNames
                                        : $t('none') + ' ...'
                                "
                            ></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item
                        tag="a"
                        class="hc-role-relation"
                        @click.prevent="manageCategories"
                    >
                        <icon class="fa fa-tags" />
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                class="hc-role-relation-title"
                                v-text="$t('user.categories.title')"
                            ></span>
                            <span
                                :class="[
                                    'hc-role-relation-value',
                                    roleCategoriesNames ? 'with-value' : '',
                                ]"
                                v-text="
                                    roleCategoriesNames
                                        ? roleCategoriesNames
                                        : $t('none') + ' ...'
                                "
                            ></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item
                        tag="a"
                        class="hc-role-relation"
                        @click.prevent="manageDocuments"
                    >
                        <icon class="fa fa-file-pdf" />
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                class="hc-role-relation-title"
                                v-text="$t('user.documents.title')"
                            ></span>
                            <span
                                :class="[
                                    'hc-role-relation-value',
                                    roleDocumentsNames ? 'with-value' : '',
                                ]"
                                v-text="
                                    roleDocumentsNames
                                        ? roleDocumentsNames
                                        : $t('none') + ' ...'
                                "
                            ></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item
                        tag="a"
                        class="hc-role-relation"
                        @click.prevent="manageFolders"
                    >
                        <icon class="fa fa-folder" />
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                class="hc-role-relation-title"
                                v-text="$t('user.folders.title')"
                            ></span>
                            <span
                                :class="[
                                    'hc-role-relation-value',
                                    roleFoldersNames ? 'with-value' : '',
                                ]"
                                v-text="
                                    roleFoldersNames
                                        ? roleFoldersNames
                                        : $t('none') + ' ...'
                                "
                            ></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item
                        tag="a"
                        class="hc-role-relation"
                        @click.prevent="manageOrderActions"
                    >
                        <icon class="fa fa-person-digging" />
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                class="hc-role-relation-title"
                                v-text="$t('user.order_actions.title')"
                            ></span>
                            <span
                                :class="[
                                    'hc-role-relation-value',
                                    roleOrderActionsNames ? 'with-value' : '',
                                ]"
                                v-text="
                                    roleOrderActionsNames
                                        ? roleOrderActionsNames
                                        : $t('none') + ' ...'
                                "
                            ></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item
                        tag="a"
                        class="hc-role-relation"
                        @click.prevent="manageOrderSteps"
                    >
                        <icon class="fa fa-shopping-cart" />
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                class="hc-role-relation-title"
                                v-text="$t('user.order_steps.title')"
                            ></span>
                            <span
                                :class="[
                                    'hc-role-relation-value',
                                    roleOrderStepsNames ? 'with-value' : '',
                                ]"
                                v-text="
                                    roleOrderStepsNames
                                        ? roleOrderStepsNames
                                        : $t('none') + ' ...'
                                "
                            ></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item
                        tag="a"
                        class="hc-role-relation"
                        @click.prevent="manageQuestionnaires"
                    >
                        <icon class="fa fa-clipboard" />
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                class="hc-role-relation-title"
                                v-text="$t('user.questionnaires.title')"
                            ></span>
                            <span
                                :class="[
                                    'hc-role-relation-value',
                                    roleQuestionnairesNames ? 'with-value' : '',
                                ]"
                                v-text="
                                    roleQuestionnairesNames
                                        ? roleQuestionnairesNames
                                        : $t('none') + ' ...'
                                "
                            ></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item
                        tag="a"
                        class="hc-role-relation"
                        @click.prevent="manageThreads"
                    >
                        <icon class="fa fa-envelope" />
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                class="hc-role-relation-title"
                                v-text="$t('user.threads.title')"
                            ></span>
                            <span
                                :class="[
                                    'hc-role-relation-value',
                                    roleThreadsNames ? 'with-value' : '',
                                ]"
                                v-text="
                                    roleThreadsNames
                                        ? roleThreadsNames
                                        : $t('none') + ' ...'
                                "
                            ></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item
                        tag="a"
                        class="hc-role-relation"
                        @click.prevent="manageUsers"
                    >
                        <icon class="fa fa-user-tie" />
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                class="hc-role-relation-title"
                                v-text="$t('user.users.title')"
                            ></span>
                            <span
                                :class="[
                                    'hc-role-relation-value',
                                    roleUsersNames ? 'with-value' : '',
                                ]"
                                v-text="
                                    roleUsersNames
                                        ? roleUsersNames
                                        : $t('none') + ' ...'
                                "
                            ></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>
                </item-list>
                <buttons>
                    <a @click.prevent="edit" v-text="$t('update')"></a>
                </buttons>
            </div>
        </template>
    </tab-layout>
</template>

<style>
.hc-role-relation {
    padding: 2px 0 !important;
    align-items: start;
}

.hc-role-relation-title {
    color: #000000;
}

.hc-role-relation-value {
    font-size: 11px;
    opacity: 0.5;
    color: #000000;
}

.hc-role-relation-value.with-value {
    opacity: 1;
    color: #12a0f3;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { SHOW_ROLE, SET_ROLE } from "@/actions/project/role";
import { OPEN_MODAL } from "@/actions/modal";
import { OPEN_SUB_SLIDE } from "@/actions/slide";

// Components
import RoleRow from "./RoleRow.vue";

export default {
    components: {
        RoleRow,
    },

    data() {
        return {
            name: "manage-roles",
            tab: 0,
            roleKeyword: "",
        };
    },

    created() {
        if (this.role) {
            this.tab = 1;
        }
    },

    methods: {
        /**
         *
         */
        addRole() {
            store.commit(OPEN_MODAL, "role-add");
        },

        /**
         *
         * @param {*} role
         */
        async manageRole(role) {
            store.commit(SET_ROLE, role);

            if (role) {
                try {
                    const u = await store.dispatch(SHOW_ROLE, role.id);
                    store.commit(SET_ROLE, u);
                } finally {
                }
            }
        },

        /**
         *
         */
        duplicate() {
            store.commit(OPEN_SUB_SLIDE, "role-duplicate");
        },

        /**
         *
         */
        manageCalendars() {
            store.commit(OPEN_SUB_SLIDE, "role-manage-calendars");
        },

        /**
         *
         */
        manageCategories() {
            store.commit(OPEN_SUB_SLIDE, "role-manage-categories");
        },

        /**
         *
         */
        manageDocuments() {
            store.commit(OPEN_SUB_SLIDE, "role-manage-documents");
        },

        /**
         *
         */
        manageFolders() {
            store.commit(OPEN_SUB_SLIDE, "role-manage-folders");
        },

        /**
         *
         */
        manageGroups() {
            store.commit(OPEN_SUB_SLIDE, "role-manage-groups");
        },

        /**
         *
         */
        manageOrderActions() {
            store.commit(OPEN_SUB_SLIDE, "role-manage-order-actions");
        },

        /**
         *
         */
        manageOrderSteps() {
            store.commit(OPEN_SUB_SLIDE, "role-manage-order-steps");
        },

        /**
         *
         */
        manageQuestionnaires() {
            store.commit(OPEN_SUB_SLIDE, "role-manage-questionnaires");
        },

        /**
         *
         */
        manageThreads() {
            store.commit(OPEN_SUB_SLIDE, "role-manage-threads");
        },

        /**
         *
         */
        managePermissions() {
            store.commit(OPEN_SUB_SLIDE, "role-manage-permissions");
        },

        /**
         *
         */
        manageUsers() {
            store.commit(OPEN_SUB_SLIDE, "role-manage-users");
        },

        /**
         *
         */
        edit() {
            store.commit(OPEN_MODAL, "role-update");
            store.commit(SET_ROLE, this.role);
        },
    },

    watch: {
        role(newValue) {
            if (this.slideOpen(this.name)) {
                if (newValue) {
                    this.tab = 1;
                } else {
                    this.tab = 0;
                }
            }
        },
    },

    computed: {
        ...mapGetters([
            "roles",
            "role",
            "slideOpen",
            "roleCalendars",
            "roleCategories",
            "roleDocuments",
            "roleFolders",
            "roleGroups",
            "roleOrderActions",
            "roleOrderSteps",
            "roleQuestionnaires",
            "roleThreads",
            "rolePermissions",
            "roleUsers",
            "slideOpen",
            "can",
        ]),

        /**
         *
         */
        roleCalendarsNames() {
            return this.roleCalendars.map((item) => item.name).join(", ");
        },

        /**
         *
         */
        roleCategoriesNames() {
            return this.roleCategories.map((item) => item.name).join(", ");
        },

        /**
         *
         */
        roleDocumentsNames() {
            return this.roleDocuments.map((item) => item.name).join(", ");
        },

        /**
         *
         */
        roleFoldersNames() {
            return this.roleFolders.map((item) => item.name).join(", ");
        },

        /**
         *
         */
        roleGroupsNames() {
            return this.roleGroups.map((item) => item.name).join(", ");
        },

        /**
         *
         */
        roleOrderActionsNames() {
            return this.roleOrderActions.map((item) => item.name).join(", ");
        },

        /**
         *
         */
        roleOrderStepsNames() {
            return this.roleOrderSteps.map((item) => item.name).join(", ");
        },

        /**
         *
         */
        roleQuestionnairesNames() {
            return this.roleQuestionnaires.map((item) => item.name).join(", ");
        },

        /**
         *
         */
        roleThreadsNames() {
            return this.roleThreads.map((item) => item.name).join(", ");
        },

        /**
         *
         */
        roleUsersNames() {
            return this.roleUsers.map((item) => item.name).join(", ");
        },

        /**
         *
         */
        filteredRoles() {
            const keyword = removeStringAccent(this.roleKeyword);

            return this.roles.filter(
                (role) => removeStringAccent(role.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
