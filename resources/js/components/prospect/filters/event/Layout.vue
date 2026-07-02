<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <item-list padding="12px" style="height: 100%; overflow: auto">
                <!-- With events -->
                <item tag="label">
                    <icon class="fa fa-filter" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('prospect.table.filters.appointments.with')"
                    ></div>
                    <!-- Without events -->
                    <icon
                        class="fa fa-thumbs-down"
                        :style="{ color: exclude ? '#DD0000' : '#CCCCCC' }"
                        :title="
                            $t('prospect.table.filters.appointments.without')
                        "
                        @click.prevent.stop="exclude = !exclude"
                    />
                    <checkbox
                        v-model="eventParamExists"
                        style="margin-right: 5px"
                    />
                </item>

                <!-- Filter by calendars -->
                <item
                    class="hc-prospect-filter-event-item"
                    @click="(tab = 1), (frameTab = 0)"
                >
                    <icon class="fa fa-calendar" />
                    <div class="hc-item-main-content hc-flex-column">
                        <span
                            class="hc-prospect-filter-event-item-title"
                            v-text="
                                $t(
                                    'prospect.table.filters.appointments.calendars'
                                )
                            "
                        ></span>
                        <span
                            class="hc-prospect-filter-event-item-value"
                            v-text="
                                filterCalendarsName
                                    ? filterCalendarsName
                                    : $t(
                                          'prospect.table.filters.appointments.no_selected_calendar'
                                      )
                            "
                        ></span>
                    </div>
                    <icon class="fa fa-caret-right" />
                </item>

                <!-- Filter by affected users -->
                <item
                    class="hc-prospect-filter-event-item"
                    @click="(tab = 1), (frameTab = 1)"
                >
                    <icon class="fa fa-user" />
                    <div class="hc-item-main-content hc-flex-column">
                        <span
                            class="hc-prospect-filter-event-item-title"
                            v-text="
                                $t(
                                    'prospect.table.filters.appointments.affected_to'
                                )
                            "
                        ></span>
                        <span
                            class="hc-prospect-filter-event-item-value"
                            v-text="
                                filterUsersName
                                    ? filterUsersName
                                    : $t(
                                          'prospect.table.filters.appointments.no_selected_affected_to'
                                      )
                            "
                        ></span>
                    </div>
                    <icon class="fa fa-caret-right" />
                </item>

                <!-- Filter by creators -->
                <item
                    class="hc-prospect-filter-event-item"
                    @click="(tab = 1), (frameTab = 2)"
                >
                    <icon class="fa fa-user" />
                    <div class="hc-item-main-content hc-flex-column">
                        <span
                            class="hc-prospect-filter-event-item-title"
                            v-text="
                                $t(
                                    'prospect.table.filters.appointments.created_by'
                                )
                            "
                        ></span>
                        <span
                            class="hc-prospect-filter-event-item-value"
                            v-text="
                                filterCreatorsName
                                    ? filterCreatorsName
                                    : $t(
                                          'prospect.table.filters.appointments.no_selected_created_by'
                                      )
                            "
                        ></span>
                    </div>
                    <icon class="fa fa-caret-right" />
                </item>

                <!-- After date -->
                <item tag="label">
                    <icon class="fa fa-clock" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('prospect.table.filters.appointments.after')"
                    ></div>
                    <input
                        type="date"
                        v-model="filterStartedAt"
                        class="hc-prospect-filter-event-item-date"
                    />
                    <icon
                        v-if="filterStartedAt"
                        tag="a"
                        class="fa fa-times"
                        @click.prevent.stop="filterStartedAt = null"
                    />
                    <icon v-else class="fa fa-plus" />
                </item>

                <!-- Before date -->
                <item tag="label">
                    <icon class="fa fa-clock" />
                    <div
                        class="hc-item-main-content"
                        v-text="
                            $t('prospect.table.filters.appointments.before')
                        "
                    ></div>
                    <input
                        type="date"
                        v-model="filterEndedAt"
                        class="hc-prospect-filter-event-item-date"
                    />
                    <icon
                        v-if="filterEndedAt"
                        tag="a"
                        class="fa fa-times"
                        @click.prevent.stop="filterEndedAt = null"
                    />
                    <icon v-else class="fa fa-plus" />
                </item>

                <!-- Coming -->
                <item tag="label">
                    <icon class="fa fa-step-forward" />
                    <div
                        class="hc-item-main-content"
                        v-text="
                            $t('prospect.table.filters.appointments.future')
                        "
                    ></div>
                    <checkbox
                        v-model="filterComing"
                        style="margin-right: 5px"
                    />
                </item>

                <!-- Passed -->
                <item tag="label">
                    <icon class="fa fa-step-backward" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('prospect.table.filters.appointments.old')"
                    ></div>
                    <checkbox
                        v-model="filterNotComing"
                        style="margin-right: 5px"
                    />
                </item>

                <!-- Confirmed -->
                <item tag="label">
                    <icon class="fa fa-thumbs-up" />
                    <div
                        class="hc-item-main-content"
                        v-text="
                            $t('prospect.table.filters.appointments.confirmed')
                        "
                    ></div>
                    <checkbox
                        v-model="filterConfirmed"
                        style="margin-right: 5px"
                    />
                </item>

                <!-- Not confirmed -->
                <item tag="label">
                    <icon class="fa fa-thumbs-down" />
                    <div
                        class="hc-item-main-content"
                        v-text="
                            $t(
                                'prospect.table.filters.appointments.not_confirmed'
                            )
                        "
                    ></div>
                    <checkbox
                        v-model="filterNotConfirmed"
                        style="margin-right: 5px"
                    />
                </item>

                <!-- Done -->
                <item tag="label">
                    <icon class="fa fa-check" />
                    <div
                        class="hc-item-main-content"
                        v-text="$t('prospect.table.filters.appointments.done')"
                    ></div>
                    <checkbox v-model="filterDone" style="margin-right: 5px" />
                </item>

                <!-- Not done -->
                <item tag="label">
                    <icon class="fa fa-spinner" />
                    <div
                        class="hc-item-main-content"
                        v-text="
                            $t('prospect.table.filters.appointments.not_done')
                        "
                    ></div>
                    <checkbox
                        v-model="filterNotDone"
                        style="margin-right: 5px"
                    />
                </item>
            </item-list>
        </template>

        <template #2>
            <frame-layout :count="3" :tab="frameTab" class="hc-flex-1">
                <template #1>
                    <div
                        class="hc-flex-column"
                        style="height: 100%; position: relative"
                    >
                        <item @click="tab = 0" class="bordered">
                            <icon class="fa fa-caret-left" />
                            <div
                                class="hc-item-main-content hc-flex-column"
                                v-text="
                                    $t(
                                        'prospect.table.filters.appointments.select_calendars'
                                    )
                                "
                            ></div>
                        </item>
                        <search v-model="calendarKeyword" />
                        <item-list
                            class="hc-flex-1"
                            padding="12px"
                            style="position: relative"
                        >
                            <calendar-row
                                v-for="calendar in filteredCalendars"
                                :key="calendar.id"
                                :calendar="calendar"
                                v-model="filterCalendars"
                            />
                            <loading :loading="fetchingCalendars" />
                        </item-list>
                    </div>
                </template>

                <template #2>
                    <div
                        class="hc-flex-column"
                        style="height: 100%; position: relative"
                    >
                        <item
                            @click="tab = 0"
                            style="border-bottom: 1px solid #eee"
                        >
                            <icon class="fa fa-caret-left" />
                            <div
                                class="hc-item-main-content hc-flex-column"
                                v-text="
                                    $t(
                                        'prospect.table.filters.appointments.affected_to'
                                    )
                                "
                            ></div>
                        </item>
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

                <template #3>
                    <div
                        class="hc-flex-column"
                        style="height: 100%; position: relative"
                    >
                        <item
                            @click="tab = 0"
                            style="border-bottom: 1px solid #eee"
                        >
                            <icon class="fa fa-caret-left" />
                            <div
                                class="hc-item-main-content hc-flex-column"
                                v-text="
                                    $t(
                                        'prospect.table.filters.appointments.created_by'
                                    )
                                "
                            ></div>
                        </item>
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
                                v-model="filterCreators"
                            />
                            <loading :loading="fetchingUsers" />
                        </item-list>
                    </div>
                </template>
            </frame-layout>
        </template>
    </tab-layout>
</template>

<style>
.hc-prospect-filter-event-item {
    padding: 3px 0 !important;
    align-items: start;
}

.hc-prospect-filter-event-item-title {
    color: #000000;
}
.hc-prospect-filter-event-item-value {
    color: #000000;
    font-size: 11px;
    opacity: 0.7;
}

.hc-prospect-filter-event-item-date {
    width: 90px;
    border: 1px solid #ccc;
    border-radius: 3px;
    padding: 0 0 0 5px;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { FETCH_CALENDARS } from "@/actions/project/calendar";
import { FETCH_USERS } from "@/actions/project/user";
import {
    ADD_PROSPECT_PARAMS,
    REMOVE_PROSPECT_PARAMS,
    FETCH_PROSPECTS,
} from "@/actions/project/prospect";

// Components
import CalendarRow from "./CalendarRow.vue";
import UserRow from "./UserRow.vue";

export default {
    components: {
        CalendarRow,
        UserRow,
    },

    data() {
        return {
            tab: 0,
            frameTab: 0,
            calendarKeyword: "",
            userKeyword: "",
            exclude: false,
            fetchingCalendars: false,
            fetchingUsers: false,
        };
    },

    watch: {
        async tab(newValue) {
            if (newValue == 1) {
                // Calendars
                if (this.frameTab == 0) {
                    if (this.calendars.length == 0) {
                        this.fetchingCalendars = true;
                    }

                    try {
                        await store.dispatch(FETCH_CALENDARS);
                    } finally {
                        this.fetchingCalendars = false;
                    }
                    // Users
                } else if (this.frameTab == 1 || this.frameTab == 2) {
                    this.fetchingUsers = true;

                    try {
                        // await store.dispatch(FETCH_USERS);
                    } finally {
                        this.fetchingUsers = false;
                    }
                }
            }
        },

        exclude(value) {
            if (true) {
                const prospectsParamValue = this.prospectsParamValue(
                    value ? this.withKeyEvents : this.withoutKeyEvents
                );

                this.eventParams = prospectsParamValue
                    ? prospectsParamValue
                    : {};
            }
        },
    },

    computed: {
        ...mapGetters([
            "calendars",
            "users",
            "prospectsParamValue",
            "prospectsParamExists",
        ]),

        /**
         *
         */
        withKeyEvents() {
            return "withEvents";
        },

        /**
         *
         */
        withoutKeyEvents() {
            return "withoutEvents";
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
        filterCalendars: {
            get() {
                const eventParams = this.eventParams;

                if (!eventParams || !eventParams.withCalendars) {
                    return [];
                }

                return eventParams.withCalendars;
            },
            set(value) {
                const eventParams = this.eventParams || {};
                if (value.length > 0) {
                    eventParams.withCalendars = value;
                } else if (eventParams.withCalendars) {
                    delete eventParams.withCalendars;
                }
                this.eventParams =
                    Object.keys(eventParams).length > 0 ? eventParams : null;
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
        filterCreators: {
            get() {
                const eventParams = this.eventParams;

                if (
                    !eventParams ||
                    !eventParams.withCreators ||
                    !eventParams.withCreators.ids
                ) {
                    return [];
                }

                return eventParams.withCreators.ids;
            },
            set(value) {
                const eventParams = this.eventParams || {};
                if (value.length > 0) {
                    if (!eventParams.withCreators) {
                        eventParams.withCreators = {};
                    }

                    eventParams.withCreators.ids = value;
                } else if (
                    eventParams.withCreators &&
                    eventParams.withCreators.ids
                ) {
                    delete eventParams.withCreators.ids;

                    if (Object.keys(eventParams.withCreators).length == 0) {
                        delete eventParams.withCreators;
                    }
                }
                this.eventParams =
                    Object.keys(eventParams).length == 0 ? null : eventParams;
            },
        },

        /**
         *
         */
        filterStartedAt: {
            get() {
                const eventParams = this.eventParams;

                if (!eventParams || !eventParams.startedAt) {
                    return null;
                }

                return eventParams.startedAt;
            },
            set(value) {
                const eventParams = this.eventParams || {};
                if (value) {
                    eventParams.startedAt = value;
                } else if (eventParams.startedAt) {
                    delete eventParams.startedAt;
                }
                this.eventParams =
                    Object.keys(eventParams).length > 0 ? eventParams : null;
            },
        },

        /**
         *
         */
        filterEndedAt: {
            get() {
                const eventParams = this.eventParams;

                if (!eventParams || !eventParams.endedAt) {
                    return null;
                }

                return eventParams.endedAt;
            },
            set(value) {
                const eventParams = this.eventParams || {};
                if (value) {
                    eventParams.endedAt = value;
                } else if (eventParams.endedAt) {
                    delete eventParams.endedAt;
                }
                this.eventParams =
                    Object.keys(eventParams).length > 0 ? eventParams : null;
            },
        },

        /**
         *
         */
        filterComing: {
            get() {
                const eventParams = this.eventParams;

                return (
                    eventParams &&
                    eventParams.coming !== undefined &&
                    eventParams.coming == 1
                );
            },
            set(value) {
                const eventParams = this.eventParams || {};
                if (value) {
                    eventParams.coming = 1;
                } else if (eventParams.coming !== undefined) {
                    delete eventParams.coming;
                }
                this.eventParams =
                    Object.keys(eventParams).length > 0 ? eventParams : null;
            },
        },

        /**
         *
         */
        filterNotComing: {
            get() {
                const eventParams = this.eventParams;

                return (
                    eventParams &&
                    eventParams.coming !== undefined &&
                    eventParams.coming == 0
                );
            },
            set(value) {
                const eventParams = this.eventParams || {};
                if (value) {
                    eventParams.coming = 0;
                } else if (eventParams.coming !== undefined) {
                    delete eventParams.coming;
                }
                this.eventParams =
                    Object.keys(eventParams).length > 0 ? eventParams : null;
            },
        },

        /**
         *
         */
        filterConfirmed: {
            get() {
                const eventParams = this.eventParams;

                return (
                    eventParams &&
                    eventParams.confirmed !== undefined &&
                    eventParams.confirmed == 1
                );
            },
            set(value) {
                const eventParams = this.eventParams || {};
                if (value) {
                    eventParams.confirmed = 1;
                } else if (eventParams.confirmed !== undefined) {
                    delete eventParams.confirmed;
                }
                this.eventParams =
                    Object.keys(eventParams).length > 0 ? eventParams : null;
            },
        },

        /**
         *
         */
        filterNotConfirmed: {
            get() {
                const eventParams = this.eventParams;

                return (
                    eventParams &&
                    eventParams.confirmed !== undefined &&
                    eventParams.confirmed == 0
                );
            },
            set(value) {
                const eventParams = this.eventParams || {};
                if (value) {
                    eventParams.confirmed = 0;
                } else if (eventParams.confirmed !== undefined) {
                    delete eventParams.confirmed;
                }
                this.eventParams =
                    Object.keys(eventParams).length > 0 ? eventParams : null;
            },
        },

        /**
         *
         */
        filterDone: {
            get() {
                const eventParams = this.eventParams;

                return (
                    eventParams &&
                    eventParams.done !== undefined &&
                    eventParams.done == 1
                );
            },
            set(value) {
                const eventParams = this.eventParams || {};
                if (value) {
                    eventParams.done = 1;
                } else if (eventParams.done !== undefined) {
                    delete eventParams.done;
                }
                this.eventParams =
                    Object.keys(eventParams).length > 0 ? eventParams : null;
            },
        },

        /**
         *
         */
        filterNotDone: {
            get() {
                const eventParams = this.eventParams;

                return (
                    eventParams &&
                    eventParams.done !== undefined &&
                    eventParams.done == 0
                );
            },
            set(value) {
                const eventParams = this.eventParams || {};
                if (value) {
                    eventParams.done = 0;
                } else if (eventParams.done !== undefined) {
                    delete eventParams.done;
                }
                this.eventParams =
                    Object.keys(eventParams).length > 0 ? eventParams : null;
            },
        },

        /**
         *
         */
        filterCalendarsName() {
            return this.filterCalendars
                .map((fu) => this.calendars.find((u) => u.id == fu))
                .filter((u) => u)
                .map((u) => u.name)
                .join(", ");
        },

        /**
         *
         */
        filterUsersName() {
            return this.filterUsers
                .map((fu) => this.users.find((u) => u.id == fu))
                .filter((u) => u)
                .map((u) => u.name)
                .join(", ");
        },

        /**
         *
         */
        filterCreatorsName() {
            return this.filterCreators
                .map((fu) => this.users.find((u) => u.id == fu))
                .filter((u) => u)
                .map((u) => u.name)
                .join(", ");
        },

        /**
         *
         */
        filteredCalendars() {
            const keyword = removeStringAccent(this.calendarKeyword);

            return this.calendars.filter(
                (calendar) =>
                    removeStringAccent(calendar.name).indexOf(keyword) >= 0
            );
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
