<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="userKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label">
                <icon class="fa fa-check-square" />
                <div class="hc-item-main-content" v-text="$t('all')"></div>
                <checkbox v-model="all" />
            </item>
            <user-row
                v-for="user in filteredUsers"
                :key="user.id"
                :user="user"
                :value="isUserChecked(user)"
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
    BULK_ADD_USER_USER,
    BULK_REMOVE_USER_USER,
    ADD_USER_USER,
    REMOVE_USER_USER,
} from "@/actions/project/user/user";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import UserRow from "./UserRow.vue";

export default {
    components: {
        UserRow,
    },

    data() {
        return {
            name: "user-manage-users",
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
                // this.can("all") ||
                this.userUsers.findIndex((l) => l.id == user.id) >= 0
            );
        },

        /**
         *
         */
        addUser() {
            store.commit(OPEN_MODAL, "user-add");
        },
    },

    computed: {
        ...mapGetters(["users", "user", "userUsers", "can"]),

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
                    // this.can("all") ||
                    this.userUsers.length == this.users.length
                );
            },
            set: async function (value) {
                if (value) {
                    this.users.forEach((f) => {
                        store.commit(ADD_USER_USER, f);
                    });
                    store.dispatch(BULK_ADD_USER_USER, {
                        users: [this.user.id],
                        assignableUsers: this.users.map((f) => f.id),
                    });
                } else {
                    this.users.forEach((f) => {
                        store.commit(REMOVE_USER_USER, f);
                    });
                    store.dispatch(BULK_REMOVE_USER_USER, {
                        users: [this.user.id],
                        assignableUsers: this.users.map((f) => f.id),
                    });
                }
            },
        },
    },
};
</script>
