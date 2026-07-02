<template>
    <div>
        <item @click="$emit('back')" style="border-bottom: 1px solid #eee">
            <icon class="fa fa-caret-left" />
            <div
                class="hc-item-main-content hc-flex-column"
                v-text="$t('event.choose_user')"
            ></div>
        </item>
        <search v-model="userKeyword" />
        <item-list class="hc-flex-1" padding="12px" style="position: relative">
            <user-row
                v-for="user in filteredUsers"
                :key="user.id"
                :user="user"
                @click="$emit('user-selected', user)"
            />
            <loading :loading="fetchingUsers" />
        </item-list>
    </div>
</template>

<script>
import { mapGetters } from "vuex";

// Components
import UserRow from "./UserRow.vue";

export default {
    components: {
        UserRow,
    },

    data() {
        return {
            userKeyword: "",
        };
    },

    computed: {
        ...mapGetters(["users"]),

        /**
         *
         */
        filteredUsers() {
            const keyword = removeStringAccent(this.userKeyword);

            return this.users
                .filter(
                    (user) =>
                        removeStringAccent(user.name).indexOf(keyword) >= 0
                )
                .sort((user1, user2) =>
                    user1.pivot &&
                    user2.pivot &&
                    user1.pivot.relevance_event > user2.pivot.relevance_event
                        ? -1
                        : 1
                );
        },
    },
};
</script>
