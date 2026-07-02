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
            <attendee-row
                v-for="user in filteredUsers"
                :key="user.id"
                :user="user"
                :value="attendees.findIndex((a) => a.id == user.id) >= 0"
                @add="addAttendee"
                @remove="removeAttendee"
            />
            <loading :loading="fetchingUsers" />
        </item-list>
    </div>
</template>

<script>
import { mapGetters } from "vuex";

// Components
import AttendeeRow from "./AttendeeRow.vue";

export default {
    components: {
        AttendeeRow,
    },

    props: {
        prospectEvent: {},
    },

    data() {
        return {
            userKeyword: "",
        };
    },

    methods: {
        addAttendee(user) {
            this.$emit("add", user);
        },

        removeAttendee(user) {
            this.$emit("remove", user);
        },
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
                        user.id != this.prospectEvent.creator_id &&
                        user.id !=
                            (this.prospectEvent.user
                                ? this.prospectEvent.user.id
                                : this.prospectEvent.user_id) &&
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

        attendees() {
            return this.prospectEvent.users ? this.prospectEvent.users : [];
        },
    },
};
</script>
