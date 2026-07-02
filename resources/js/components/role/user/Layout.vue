<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="userKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label">
                <icon class="fa fa-check-square" />
                <div class="hc-item-main-content" v-text="$t('all')"></div>
                <checkbox v-model="all" @change="change" :disabled="canAll" />
            </item>
            <user-row
                v-for="user in filteredUsers"
                :key="user.id"
                :user="user"
                :value="isUserChecked(user)"
                :disabled="canAll"
            />
        </item-list>
        <buttons>
            <a @click.prevent="addUser" v-text="$t('add')"></a>
        </buttons>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    FETCH_ROLE_USERS,
    BULK_ADD_ROLE_USER,
    BULK_REMOVE_ROLE_USER,
    ADD_ROLE_USER,
    REMOVE_ROLE_USER,
} from "@/actions/project/role/user";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import UserRow from "./UserRow.vue";

export default {
    components: {
        UserRow,
    },

    data() {
        return {
            name: "role-manage-users",
            tab: 0,
            userKeyword: "",
        };
    },

    methods: {
        /**
         *
         * @param {*} user
         */
        isUserChecked(user) {
            return (
                this.canAll ||
                this.roleUsers.findIndex((l) => l.id == user.id) >= 0
            );
        },

        /**
         *
         */
        fetchUserUsers() {
            if (this.user) {
                store.dispatch(FETCH_ROLE_USERS, this.user);
            }
        },

        /**
         *
         */
        addUser() {
            store.commit(OPEN_MODAL, "user-add");
        },
    },

    computed: {
        ...mapGetters([
            "users",
            "role",
            "roleUsers",
            "rolePermissions",
            "roleFullName",
        ]),

        /**
         *
         */
        canAll() {
            return this.rolePermissions.includes("all");
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
        all: {
            get: function () {
                return (
                    this.canAll || this.roleUsers.length == this.users.length
                );
            },
            set: async function (value) {
                if (value) {
                    this.users.forEach((f) => {
                        store.commit(ADD_ROLE_USER, f);
                    });
                    store.dispatch(BULK_ADD_ROLE_USER, {
                        roles: [this.role.id],
                        users: this.users.map((f) => f.id),
                    });
                } else {
                    this.users.forEach((f) => {
                        store.commit(REMOVE_ROLE_USER, f);
                    });
                    store.dispatch(BULK_REMOVE_ROLE_USER, {
                        roles: [this.role.id],
                        users: this.users.map((f) => f.id),
                    });
                }
            },
        },
    },
};
</script>
