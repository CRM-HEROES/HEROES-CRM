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
                v-for="assignableUser in filteredUsers"
                :key="assignableUser.id"
                :user="assignableUser"
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
            <a @click.prevent="addUser" v-text="$t('add')"></a>
        </buttons>
        <loading :loading="bulking" />
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    BULK_ADD_USER_USER,
    BULK_REMOVE_USER_USER,
} from "@/actions/project/user/user";
import {
    FETCH_USERS,
    UPDATE_SELECTED_USERS,
    UPDATE_USER_BULK_USERS,
} from "@/actions/project/user";
import { OPEN_MODAL } from "@/actions/modal";
import { CLOSE_SLIDE } from "@/actions/slide";

// Components
import UserRow from "./UserRow.vue";

export default {
    components: {
        UserRow,
    },

    data() {
        return {
            bulking: false,
            userKeyword: "",
        };
    },

    methods: {
        /**
         *
         */
        addUser() {
            store.commit(OPEN_MODAL, "user-add");
        },

        /**
         *
         */
        async attach() {
            this.bulking = true;
            const usersSelected = this.usersSelected;
            store.commit(UPDATE_SELECTED_USERS, []);

            try {
                await store.dispatch(BULK_ADD_USER_USER, {
                    users: usersSelected,
                    users: this.userBulkUsers,
                });
                store.dispatch(FETCH_USERS);
            } finally {
                this.bulking = false;
                store.commit(UPDATE_USER_BULK_USERS, []);
                store.commit(CLOSE_SLIDE, "user-bulk-manage-users");
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
                await store.dispatch(BULK_REMOVE_USER_USER, {
                    users: usersSelected,
                    users: this.userBulkUsers,
                });
                store.dispatch(FETCH_USERS);
            } finally {
                this.bulking = false;
                store.commit(UPDATE_USER_BULK_USERS, []);
                store.commit(CLOSE_SLIDE, "user-bulk-manage-users");
            }
        },
    },

    computed: {
        ...mapGetters(["users", "usersSelected", "userBulkUsers"]),

        /**
         *
         */
        disabled() {
            return this.userBulkUsers.length == 0;
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
                for (let i in this.filteredUsers) {
                    if (
                        !this.userBulkUsers.find(
                            (user) => user == this.filteredUsers[i].id
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
                        UPDATE_USER_BULK_USERS,
                        this.filteredUsers.map((user) => user.id)
                    );
                } else {
                    store.commit(UPDATE_USER_BULK_USERS, []);
                }
            },
        },
    },
};
</script>
