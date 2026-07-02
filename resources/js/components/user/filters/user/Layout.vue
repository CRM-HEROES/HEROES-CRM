<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="userKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label" class="hc-users-table-filter-user">
                <icon class="fa fa-tag" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('user.table.filters.users.with')"
                ></div>
                <icon
                    tag="a"
                    class="fa fa-thumbs-down"
                    :style="excludeUserStyle"
                    :title="$t('user.table.filters.users.without')"
                    @click.prevent="toggleExcludeUser"
                />
                <checkbox :model-value="isCheckedUser" @change="changeUser" />
            </item>
            <user-row
                v-for="user in filteredUsers"
                :key="user.id"
                :user="user"
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
    REMOVE_USER_PARAMS,
    ADD_USER_PARAMS,
    FETCH_USERS,
} from "@/actions/project/user";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import UserRow from "./UserRow.vue";

export default {
    components: {
        UserRow,
    },

    data() {
        return {
            tab: 0,
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

        changeUser(event) {
            // Remove without param
            store.commit(REMOVE_USER_PARAMS, {
                key: this.withoutKeyUser,
            });

            // Add or remove with param
            store.commit(
                event.target.checked ? ADD_USER_PARAMS : REMOVE_USER_PARAMS,
                {
                    key: this.withKeyUser,
                }
            );
            store.dispatch(FETCH_USERS);
        },

        toggleExcludeUser() {
            // Add or remove with param
            store.commit(
                this.isExcludedUser ? ADD_USER_PARAMS : REMOVE_USER_PARAMS,
                {
                    key: this.withKeyUser,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcludedUser ? REMOVE_USER_PARAMS : ADD_USER_PARAMS,
                {
                    key: this.withoutKeyUser,
                }
            );

            store.dispatch(FETCH_USERS);
        },
    },

    computed: {
        ...mapGetters(["users", "user", "userFullName", "usersParamValue"]),

        /**
         *
         */
        withKeyUser() {
            return "withUsers";
        },

        /**
         *
         */
        withoutKeyUser() {
            return "withoutUsers";
        },

        /**
         *
         */
        isCheckedUser() {
            return (
                this.usersParamValue(this.withKeyUser) === "" ||
                this.usersParamValue(this.withoutKeyUser) === ""
            );
        },

        /**
         *
         */
        isExcludedUser() {
            return this.usersParamValue(this.withoutKeyUser) === "";
        },

        /**
         *
         */
        excludeUserStyle() {
            return {
                color: this.isExcludedUser ? "#CC0000" : "#CCCCCC",
            };
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
    },
};
</script>
