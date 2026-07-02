<template>
    <slide
        name="user-bulk-manage-roles"
        :title="$t('user.table.bulk.roles.title')"
        icon="fa fa-tags"
        style="width: 260px"
        @open="fetchRoles"
    >
        <div class="hc-flex-column" style="height: 100%">
            <search v-model="roleKeyword" />
            <item-list class="hc-flex-1" padding="5px">
                <item tag="label">
                    <icon class="fa fa-check-square" />
                    <div class="hc-item-main-content" v-text="$t('all')"></div>
                    <checkbox v-model="all" />
                </item>
                <role-row
                    v-for="role in filteredRoles"
                    :key="role.id"
                    :role="role"
                />
            </item-list>
            <buttons>
                <button @click.prevent="attach" :disabled="disabled">
                    Assigner
                </button>
                <button
                    @click.prevent="detach"
                    :disabled="disabled"
                    class="hc-button-danger"
                >
                    Retirer
                </button>
                <a
                    v-if="can('all.project.role.add')"
                    @click.prevent="addRole"
                    v-text="$t('add')"
                ></a>
            </buttons>
            <loading :loading="bulking" />
        </div>
    </slide>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    BULK_ADD_USER_ROLE,
    BULK_REMOVE_USER_ROLE,
} from "@/actions/project/user/role";
import {
    FETCH_USERS,
    UPDATE_SELECTED_USERS,
    UPDATE_USER_BULK_ROLES,
} from "@/actions/project/user";
import { FETCH_ROLES } from "@/actions/project/role";
import { OPEN_MODAL } from "@/actions/modal";
import { CLOSE_SLIDE } from "@/actions/slide";

// Components
import RoleRow from "./RoleRow.vue";

export default {
    components: {
        RoleRow,
    },

    data() {
        return {
            bulking: false,
            roleKeyword: "",
        };
    },

    methods: {
        /**
         *
         * @param {*} role
         */
        fetchRoles() {
            store.dispatch(FETCH_ROLES);
        },

        /**
         *
         */
        addRole() {
            store.commit(OPEN_MODAL, "role-add");
        },

        /**
         *
         */
        async attach() {
            this.bulking = true;
            const usersSelected = this.usersSelected;
            store.commit(UPDATE_SELECTED_USERS, []);

            try {
                await store.dispatch(BULK_ADD_USER_ROLE, {
                    users: usersSelected,
                    roles: this.userBulkRoles,
                });
                store.dispatch(FETCH_USERS);
            } finally {
                this.bulking = false;
                store.commit(UPDATE_USER_BULK_ROLES, []);
                store.commit(CLOSE_SLIDE, "user-bulk-manage-roles");
            }
        },

        /**
         *
         */
        async detach() {
            this.bulking = true;
            const usersSelected = this.usersSelected;
            store.commit(UPDATE_SELECTED_USERS, []);

            try {
                await store.dispatch(BULK_REMOVE_USER_ROLE, {
                    users: usersSelected,
                    roles: this.userBulkRoles,
                });
                store.dispatch(FETCH_USERS);
            } finally {
                this.bulking = false;
                store.commit(UPDATE_USER_BULK_ROLES, []);
                store.commit(CLOSE_SLIDE, "user-bulk-manage-roles");
            }
        },
    },

    computed: {
        ...mapGetters(["roles", "usersSelected", "userBulkRoles", "can"]),

        /**
         *
         */
        disabled() {
            return this.userBulkRoles.length == 0;
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

        /**
         *
         */
        all: {
            get: function () {
                for (let i in this.filteredRoles) {
                    if (
                        !this.userBulkRoles.find(
                            (role) => role == this.filteredRoles[i].id
                        )
                    ) {
                        return false;
                    }
                }

                return true;
            },
            set: async function (value) {
                if (value) {
                    store.commit(
                        UPDATE_USER_BULK_ROLES,
                        this.filteredRoles.map((role) => role.id)
                    );
                } else {
                    store.commit(UPDATE_USER_BULK_ROLES, []);
                }
            },
        },
    },
};
</script>
