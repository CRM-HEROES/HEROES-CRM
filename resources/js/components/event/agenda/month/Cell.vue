<template>
    <td
        :class="[
            'hc-user-agenda-month-table-cell',
            currentMonth ? 'current-month' : '',
            currentDay ? 'current-day' : '',
        ]"
        @click="addEvent"
        @mouseenter="dragEnter"
        @mouseup="dragEnd"
        @dragover.prevent
    >
        <div class="hc-user-agenda-month-table-cell-content">
            <span class="hc-user-agenda-month-table-cell-day">
                <span v-text="date.getDate()"></span>
            </span>
            <div class="hc-user-agenda-month-table-cell-menus">
                <icon
                    v-if="prospects.length > 0"
                    class="fa fa-comment icon-purple"
                    tag="a"
                    :size="30"
                    :icon-size="10"
                    @click.prevent.stop="bulkSms"
                />
                <icon
                    v-if="dayPhysicalEvents.length > 0"
                    :size="30"
                    :icon-size="10"
                    class="fa fa-route"
                    :style="{
                        color: this.directionColor,
                    }"
                    @click.prevent.stop="showDirection"
                >
                    <loading :loading="fetchingDirection" />
                </icon>
            </div>
            <div class="hc-user-agenda-month-table-cell-events">
                <div class="hc-user-agenda-month-table-cell-events-content">
                    <event
                        v-for="event in shownEvents"
                        :key="event.id"
                        :event="event"
                    />
                    <div
                        v-if="allDayEvents.length > maxShownEvents"
                        class="hc-user-agenda-month-table-cell-less-more"
                    >
                        <span
                            v-if="showEventsFrom > 0"
                            class="hc-user-agenda-month-table-cell-less"
                            @click.prevent.stop="showEventsFrom -= 3"
                        >
                            <i class="fa fa-caret-up"></i>
                        </span>
                        <span
                            v-if="hiddenEvents.length > 0"
                            class="hc-user-agenda-month-table-cell-more"
                            v-text="hiddenEvents.length + ' plus ...'"
                            @click.prevent.stop="showEventsFrom += 3"
                        ></span>
                    </div>
                </div>
            </div>
        </div>
    </td>
</template>

<style>
.hc-user-agenda-month-table-cell {
    width: 20% !important;
    min-width: 200px !important;
    height: 100%;
    vertical-align: top;
    cursor: pointer;
    overflow: hidden;
    position: relative;
    background-color: #fcfcfc;
}

.agenda-week-end .hc-user-agenda-month-table-cell {
    width: 14.2857% !important;
}

.hc-user-agenda-month-table-cell.current-month {
    background-color: white;
}

.hc-user-agenda-month-table-cell-content {
    display: flex;
    flex-direction: column;
    width: 100%;
    height: 100%;
}

.hc-user-agenda-month-table-cell-day {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 30px;
    font-size: 12px;
    color: #777;
}

.current-month .hc-user-agenda-month-table-cell-day {
    color: #000;
}

.hc-user-agenda-month-table-cell-day > span {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
}

.current-day .hc-user-agenda-month-table-cell-day > span {
    background-color: #7939b8;
    color: white;
}

.hc-user-agenda-month-table-cell-menus {
    position: absolute;
    right: 0;
    top: 0;
    display: flex;
    flex-direction: row;
    align-items: center;
    height: 23.07%;
}

.hc-user-agenda-month-table-cell-events {
    width: 100%;
    padding: 0 2px 2px 2px;
    flex: 1;
}

.hc-user-agenda-month-table-cell-events-content {
    flex-direction: column;
    display: flex;
    width: 100%;
    height: 100%;
    gap: 1px;
    flex: 1;
    position: relative;
}

.hc-user-agenda-month-table-cell-less-more {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 20%;
    gap: 3px;
    position: absolute;
    bottom: 0;
    left: 0;
}

.hc-user-agenda-month-table-cell-less,
.hc-user-agenda-month-table-cell-more {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    flex: 1;
    font-size: 11px;
    height: 100%;
    border-radius: 2px;
    cursor: pointer;
}

.hc-user-agenda-month-table-cell-less {
    font-size: 10px;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { OPEN_SLIDE } from "@/actions/slide";
import {
    SET_EVENT,
    SET_DRAGGED_EVENT,
    UPDATE_EVENT,
} from "@/actions/project/event";
import {
    SET_PROSPECT,
    UPDATE_SELECTED_PROSPECTS,
} from "@/actions/project/prospect";
import { TOGGLE_USER_DAILY_DIRECTION } from "@/actions/project/user/daily-direction";
import { FETCH_EVENT_USER_DAILY_DIRECTION } from "@/actions/project/event";

import Event from "./Event.vue";

export default {
    components: {
        Event,
    },

    props: {
        date: {},

        currentMonth: {
            type: Boolean,
            default: true,
        },

        currentDay: {
            type: Boolean,
            default: true,
        },
    },

    data() {
        return {
            showEventsFrom: 0,
            maxShownEvents: 4,
            dailyDirections: {},
            directions: [],
            fetchingDirection: false,
        };
    },

    methods: {
        addEvent() {
            const startDate = new Date(this.date);
            startDate.setHours(8);
            startDate.setMinutes(0);

            const endDate = new Date(this.date);
            endDate.setHours(9);
            endDate.setMinutes(0);

            store.commit(SET_PROSPECT, null);
            store.commit(SET_EVENT, {
                user: this.$store.state.auth.user,
                started_at: dateToString(startDate),
                ended_at: dateToString(endDate),
            });
            store.commit(OPEN_SLIDE, "prospect-manage-events");
        },

        dragEnter() {
            if (this.draggedEvent) {
                const delay = this.getEventDelayInMinutes(
                    this.draggedEvent.started_at,
                    this.draggedEvent.ended_at
                );

                this.draggedEvent.started_at =
                    this.dateString +
                    this.draggedEvent.started_at.substring(10);

                let endDate = new Date(this.draggedEvent.started_at);
                endDate.setMinutes(endDate.getMinutes() + delay);

                this.draggedEvent.ended_at = dateToString(endDate);
            }
        },

        dragEnd() {
            if (this.draggedEvent) {
                store.dispatch(UPDATE_EVENT, {
                    id: this.draggedEvent.id,
                    started_at: this.draggedEvent.started_at,
                    ended_at: this.draggedEvent.ended_at,
                });
                store.commit(SET_DRAGGED_EVENT, null);
            }
        },

        getEventDelayInMinutes(start, end) {
            let ms = Math.abs(new Date(end) - new Date(start));
            return Math.floor(ms / 1000 / 60);
        },

        showDirection() {
            if (this.direction) {
                for (let i in this.dayPhysicalEventsUsers) {
                    store.commit(
                        TOGGLE_USER_DAILY_DIRECTION,
                        this.dailyDirections[this.dayPhysicalEventsUsers[i].id]
                    );
                }
            } else {
                this.dailyDirections = {};

                for (let i in this.dayPhysicalEventsUsers) {
                    const userId = this.dayPhysicalEventsUsers[i];

                    this.userEventsDailyDirection(userId).then((value) => {
                        store.commit(TOGGLE_USER_DAILY_DIRECTION, value);
                    });
                }

                store.commit(OPEN_SLIDE, "user-daily-direction");
            }
        },

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

                return data;
            } finally {
                this.fetchingDirection = false;
            }

            return null;
        },

        bulkSms() {
            store.commit(SET_PROSPECT, null);
            store.commit(
                UPDATE_SELECTED_PROSPECTS,
                this.prospects.map((p) => p.id)
            );
            store.commit(OPEN_SLIDE, "prospect-manage-sms");
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "events",
            "globalEvents",
            "draggedEvent",
            "getEventUserDailyDirection",
            "directionFor",
            "agendaOtherProjects",
        ]),

        dateString() {
            return dateToString(this.date).substring(0, 10);
        },

        dayEvents() {
            return this.events
                .filter(
                    (event) =>
                        event.started_at.substring(0, 10) == this.dateString
                )
                .sort((event1, event2) =>
                    event1.started_at < event2.started_at ? -1 : 1
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
                    // If the event being dragged is not from the current cell
                    !events.find((event) => event.id == this.draggedEvent.id) &&
                    // and it is being dragged into the current cell
                    this.draggedEvent.started_at.substring(0, 10) ==
                        this.dateString
                ) {
                    // Add the dragged event into the current cell
                    events.push(this.draggedEvent);
                } else if (
                    // If the event being dragged is from the current cell
                    events.find((event) => event.id == this.draggedEvent.id) &&
                    // and it is being dragged outside the current cell
                    this.draggedEvent.started_at.substring(0, 10) !=
                        this.dateString
                ) {
                    // Remove the event being dragged from the current cell
                    events = events.filter(
                        (event) => event.id != this.draggedEvent.id
                    );
                }
            }

            return events.sort((event1, event2) =>
                event1.started_at < event2.started_at ? -1 : 1
            );
        },

        shownEvents() {
            return this.allDayEvents.length > this.maxShownEvents
                ? this.allDayEvents.slice(
                      this.showEventsFrom,
                      this.showEventsFrom + this.maxShownEvents - 1
                  )
                : this.allDayEvents;
        },

        hiddenEvents() {
            return this.allDayEvents.length > this.maxShownEvents
                ? this.allDayEvents.slice(
                      this.showEventsFrom + this.maxShownEvents - 1
                  )
                : [];
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

        dayPhysicalEvents() {
            return this.events.filter(
                (event) => event.calendar.type == "physical" && event.location
            );
        },

        prospects() {
            return this.allDayEvents.map((e) => e.prospect).filter((p) => p);
        },

        direction() {
            const keys = Object.keys(this.dailyDirections);

            if (keys.length == 0) {
                return null;
            }

            return this.directionFor(
                this.dailyDirections[keys[0]].user_id,
                this.dailyDirections[keys[0]].direction_at
            );
        },

        directionColor() {
            if (!this.direction) {
                return "#AAAAAA";
            }

            return this.direction.color;
        },
    },
};
</script>
