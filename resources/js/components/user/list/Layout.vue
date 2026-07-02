<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <div class="hc-flex-column" style="height: 100%">
                <search v-model="userKeyword" />
                <item-list class="hc-flex-1" padding="5px">
                    <user-row
                        v-for="user in filteredUsers"
                        :key="user.id"
                        :user="user"
                        @click.prevent="manageUser(user)"
                    />
                </item-list>
                <buttons>
                    <a @click.prevent="addUser" v-text="$t('add')"></a>
                </buttons>
            </div>
        </template>
        <template #2>
            <div class="hc-flex-column" style="height: 100%" v-if="user">
                <item tag="a" @click.prevent="manageUser(null)">
                    <icon class="fa fa-caret-left" />
                    <div class="hc-item-main-content" v-text="user.name"></div>
                </item>
                <search v-model="searchKeyword" />
                <item-list
                    v-if="searchKeyword.length > 0"
                    class="hc-flex-1"
                    padding="5px"
                >
                    <template
                        v-for="result in filteredSearchResults"
                        :key="result.type + '-' + result.id"
                    >
                        <calendar-row
                            v-if="result.type == 'calendar'"
                            :calendar="result"
                            :value="isCalendarChecked(result)"
                        />
                        <category-row
                            v-else-if="result.type == 'category'"
                            :category="result"
                            :value="isCategoryChecked(result)"
                        />
                        <document-row
                            v-else-if="result.type == 'document'"
                            :document="result"
                            :value="isDocumentChecked(result)"
                        />
                        <folder-row
                            v-else-if="result.type == 'folder'"
                            :folder="result"
                            :value="isFolderChecked(result)"
                        />
                        <group-row
                            v-else-if="result.type == 'group'"
                            :group="result"
                            :value="isGroupChecked(result)"
                        />
                        <menu-row
                            v-else-if="result.type == 'menu'"
                            :menu="result"
                            :value="isMenuChecked(result)"
                        />
                        <order-action-row
                            v-else-if="result.type == 'order-action'"
                            :order-action="result"
                            :value="isOrderActionChecked(result)"
                        />
                        <order-step-row
                            v-else-if="result.type == 'order-step'"
                            :order-step="result"
                            :value="isOrderStepChecked(result)"
                        />
                        <permission-row
                            v-else-if="result.type == 'permission'"
                            :permission="result"
                            :value="isPermissionChecked(result)"
                            :permission-value="result.value"
                        />
                        <questionnaire-row
                            v-else-if="result.type == 'questionnaire'"
                            :questionnaire="result"
                            :value="isQuestionnaireChecked(result)"
                        />
                        <role-row
                            v-else-if="result.type == 'role'"
                            :role="result"
                            :value="isRoleChecked(result)"
                        />
                        <thread-row
                            v-else-if="result.type == 'thread'"
                            :thread="result"
                            :value="isThreadChecked(result)"
                        />
                        <assignable-user-row
                            v-else-if="result.type == 'user'"
                            :user="result"
                            :value="isUserChecked(result)"
                        />
                    </template>
                </item-list>
                <item-list v-else class="hc-flex-1" padding="5px">
                    <item
                        tag="a"
                        class="hc-user-relation"
                        @click.prevent="manageCalendars"
                    >
                        <icon class="fa fa-calendar" />
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                class="hc-user-relation-title"
                                v-text="$t('user.calendars.title')"
                            ></span>
                            <span
                                :class="[
                                    'hc-user-relation-value',
                                    userCalendarsNames ? 'with-value' : '',
                                ]"
                                v-text="
                                    userCalendarsNames
                                        ? userCalendarsNames
                                        : $t('none') + ' ...'
                                "
                            ></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item
                        tag="a"
                        class="hc-user-relation"
                        @click.prevent="manageCategories"
                    >
                        <icon class="fa fa-tags" />
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                class="hc-user-relation-title"
                                v-text="$t('user.categories.title')"
                            ></span>
                            <span
                                :class="[
                                    'hc-user-relation-value',
                                    userCategoriesNames ? 'with-value' : '',
                                ]"
                                v-text="
                                    userCategoriesNames
                                        ? userCategoriesNames
                                        : $t('none') + ' ...'
                                "
                            ></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item
                        tag="a"
                        class="hc-user-relation"
                        @click.prevent="manageDocuments"
                    >
                        <icon class="fa fa-file-pdf" />
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                class="hc-user-relation-title"
                                v-text="$t('user.documents.title')"
                            ></span>
                            <span
                                :class="[
                                    'hc-user-relation-value',
                                    userDocumentsNames ? 'with-value' : '',
                                ]"
                                v-text="
                                    userDocumentsNames
                                        ? userDocumentsNames
                                        : $t('none') + ' ...'
                                "
                            ></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item
                        tag="a"
                        class="hc-user-relation"
                        @click.prevent="manageFolders"
                    >
                        <icon class="fa fa-folder" />
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                class="hc-user-relation-title"
                                v-text="$t('user.folders.title')"
                            ></span>
                            <span
                                :class="[
                                    'hc-user-relation-value',
                                    userFoldersNames ? 'with-value' : '',
                                ]"
                                v-text="
                                    userFoldersNames
                                        ? userFoldersNames
                                        : $t('none') + ' ...'
                                "
                            ></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item
                        tag="a"
                        class="hc-user-relation"
                        @click.prevent="manageGroups"
                    >
                        <icon class="fa fa-users" />
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                class="hc-user-relation-title"
                                v-text="$t('user.groups.title')"
                            ></span>
                            <span
                                :class="[
                                    'hc-user-relation-value',
                                    userGroupsNames ? 'with-value' : '',
                                ]"
                                v-text="
                                    userGroupsNames
                                        ? userGroupsNames
                                        : $t('none') + ' ...'
                                "
                            ></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item
                        tag="a"
                        class="hc-user-relation"
                        @click.prevent="manageMenus"
                    >
                        <icon class="fa fa-list" />
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                class="hc-user-relation-title"
                                v-text="$t('user.menus.title')"
                            ></span>
                            <span
                                :class="[
                                    'hc-user-relation-value',
                                    userMenusNames ? 'with-value' : '',
                                ]"
                                v-text="
                                    userMenusNames
                                        ? userMenusNames
                                        : $t('none') + ' ...'
                                "
                            ></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item
                        tag="a"
                        class="hc-user-relation"
                        @click.prevent="manageOrderActions"
                    >
                        <icon class="fa fa-person-digging" />
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                class="hc-user-relation-title"
                                v-text="$t('user.order_actions.title')"
                            ></span>
                            <span
                                :class="[
                                    'hc-user-relation-value',
                                    userOrderActionsNames ? 'with-value' : '',
                                ]"
                                v-text="
                                    userOrderActionsNames
                                        ? userOrderActionsNames
                                        : $t('none') + ' ...'
                                "
                            ></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item
                        tag="a"
                        class="hc-user-relation"
                        @click.prevent="manageOrderSteps"
                    >
                        <icon class="fa fa-shopping-cart" />
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                class="hc-user-relation-title"
                                v-text="$t('user.order_steps.title')"
                            ></span>
                            <span
                                :class="[
                                    'hc-user-relation-value',
                                    userOrderStepsNames ? 'with-value' : '',
                                ]"
                                v-text="
                                    userOrderStepsNames
                                        ? userOrderStepsNames
                                        : $t('none') + ' ...'
                                "
                            ></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item
                        tag="a"
                        class="hc-user-relation"
                        @click.prevent="managePermissions"
                    >
                        <icon class="fa fa-hand-paper" />
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                class="hc-user-relation-title"
                                v-text="$t('user.permissions.title')"
                            ></span>
                            <span v-text="$t('user.permissions.title')"></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item
                        tag="a"
                        class="hc-user-relation"
                        @click.prevent="manageQuestionnaires"
                    >
                        <icon class="fa fa-clipboard" />
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                class="hc-user-relation-title"
                                v-text="$t('user.questionnaires.title')"
                            ></span>
                            <span
                                :class="[
                                    'hc-user-relation-value',
                                    userQuestionnairesNames ? 'with-value' : '',
                                ]"
                                v-text="
                                    userQuestionnairesNames
                                        ? userQuestionnairesNames
                                        : $t('none') + ' ...'
                                "
                            ></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item
                        tag="a"
                        class="hc-user-relation"
                        @click.prevent="manageThreads"
                    >
                        <icon class="fa fa-envelope" />
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                class="hc-user-relation-title"
                                v-text="$t('user.threads.title')"
                            ></span>
                            <span
                                :class="[
                                    'hc-user-relation-value',
                                    userThreadsNames ? 'with-value' : '',
                                ]"
                                v-text="
                                    userThreadsNames
                                        ? userThreadsNames
                                        : $t('none') + ' ...'
                                "
                            ></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item
                        tag="a"
                        class="hc-user-relation"
                        @click.prevent="manageUsers"
                    >
                        <icon class="fa fa-user" />
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                class="hc-user-relation-title"
                                v-text="$t('user.users.title')"
                            ></span>
                            <span
                                :class="[
                                    'hc-user-relation-value',
                                    userUsersNames ? 'with-value' : '',
                                ]"
                                v-text="
                                    userUsersNames
                                        ? userUsersNames
                                        : $t('none') + ' ...'
                                "
                            ></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>
                    <item
                        tag="a"
                        class="hc-user-relation"
                        @click.prevent="manageRoles"
                    >
                        <icon class="fa fa-user-md" />
                        <div class="hc-item-main-content hc-flex-column">
                            <span
                                class="hc-user-relation-title"
                                v-text="$t('user.roles.title')"
                            ></span>
                            <span
                                :class="[
                                    'hc-user-relation-value',
                                    userRolesNames ? 'with-value' : '',
                                ]"
                                v-text="
                                    userRolesNames
                                        ? userRolesNames
                                        : $t('none') + ' ...'
                                "
                            ></span>
                        </div>
                        <icon class="fa fa-caret-right" />
                    </item>
                </item-list>
                <buttons v-if="can('all')">
                    <a @click.prevent="edit" v-text="$t('update')"></a>
                </buttons>
            </div>
        </template>
    </tab-layout>
</template>

<style>
.hc-user-relation {
    padding: 2px 0 !important;
    align-items: start;
}

.hc-user-relation-title {
    color: #000000;
}

.hc-user-relation-value {
    font-size: 11px;
    opacity: 0.5;
    color: #000000;
}

.hc-user-relation-value.with-value {
    opacity: 1;
    color: #12a0f3;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import permissionsList from "@/utils/permissions";

// Actions
import { SHOW_USER, SET_USER } from "@/actions/project/user";
import { OPEN_MODAL } from "@/actions/modal";
import { OPEN_SUB_SLIDE } from "@/actions/slide";
import { SET_GLOBAL_USER } from "@/actions/user";
import { FETCH_USER_PERMISSIONS } from "@/actions/project/user/permission";

// Components
import UserRow from "./UserRow.vue";

import CalendarRow from "../calendar/CalendarRow.vue";
import CategoryRow from "../category/CategoryRow.vue";
import DocumentRow from "../document/DocumentRow.vue";
import FolderRow from "../folder/FolderRow.vue";
import GroupRow from "../group/GroupRow.vue";
import MenuRow from "../menu/MenuRow.vue";
import OrderActionRow from "../order-action/OrderActionRow.vue";
import OrderStepRow from "../order-step/OrderStepRow.vue";
import PermissionRow from "../permission/PermissionRow.vue";
import QuestionnaireRow from "../questionnaire/QuestionnaireRow.vue";
import RoleRow from "../role/RoleRow.vue";
import ThreadRow from "../thread/ThreadRow.vue";
import AssignableUserRow from "../user/UserRow.vue";

export default {
    components: {
        UserRow,
        CalendarRow,
        CategoryRow,
        DocumentRow,
        FolderRow,
        GroupRow,
        MenuRow,
        OrderActionRow,
        OrderStepRow,
        PermissionRow,
        QuestionnaireRow,
        RoleRow,
        ThreadRow,
        AssignableUserRow,
    },

    data() {
        return {
            tab: 0,
            userKeyword: "",
            searchKeyword: "",
        };
    },

    created() {
        this.computePermissionsList(permissionsList);
    },

    methods: {
        /**
         *
         */
        fetchUserPermissions() {
            if (this.user) {
                store.dispatch(FETCH_USER_PERMISSIONS, this.user);
            }
        },

        computePermissionsList(list, key) {
            list.value = (key ? key + "." : "") + list.key;

            if (list.sub) {
                list.sub.forEach((sub) =>
                    this.computePermissionsList(sub, list.value)
                );
            }
        },

        /**
         *
         */
        addUser() {
            store.commit(OPEN_MODAL, "user-add");
        },

        /**
         *
         * @param {*} user
         */
        async manageUser(user) {
            store.commit(SET_USER, user);

            if (user) {
                try {
                    const u = await store.dispatch(SHOW_USER, user.id);
                    store.commit(SET_USER, u);
                } finally {
                }
            }
        },

        /**
         *
         */
        manageCategories() {
            store.commit(OPEN_SUB_SLIDE, "user-manage-categories");
        },

        /**
         *
         */
        manageCalendars() {
            store.commit(OPEN_SUB_SLIDE, "user-manage-calendars");
        },

        /**
         *
         */
        manageDocuments() {
            store.commit(OPEN_SUB_SLIDE, "user-manage-documents");
        },

        /**
         *
         */
        manageFolders() {
            store.commit(OPEN_SUB_SLIDE, "user-manage-folders");
        },

        /**
         *
         */
        manageGroups() {
            store.commit(OPEN_SUB_SLIDE, "user-manage-groups");
        },

        /**
         *
         */
        manageMenus() {
            store.commit(OPEN_SUB_SLIDE, "user-manage-menus");
        },

        /**
         *
         */
        manageOrderActions() {
            store.commit(OPEN_SUB_SLIDE, "user-manage-order-actions");
        },

        /**
         *
         */
        manageOrderSteps() {
            store.commit(OPEN_SUB_SLIDE, "user-manage-order-steps");
        },

        /**
         *
         */
        managePermissions() {
            store.commit(OPEN_SUB_SLIDE, "user-manage-permissions");
        },

        /**
         *
         */
        manageQuestionnaires() {
            store.commit(OPEN_SUB_SLIDE, "user-manage-questionnaires");
        },

        /**
         *
         */
        manageRoles() {
            store.commit(OPEN_SUB_SLIDE, "user-manage-roles");
        },

        /**
         *
         */
        manageThreads() {
            store.commit(OPEN_SUB_SLIDE, "user-manage-threads");
        },

        /**
         *
         */
        manageUsers() {
            store.commit(OPEN_SUB_SLIDE, "user-manage-users");
        },

        /**
         *
         */
        edit() {
            store.commit(SET_GLOBAL_USER, this.user);
            this.$router.push({
                name: "global.user.show",
                params: {
                    user: this.user.id,
                },
            });
        },

        /**
         *
         * @param {*} calendar
         */
        isCalendarChecked(calendar) {
            return (
                this.userCalendars.findIndex((l) => l.id == calendar.id) >= 0
            );
        },

        /**
         *
         * @param {*} category
         */
        isCategoryChecked(category) {
            return (
                this.userCategories.findIndex((l) => l.id == category.id) >= 0
            );
        },

        /**
         *
         * @param {*} document
         */
        isDocumentChecked(document) {
            return (
                this.userDocuments.findIndex((l) => l.id == document.id) >= 0
            );
        },

        /**
         *
         * @param {*} folder
         */
        isFolderChecked(folder) {
            return this.userFolders.findIndex((l) => l.id == folder.id) >= 0;
        },

        /**
         *
         * @param {*} group
         */
        isGroupChecked(group) {
            return this.userGroups.findIndex((l) => l.id == group.id) >= 0;
        },

        /**
         *
         * @param {*} menu
         */
        isMenuChecked(menu) {
            return this.userMenus.findIndex((l) => l.id == menu.id) >= 0;
        },

        /**
         *
         * @param {*} orderAction
         */
        isOrderActionChecked(orderAction) {
            return (
                this.userOrderActions.findIndex(
                    (l) => l.id == orderAction.id
                ) >= 0
            );
        },

        /**
         *
         * @param {*} orderStep
         */
        isOrderStepChecked(orderStep) {
            return (
                this.userOrderSteps.findIndex((l) => l.id == orderStep.id) >= 0
            );
        },

        /**
         *
         * @param {*} permission
         */
        isPermissionChecked(permission) {
            return (
                this.userPermissions.findIndex((l) => l == permission.value) >=
                0
            );
        },

        /**
         *
         * @param {*} questionnaire
         */
        isQuestionnaireChecked(questionnaire) {
            return (
                this.userQuestionnaires.findIndex(
                    (l) => l.id == questionnaire.id
                ) >= 0
            );
        },

        /**
         *
         * @param {*} role
         */
        isRoleChecked(role) {
            return this.userRoles.findIndex((l) => l.id == role.id) >= 0;
        },

        /**
         *
         * @param {*} thread
         */
        isThreadChecked(thread) {
            return this.userThreads.findIndex((l) => l.id == thread.id) >= 0;
        },

        /**
         *
         * @param {*} user
         */
        isUserChecked(user) {
            return this.userUsers.findIndex((l) => l.id == user.id) >= 0;
        },
    },

    watch: {
        user(newValue) {
            if (this.slideOpen("manage-users")) {
                if (newValue) {
                    this.tab = 1;
                    this.fetchUserPermissions();
                } else {
                    this.tab = 0;
                }
            }
        },
    },

    computed: {
        ...mapGetters([
            "users",
            "calendars",
            "categories",
            "documents",
            "folders",
            "groups",
            "menus",
            "orderActions",
            "orderSteps",
            "questionnaires",
            "roles",
            "threads",
            "user",
            "slideOpen",
            "userCalendars",
            "userCategories",
            "userDocuments",
            "userFolders",
            "userGroups",
            "userMenus",
            "userOrderActions",
            "userOrderSteps",
            "userPermissions",
            "userQuestionnaires",
            "userRoles",
            "userThreads",
            "userUsers",
            "slideOpen",
            "can",
        ]),

        /**
         *
         */
        userCalendarsNames() {
            return this.userCalendars.map((item) => item.name).join(", ");
        },

        /**
         *
         */
        userCategoriesNames() {
            return this.userCategories.map((item) => item.name).join(", ");
        },

        /**
         *
         */
        userDocumentsNames() {
            return this.userDocuments.map((item) => item.name).join(", ");
        },

        /**
         *
         */
        userFoldersNames() {
            return this.userFolders.map((item) => item.name).join(", ");
        },

        /**
         *
         */
        userGroupsNames() {
            return this.userGroups.map((item) => item.name).join(", ");
        },

        /**
         *
         */
        userMenusNames() {
            return this.userMenus.map((item) => item.name).join(", ");
        },

        /**
         *
         */
        userOrderActionsNames() {
            return this.userOrderActions.map((item) => item.name).join(", ");
        },

        /**
         *
         */
        userOrderStepsNames() {
            return this.userOrderSteps.map((item) => item.name).join(", ");
        },

        /**
         *
         */
        userQuestionnairesNames() {
            return this.userQuestionnaires.map((item) => item.name).join(", ");
        },

        /**
         *
         */
        userUsersNames() {
            return this.userUsers.map((item) => item.name).join(", ");
        },

        /**
         *
         */
        userThreadsNames() {
            return this.userThreads.map((item) => item.name).join(", ");
        },

        /**
         *
         */
        userRolesNames() {
            return this.userRoles.map((item) => item.name).join(", ");
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

        /**
         *
         */
        filteredCalendars() {
            const keyword = removeStringAccent(this.searchKeyword);

            return this.calendars
                .filter(
                    (calendar) =>
                        removeStringAccent(calendar.name).indexOf(keyword) >= 0
                )
                .map((result) => ({ ...result, type: "calendar" }));
        },

        /**
         *
         */
        filteredCategories() {
            const keyword = removeStringAccent(this.searchKeyword);

            return this.categories
                .filter(
                    (category) =>
                        removeStringAccent(category.name).indexOf(keyword) >= 0
                )
                .map((result) => ({ ...result, type: "category" }));
        },

        /**
         *
         */
        filteredDocuments() {
            const keyword = removeStringAccent(this.searchKeyword);

            return this.documents
                .filter(
                    (document) =>
                        removeStringAccent(document.name).indexOf(keyword) >= 0
                )
                .map((result) => ({ ...result, type: "document" }));
        },

        /**
         *
         */
        filteredFolders() {
            const keyword = removeStringAccent(this.searchKeyword);

            return this.folders
                .filter(
                    (folder) =>
                        folder.for == "prospect" &&
                        removeStringAccent(folder.name).indexOf(keyword) >= 0
                )
                .map((result) => ({ ...result, type: "folder" }));
        },

        /**
         *
         */
        filteredGroups() {
            const keyword = removeStringAccent(this.searchKeyword);

            return this.groups
                .filter(
                    (group) =>
                        removeStringAccent(group.name).indexOf(keyword) >= 0
                )
                .map((result) => ({ ...result, type: "group" }));
        },

        /**
         *
         */
        filteredMenus() {
            const keyword = removeStringAccent(this.searchKeyword);

            return this.menus
                .filter(
                    (menu) =>
                        removeStringAccent(menu.name).indexOf(keyword) >= 0
                )
                .map((result) => ({ ...result, type: "menu" }));
        },

        /**
         *
         */
        filteredOrderActions() {
            const keyword = removeStringAccent(this.searchKeyword);

            return this.orderActions
                .filter(
                    (orderAction) =>
                        removeStringAccent(orderAction.name).indexOf(keyword) >=
                        0
                )
                .map((result) => ({ ...result, type: "order-action" }));
        },

        /**
         *
         */
        filteredOrderSteps() {
            const keyword = removeStringAccent(this.searchKeyword);

            return this.orderSteps
                .filter(
                    (orderStep) =>
                        removeStringAccent(orderStep.name).indexOf(keyword) >= 0
                )
                .map((result) => ({ ...result, type: "order-step" }));
        },

        allPermissions() {
            let permissionGroups = [permissionsList];

            for (let i = 0; i < permissionGroups.length; ++i) {
                let permission = permissionGroups[i];

                if (permission.sub) {
                    for (let j = 0; j < permission.sub.length; ++j) {
                        permissionGroups.push(permission.sub[j]);
                    }
                }
            }

            return permissionGroups.filter((p) => !p.sub);
        },

        /**
         * Filtered permissions
         */
        filteredPermissions() {
            const keyword = removeStringAccent(this.searchKeyword);

            return this.allPermissions
                .filter(
                    (permission) =>
                        removeStringAccent(permission.name).indexOf(keyword) >=
                        0
                )
                .map((result) => ({
                    ...result,
                    id: result.value,
                    type: "permission",
                }));
        },

        /**
         *
         */
        filteredQuestionnaires() {
            const keyword = removeStringAccent(this.searchKeyword);

            return this.questionnaires
                .filter(
                    (questionnaire) =>
                        removeStringAccent(questionnaire.name).indexOf(
                            keyword
                        ) >= 0
                )
                .map((result) => ({ ...result, type: "questionnaire" }));
        },

        /**
         *
         */
        filteredRoles() {
            const keyword = removeStringAccent(this.searchKeyword);

            return this.roles
                .filter(
                    (role) =>
                        removeStringAccent(role.name).indexOf(keyword) >= 0
                )
                .map((result) => ({ ...result, type: "role" }));
        },

        /**
         *
         */
        filteredThreads() {
            const keyword = removeStringAccent(this.searchKeyword);

            return this.threads
                .filter(
                    (thread) =>
                        removeStringAccent(thread.name).indexOf(keyword) >= 0
                )
                .map((result) => ({ ...result, type: "thread" }));
        },

        /**
         *
         */
        filteredAssignableUsers() {
            const keyword = removeStringAccent(this.searchKeyword);

            return this.users
                .filter(
                    (user) =>
                        removeStringAccent(user.name).indexOf(keyword) >= 0
                )
                .map((result) => ({ ...result, type: "user" }));
        },

        /**
         *
         */
        filteredSearchResults() {
            return [
                ...this.filteredCalendars,
                ...this.filteredCategories,
                ...this.filteredDocuments,
                ...this.filteredFolders,
                ...this.filteredGroups,
                ...this.filteredMenus,
                ...this.filteredOrderActions,
                ...this.filteredPermissions,
                ...this.filteredQuestionnaires,
                ...this.filteredRoles,
                ...this.filteredThreads,
                ...this.filteredAssignableUsers,
            ];
        },
    },
};
</script>
