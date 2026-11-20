<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="roleKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <selection-list
                v-model="all"
                :items="filteredRoles"
                :selected="userRoles"
                :render-when-empty="true"
            >
                <template #default="{ sortedItems }">
                    <role-row
                        v-for="role in sortedItems"
                        :key="role.id"
                        :role="role"
                        :value="isRoleChecked(role)"
                    />
                </template>
            </selection-list>
        </item-list>
        <buttons v-if="can('all.project.role.add')">
            <a @click.prevent="addRole" v-text="$t('add')"></a>
        </buttons>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    BULK_ADD_USER_ROLE,
    BULK_REMOVE_USER_ROLE,
    ADD_USER_ROLE,
    REMOVE_USER_ROLE,
} from "@/actions/project/user/role";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import RoleRow from "./RoleRow.vue";

export default {
    components: {
        RoleRow,
    },

    data() {
        return {
            name: "user-manage-roles",
            roleKeyword: "",
        };
    },

    methods: {
        /**
         *
         * @param {*} role
         */
        isRoleChecked(role) {
            return (
                // this.can("all") ||
                this.userRoles.findIndex((l) => l.id == role.id) >= 0
            );
        },

        /**
         *
         */
        addRole() {
            store.commit(OPEN_MODAL, "role-add");
        },
    },

    computed: {
        ...mapGetters(["roles", "user", "userRoles", "can"]),

        /**
         *
         */
        filteredRoles() {
            const keyword = removeStringAccent(this.roleKeyword);

            return this.roles.filter(
                (role) => removeStringAccent(role.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        all: {
            get: function () {
                return (
                    // this.can("all") ||
                    this.userRoles.length == this.roles.length
                );
            },
            set: async function (value) {
                if (value) {
                    this.roles.forEach((f) => {
                        store.commit(ADD_USER_ROLE, f);
                    });
                    store.dispatch(BULK_ADD_USER_ROLE, {
                        roles: [this.role.id],
                        roles: this.roles.map((f) => f.id),
                    });
                } else {
                    this.roles.forEach((f) => {
                        store.commit(REMOVE_USER_ROLE, f);
                    });
                    store.dispatch(BULK_REMOVE_USER_ROLE, {
                        roles: [this.role.id],
                        roles: this.roles.map((f) => f.id),
                    });
                }
            },
        },
    },
};
</script>
