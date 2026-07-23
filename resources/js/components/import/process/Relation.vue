<template>
    <div class="hc-flex-column" style="height: 100%">
        <div class="hc-flex-column hc-flex-1" style="overflow: hidden">
            <search v-model="relationKeyword" />
            <tab-layout
                :count="2"
                :tab="tab"
                class="hc-flex-1"
                id="hc-import-process-relations"
            >
                <template #1>
                    <div class="hc-flex-column" style="height: 100%">
                        <item-list class="hc-flex-1" padding="5px">
                            <!-- Users -->
                            <item
                                @click="(tab = 1), (itemsTab = 0)"
                                v-if="filteredRelationUsers.length > 0"
                            >
                                <icon class="fa fa-user" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        $t(
                                            'import.process.tab.relations.affected_users'
                                        )
                                    "
                                ></div>
                                <icon class="fa fa-caret-right" />
                            </item>

                            <!-- Groups -->
                            <item
                                @click="(tab = 1), (itemsTab = 1)"
                                v-if="filteredRelationGroups.length > 0"
                            >
                                <icon class="fa fa-users" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        $t(
                                            'import.process.tab.relations.affected_groups'
                                        )
                                    "
                                ></div>
                                <icon class="fa fa-caret-right" />
                            </item>

                            <!-- Roles -->
                            <item
                                @click="(tab = 1), (itemsTab = 3)"
                                v-if="filteredRelationRoles.length > 0"
                            >
                                <icon class="fa fa-id-badge" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="
                                        $t(
                                            'Rôles effectués'
                                        )
                                    "
                                ></div>
                                <icon class="fa fa-caret-right" />
                            </item>

                            <!-- Categories -->
                            <relation-category-row
                                v-for="c in filteredRelationCategories"
                                :key="c.id"
                                :category="c"
                                @click="
                                    (tab = 1),
                                        (itemsTab = 2),
                                        (relationCategory = c)
                                "
                            />
                        </item-list>
                    </div>
                </template>

                <template #2>
                    <div class="hc-flex-column" style="height: 100%">
                        <frame-layout
                            :count="4"
                            :tab="itemsTab"
                            class="hc-flex-1 hc-flex-column"
                            style="height: 100%"
                        >
                            <!-- Users -->
                            <template #1>
                                <!-- Title -->
                                <item @click="tab = 0" class="bordered">
                                    <icon class="fa fa-caret-left" />
                                    <div
                                        class="hc-item-main-content"
                                        v-text="
                                            $t(
                                                'import.process.tab.relations.affected_users'
                                            )
                                        "
                                    ></div>
                                </item>

                                <item-list padding="12px" class="hc-flex-1">
                                    <relation-user-row
                                        v-for="user in filteredRelationUsers"
                                        :key="user.id"
                                        :user="user"
                                        :is-checked="
                                            isRelationUserChecked(user)
                                        "
                                    />
                                </item-list>
                            </template>

                            <!-- Groups -->
                            <template #2>
                                <!-- Title -->
                                <item @click="tab = 0" class="bordered">
                                    <icon class="fa fa-caret-left" />
                                    <div
                                        class="hc-item-main-content"
                                        v-text="
                                            $t(
                                                'import.process.tab.relations.affected_groups'
                                            )
                                        "
                                    ></div>
                                </item>

                                <item-list padding="12px" class="hc-flex-1">
                                    <relation-group-row
                                        v-for="group in filteredRelationGroups"
                                        :key="group.id"
                                        :group="group"
                                        :is-checked="
                                            isRelationGroupChecked(group)
                                        "
                                    />
                                </item-list>
                            </template>

                            <!-- Labels -->
                            <template #3 v-if="relationCategory">
                                <!-- Title -->
                                <item @click="tab = 0" class="bordered">
                                    <icon class="fa fa-caret-left" />
                                    <div
                                        class="hc-item-main-content"
                                        v-text="relationCategory.name"
                                    ></div>
                                </item>

                                <item-list class="hc-flex-1" padding="12px">
                                    <relation-label-row
                                        v-for="label in filteredRelationLabels"
                                        :key="label.id"
                                        :label="label"
                                        :is-checked="
                                            isRelationLabelChecked(label)
                                        "
                                    />
                                </item-list>
                            </template>

                            <!-- Roles -->
                            <template #4>
                                <!-- Title -->
                                <item @click="tab = 0" class="bordered">
                                    <icon class="fa fa-caret-left" />
                                    <div
                                        class="hc-item-main-content"
                                        v-text="
                                            $t(
                                                'Rôles effectués'
                                            )
                                        "
                                    ></div>
                                </item>

                                <item-list padding="12px" class="hc-flex-1">
                                    <relation-role-row
                                        v-for="role in filteredRelationRoles"
                                        :key="role.id"
                                        :role="role"
                                        :is-checked="
                                            isRelationRoleChecked(role)
                                        "
                                    />
                                </item-list>
                            </template>
                        </frame-layout>
                    </div>
                </template>
            </tab-layout>
        </div>
    </div>
</template>

<script>
import { mapGetters } from "vuex";

import RelationCategoryRow from "./relation/RelationCategoryRow.vue";
import RelationLabelRow from "./relation/RelationLabelRow.vue";
import RelationGroupRow from "./relation/RelationGroupRow.vue";
import RelationUserRow from "./relation/RelationUserRow.vue";
import RelationRoleRow from "./relation/RelationRoleRow.vue";

export default {
    components: {
        RelationCategoryRow,

        RelationLabelRow,
        RelationGroupRow,
        RelationUserRow,
        RelationRoleRow,
    },

    data() {
        return {
            tab: 0,
            itemsTab: 0,
            relationCategory: null,
            relationKeyword: "",
        };
    },

    methods: {
        /**
         * Group checked
         * @param {*} group
         */
        isRelationGroupChecked(group) {
            return (
                this.prospectImport &&
                this.prospectImport.groups &&
                this.prospectImport.groups.indexOf(group.id) >= 0
            );
        },

        /**
         * Label checked
         * @param {*} label
         */
        isRelationLabelChecked(label) {
            return (
                this.prospectImport &&
                this.prospectImport.labels &&
                this.prospectImport.labels.indexOf(label.id) >= 0
            );
        },

        /**
         * User checked
         * @param {*} user
         */
        isRelationUserChecked(user) {
            return (
                this.prospectImport &&
                this.prospectImport.users &&
                this.prospectImport.users.indexOf(user.id) >= 0
            );
        },

        /**
         * Role checked
         * @param {*} role
         */
        isRelationRoleChecked(role) {
            return (
                this.prospectImport &&
                this.prospectImport.roles &&
                this.prospectImport.roles.indexOf(role.id) >= 0
            );
        },
    },

    computed: {
        ...mapGetters(["categories", "groups", "users", "roles", "prospectImport"]),

        /**
         *
         */
        filteredRelationCategories() {
            const keyword = removeStringAccent(this.relationKeyword);

            return this.categories.filter(
                (category) =>
                    (removeStringAccent(category.name).indexOf(keyword) >= 0 ||
                        (category.labels &&
                            category.labels.filter(
                                (label) =>
                                    removeStringAccent(label.name).indexOf(
                                        keyword
                                    ) >= 0
                            ).length > 0)) &&
                    (!Array.isArray(this.prospectImport.mapping) ||
                        !this.prospectImport.mapping.find(
                            (m) => m == "category->" + category.id
                        ))
            );
        },

        /**
         *
         */
        filteredRelationLabels() {
            if (!this.relationCategory.labels) {
                return [];
            }

            const keyword = removeStringAccent(this.relationKeyword);

            return this.relationCategory.labels.filter(
                (label) => removeStringAccent(label.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredRelationGroups() {
            const keyword = removeStringAccent(this.relationKeyword);

            return this.groups.filter(
                (group) => removeStringAccent(group.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredRelationUsers() {
            const keyword = removeStringAccent(this.relationKeyword);

            return this.users
                .filter(
                    (user) =>
                        removeStringAccent(user.name).indexOf(keyword) >= 0
                )
                .sort((user1, user2) =>
                    user1.pivot &&
                    user2.pivot &&
                    user1.pivot.relevance_prospect >
                        user2.pivot.relevance_prospect
                        ? -1
                        : 1
                );
        },

        /**
         *
         */
        filteredRelationRoles() {
            const keyword = removeStringAccent(this.relationKeyword);

            return (this.roles || []).filter(
                (role) => removeStringAccent(role.name).indexOf(keyword) >= 0
            );
        },
    },
};
</script>
