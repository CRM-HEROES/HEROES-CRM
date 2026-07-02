<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="userKeyword" />
        <item-list
            class="hc-flex-1"
            padding="12px"
            style="position: relative"
        >
            <user-row
                v-for="user in filteredUsers"
                :key="user.id"
                :user="user"
                v-model="filterUsers"
            />
            <loading :loading="fetchingUsers" />
        </item-list>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import {
    ADD_PROSPECT_PARAMS,
    REMOVE_PROSPECT_PARAMS,
    FETCH_PROSPECTS,
} from "@/actions/project/prospect";

// Components
import UserRow from "./UserRow.vue";

export default {
    components: {
        UserRow,
    },

    data() {
        return {
            userKeyword: "",
            exclude: false,
            fetchingUsers: false,
        };
    },

    watch: {
        exclude(value) {
            const prospectsParamValue = this.prospectsParamValue(
                value ? this.withKeyEvents : this.withoutKeyEvents
            );

            this.eventParams = prospectsParamValue
                ? prospectsParamValue
                : {};
        },
    },

    computed: {
        ...mapGetters([
            "users",
            "prospectsParamValue",
            "prospectsParamExists",
        ]),

        /**
         *
         */
        withKeyEvents() {
            return "withThreads";
        },

        /**
         *
         */
        withoutKeyEvents() {
            return "withoutThreads";
        },

        /**
         *
         */
        filterKey() {
            return this.exclude ? this.withoutKeyEvents : this.withKeyEvents;
        },

        /**
         * Extract event params
         * from prospects query params
         *
         * Event params format
         * "calendars:id1,id2,...;users:id1,id2,...;confirmed:1;done:0;...""
         */
        eventParams: {
            get() {
                // Check if event params exists
                if (!this.prospectsParamExists(this.filterKey)) {
                    return null;
                }

                // Extract event params
                // from string to object
                // {
                //    calendars: "id1,id2,...",
                //    users: "id1,id2,...",
                //    confirmed: "1",
                //    done: "0",
                // }
                return this.prospectsParamValue(this.filterKey);
            },
            set(value) {
                // Remove event params to
                // prospect params
                store.commit(REMOVE_PROSPECT_PARAMS, {
                    key: this.withKeyEvents,
                });
                store.commit(REMOVE_PROSPECT_PARAMS, {
                    key: this.withoutKeyEvents,
                });

                if (value !== null) {
                    // Add event params to
                    // prospect params
                    store.commit(ADD_PROSPECT_PARAMS, {
                        key: this.filterKey,
                        value: value,
                    });
                }

                store.dispatch(FETCH_PROSPECTS);
            },
        },

        eventParamExists: {
            get() {
                return this.prospectsParamExists(this.filterKey);
            },
            set(value) {
                this.eventParams = value ? {} : null;
            },
        },


        /**
         *
         */
        filterUsers: {
            get() {
                const eventParams = this.eventParams;

                if (
                    !eventParams ||
                    !eventParams.withUsers ||
                    !eventParams.withUsers.ids
                ) {
                    return [];
                }

                return eventParams.withUsers.ids;
            },
            set(value) {
                const eventParams = this.eventParams || {};
                if (value.length > 0) {
                    if (!eventParams.withUsers) {
                        eventParams.withUsers = {};
                    }

                    eventParams.withUsers.ids = value;
                } else if (eventParams.withUsers && eventParams.withUsers.ids) {
                    delete eventParams.withUsers.ids;

                    if (Object.keys(eventParams.withUsers).length == 0) {
                        delete eventParams.withUsers;
                    }
                }
                this.eventParams =
                    Object.keys(eventParams).length == 0 ? null : eventParams;
            },
        },

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
