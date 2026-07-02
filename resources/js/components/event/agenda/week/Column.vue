<template>
    <div
        :class="['hc-user-agenda-week-column', isWE ? 'week-end' : '']"
        :style="{
            width: width + 'px',
        }"
        :id="'hc-agenda-column-' + dateString"
    >
        <header-cell
            :date="date"
            :events="dayEvents"
            :daily-directions="dailyDirections"
            :fetching-direction="fetchingDirection"
        />
        <div
            :class="[
                'hc-user-agenda-week-column-body',
                contextMenu ? 'context-menu' : '',
            ]"
        >
            <div class="hc-user-agenda-week-hour-blocs">
                <div
                    class="hc-user-agenda-week-hour-bloc"
                    v-for="i in projectUserSettingsAgendaMaximumHour -
                    projectUserSettingsAgendaMinimumHour"
                    :key="i"
                >
                    <cell
                        v-for="j in 4"
                        :key="j"
                        :date="date"
                        :hour="i + projectUserSettingsAgendaMinimumHour - 1"
                        :minute="(j - 1) * 15"
                    />
                </div>
            </div>
            <div class="hc-user-agenda-week-events">
                <div class="hc-user-agenda-week-events-list">
                    <div
                        class="hc-user-agenda-week-events-per-user"
                        v-for="(events, userId) in allDayEvents"
                        :key="userId"
                    >
                        <div
                            v-if="agendaList"
                            class="hc-user-agenda-week-events-user"
                        >
                            <div
                                class="hc-item-main-content"
                                v-text="events[0].user.name"
                            ></div>
                        </div>
                        <event
                            v-for="e in events"
                            :key="e.id"
                            :event="e"
                            :agenda-date="dateString"
                            :daily-directions="dailyDirections"
                            @context-menu="(value) => (contextMenu = value)"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.hc-user-agenda-week-column {
    min-width: 300px;
    display: inline-block;
    vertical-align: top;
}

.hc-user-agenda-week-column:last-child {
    /*overflow: hidden;*/
}

.hc-user-agenda-week-column-body {
    position: relative;
    width: 100%;
}

.hc-user-agenda-week-column-body.context-menu {
    z-index: 100;
}

.agenda-list .hc-user-agenda-week-column-body {
    height: calc(100% - 34px);
}

.agenda-list .hc-user-agenda-week-column-body:before {
    content: "";
    position: absolute;
    right: -1px;
    border-left: 1px solid #dddddd;
    height: 100%;
}

.hc-user-agenda-week-hour-blocs {
    width: 100%;
    display: flex;
    flex-direction: column;
}

.agenda-list .hc-user-agenda-week-hour-blocs {
    display: none;
}

.hc-user-agenda-week-hour-bloc {
    display: flex;
    flex-direction: column;
    position: relative;
}

.hc-user-agenda-week-hour-bloc:before {
    border: 1px solid #ddd;
    bottom: -1px;
    content: "";
    display: inline-block;
    left: -1px;
    pointer-events: none;
    position: absolute;
    right: -1px;
    top: -1px;
}

.hc-user-agenda-week-events {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    pointer-events: none;
    padding: 0 5px;
    z-index: 1;
}

.hc-user-agenda-week-events-list {
    position: relative;
    width: 100%;
    height: 100%;
}

.agenda-list .hc-user-agenda-week-column-body {
    height: calc(100% - 34px);
    float: left;
}

.agenda-list .hc-user-agenda-week-events {
    position: relative;
    height: 100%;
    overflow: visible;
}

.agenda-list .hc-user-agenda-week-column {
    height: 100%;
}

.agenda-list .hc-user-agenda-week-events-list {
    height: auto;
}

.agenda-list .hc-user-agenda-week-events-per-user {
    width: 100%;
    padding-bottom: 10px;
    border-bottom: 1px solid #ddd;
    margin-bottom: 1px;
}

.hc-user-agenda-week-events-user {
    display: flex;
    flex-direction: row;
    width: 100%;
    height: 22px;
    background-color: white;
    top: -1px;
    position: relative;
    font-size: 11px;
    line-height: 26px;
    text-align: center;
    font-weight: 700;
}

@media (max-width: 767px) {
    .agenda-list .hc-user-agenda-week-column {
        width: 100% !important;
        height: auto !important;
    }
}
</style>

<script>
import ApiService from "@/apis/api.service";
import { mapGetters } from "vuex";
import store from "@/store";

import { FETCH_EVENT_USER_DAILY_DIRECTION } from "@/actions/project/event";

import Cell from "./Cell.vue";
import HeaderCell from "./HeaderCell.vue";
import Event from "./Event.vue";

export default {
    components: {
        Cell,
        HeaderCell,
        Event,
    },

    props: {
        date: {},
        width: {
            type: Number,
            default: 330,
        },
    },

    data() {
        return {
            directions: [],
            dailyDirections: {},
            fetchingDirection: false,
            contextMenu: false,
        };
    },

    methods: {
        async userEventsDailyDirection(user) {
            this.fetchingDirection = true;

            try {
                const direction_at = this.dateString;
                let data = this.getEventUserDailyDirection(
                    user,
                    direction_at,
                    this.dayPhysicalEvents
                        .filter((event) => event.user_id == user.id)
                        .map((event) => event.id)
                );

                if (!data) {
                    data = await store.dispatch(
                        FETCH_EVENT_USER_DAILY_DIRECTION,
                        { user, direction_at }
                    );
                }

                if (data && data.direction) {
                    this.dailyDirections[user.id] = data;
                }
            } finally {
                this.fetchingDirection = false;
            }
        },

        fetchDailyDirection() {
            this.dailyDirections = {};

            for (let i in this.dayPhysicalEventsUsers) {
                this.userEventsDailyDirection(this.dayPhysicalEventsUsers[i]);
            }
        },
    },

    watch: {
        dayPhysicalEvents(newValue, oldValue) {
            if (newValue.length == 0) {
                this.dailyDirections = {};
                return;
            }

            const newDates = newValue
                .sort((a, b) => (a.started_at > b.started_at ? 1 : -1))
                .map((event) => event.location)
                .join(";");
            const oldDates = oldValue
                .sort((a, b) => (a.started_at > b.started_at ? 1 : -1))
                .map((event) => event.location)
                .join(";");

            if (newDates == oldDates) {
                return;
            }

            this.fetchDailyDirection();
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "events",
            "globalEvents",
            "getEventUserDailyDirection",
            "newEvent",
            "draggedEvent",
            "resizedEvent",
            "agendaList",
            "agendaOtherProjects",
            "projectUserSettingsAgendaMinimumHour",
            "projectUserSettingsAgendaMaximumHour",
        ]),

        dateString() {
            return dateToString(this.date).substring(0, 10);
        },

        isWE() {
            const day = this.date.getDay();
            return day == 0 || day == 6;
        },

        dayPhysicalEventsUsers() {
            return this.dayPhysicalEvents
                .filter(
                    (event, index, array) =>
                        array.findIndex((e) => event.user_id == e.user_id) ==
                        index
                )
                .map((event) => event.user);
        },

        dayEvents() {
            return this.events.filter(
                (event) =>
                    event.started_at.substring(0, 10) <= this.dateString &&
                    this.dateString <= event.ended_at.substring(0, 10)
            );
        },

        dayGlobalEvents() {
            return this.globalEvents.filter(
                (event) =>
                    event.started_at.substring(0, 10) <= this.dateString &&
                    this.dateString <= event.ended_at.substring(0, 10)
            );
        },

        allDayEvents() {
            let events = [
                ...this.dayEvents,
                ...(this.agendaOtherProjects ? this.dayGlobalEvents : []),
            ];

            // If an event is being dragged
            if (this.draggedEvent) {
                if (
                    // If the event being dragged is from the current cell
                    events.find((event) => event.id == this.draggedEvent.id)
                ) {
                    // Remove the event being dragged from the current cell
                    events = events.filter(
                        (event) => event.id != this.draggedEvent.id
                    );
                }

                if (
                    // and it is being dragged into the current cell
                    this.draggedEvent.started_at.substring(0, 10) ==
                    this.dateString
                ) {
                    // Add the dragged event into the current cell
                    events.push(this.draggedEvent);
                }
            }

            // If an event is being resized
            if (this.resizedEvent) {
                if (
                    // If the event being resized is from the current cell
                    events.find((event) => event.id == this.resizedEvent.id)
                ) {
                    // Remove the event being resized from the current cell
                    events = events.filter(
                        (event) => event.id != this.resizedEvent.id
                    );
                }

                if (
                    // if it is being resized into the current cell
                    this.resizedEvent.started_at.substring(0, 10) ==
                    this.dateString
                ) {
                    // Add the resized event into the current cell
                    events.push(this.resizedEvent);
                }
            }

            // If an event is being created
            if (
                this.newEvent &&
                this.newEvent.started_at.substring(0, 10) == this.dateString
            ) {
                // Add the resized event into the current cell
                events.push(this.newEvent);
            }

            events.sort((event1, event2) =>
                event1.started_at < event2.started_at
                    ? -1
                    : event1.started_at > event2.started_at
                    ? 1
                    : event1.id < event2.id
                    ? -1
                    : 1
            );

            events.forEach((event, j) => {
                event.indexPos = 0;

                for (let i = j - 1; i >= 0; --i) {
                    let prevEvent = events[i];

                    if (event.started_at < prevEvent.ended_at) {
                        event.indexPos =
                            prevEvent.indexPos !== undefined
                                ? prevEvent.indexPos + 1
                                : 1;
                        break;
                    }
                }
            });

            events = events.reduce((carry, event) => {
                if (!carry[event.user_id]) {
                    carry[event.user_id] = [];
                }

                carry[event.user_id].push(event);

                return carry;
            }, {});

            return events;
        },

        dayPhysicalEvents() {
            return this.dayEvents
                .filter(
                    (event) =>
                        event.calendar.type == "physical" && event.location
                )
                .sort((event1, event2) =>
                    event1.started_at < event2.started_at
                        ? -1
                        : event1.started_at > event2.started_at
                        ? 1
                        : event1.id < event2.id
                        ? -1
                        : 1
                );
        },
    },
};
</script>
