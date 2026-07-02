<template>
    <div
        id="hc-user-agenda-week"
        :class="[
            draggedEvent || resizedEvent ? 'dragged-event' : '',
            agendaList ? 'agenda-list' : '',
            agendaFilter ? 'agenda-filter' : '',
        ]"
    >
        <div id="hc-user-agenda-week-body">
            <div
                class="hc-user-agenda-week-pagination"
                id="hc-user-agenda-week-pagination-prev"
            >
                <a class="fa fa-caret-left" @click.prevent="prevDate"></a>
            </div>
            <div
                class="hc-user-agenda-week-pagination"
                id="hc-user-agenda-week-pagination-next"
            >
                <a class="fa fa-caret-left" @click.prevent="nextDate"></a>
            </div>

            <users />

            <div id="hc-user-agenda-week-table" ref="body">
                <div id="hc-user-agenda-week-columns" ref="columns">
                    <time-slots />
                    <column
                        v-for="date in weekDates"
                        :key="columnKey(date)"
                        :date="date"
                        :width="width"
                    />
                </div>
            </div>
        </div>

        <card-menus />
        <user-daily-direction-slide />
        <ocr-modal />
    </div>
</template>

<style>
#hc-user-agenda-week {
    border-top: 1px solid silver;
    display: flex;
    flex-direction: column;
    width: 100%;
    height: 100%;
    user-select: none;
    position: relative;
}

#hc-user-agenda-week-body {
    display: flex;
    flex-direction: row;
    flex: 1;
    width: 100%;
    overflow: hidden;
    position: relative;
}

#hc-user-agenda-week-table {
    width: 100%;
    height: 100%;
    overflow: auto;
    position: relative;
    background: white;
    display: flex;
    flex-direction: row;
}

#hc-user-agenda-week-columns {
    width: auto;
    font-size: 0;
    white-space: nowrap;
}

.hc-user-agenda-week-pagination {
    position: absolute;
    height: 100%;
    display: flex;
    top: 0;
    align-items: center;
    justify-content: center;
    z-index: 1;
    pointer-events: none;
}

.agenda-list .hc-user-agenda-week-pagination {
    display: none;
}

.hc-user-agenda-week-pagination > a {
    background-color: #eee;
    width: 36px;
    height: 50px;
    display: flex;
    align-items: center;
    font-size: 18px;
    color: black;
    justify-content: center;
    text-decoration: none;
    cursor: pointer;
    pointer-events: all;
}

.hc-user-agenda-week-pagination > a:hover {
    background-color: #ddd;
}

#hc-user-agenda-week-pagination-prev {
    left: 0;
}

#hc-user-agenda-week-pagination-next {
    right: 0;
}

#hc-user-agenda-week-pagination-prev > a {
    border-radius: 0 1000px 1000px 0;
    padding-right: 6px;
}

#hc-user-agenda-week-pagination-next > a {
    border-radius: 1000px 0 0 1000px;
    padding-left: 6px;
}

@media (max-width: 767px) {
    .agenda-list #hc-user-agenda-week-table {
        flex-direction: column;
    }
    .agenda-list #hc-user-agenda-week-columns {
        white-space: normal;
    }
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import {
    SET_EVENTS,
    FETCH_EVENTS,
    SET_EVENTS_FIELDS,
    ADD_EVENT_PARAMS,
    SET_NEW_EVENT,
    SET_DRAGGED_EVENT,
    SET_RESIZED_EVENT,
    SET_EVENT_PARAMS,
} from "@/actions/project/event";

import {
    FETCH_GLOBAL_EVENTS,
    ADD_GLOBAL_EVENT_PARAMS,
    SET_GLOBAL_EVENT_PARAMS,
} from "@/actions/event";

import Users from "../../filters/Users.vue";
import Column from "./Column.vue";
import TimeSlots from "./TimeSlots.vue";
import CardMenus from "./CardMenus.vue";
import UserDailyDirectionSlide from "../../slides/daily-direction/Slide.vue";
import OcrModal from "@/components/ocr/Modal.vue";

export default {
    components: {
        Users,
        Column,
        TimeSlots,
        CardMenus,
        UserDailyDirectionSlide,
        OcrModal,
    },

    data() {
        return {
            currentDate: null,
            eventsToAddCount: 3,
        };
    },

    created() {
        this.currentDate = new Date();
        store.commit(SET_EVENTS, []);
        store.commit(SET_NEW_EVENT, null);
        store.commit(SET_DRAGGED_EVENT, null);
        store.commit(SET_RESIZED_EVENT, null);

        this.updateParamsFromUrl();
    },

    mounted() {
        let body = this.$refs.body;
        let columns = this.$refs.columns;
        let sl = 0;

        body.addEventListener("scroll", async (event) => {
            if (window.innerWidth > 767 || !this.agendaList) {
                if (
                    body.scrollLeft >=
                    columns.getBoundingClientRect().width -
                        body.getBoundingClientRect().width -
                        20
                ) {
                    this.addFutureEvents(false);
                    body.scrollLeft -= this.width * this.eventsToAddCount;
                } else if ((sl = body.scrollLeft) <= 20) {
                    this.addPastEvents();
                    body.scrollLeft = this.width * this.eventsToAddCount + sl;
                }
            } else {
                if (
                    body.scrollTop >=
                    columns.getBoundingClientRect().height -
                        body.getBoundingClientRect().height -
                        20
                ) {
                    this.addMobileFutureEvents();
                    // body.scrollTop -= this.width * this.eventsToAddCount;
                } else if (body.scrollTop <= 0) {
                    this.addMobilePastEvents();
                    await this.$nextTick();
                    body.scrollTop = 34 * 8;
                }
            }
        });

        const date = new Date();

        this.scrollToDate(date);
        this.scrollToTime(date.getHours(), date.getMinutes());
    },

    methods: {
        updateParamsFromUrl() {
            const url = new URL(window.location.href);
            const searchParams = new URLSearchParams(url.search);
            const filters = {};

            searchParams.forEach(function (value, param) {
                if (param == "filters") {
                    try {
                        value = JSON.parse(value);

                        for (let key in value) {
                            filters[key] = value[key];
                        }
                    } catch (e) {}
                }
            });

            store.commit(SET_EVENT_PARAMS, filters);
            store.commit(SET_GLOBAL_EVENT_PARAMS, {
                ...filters,
                withoutProjects: [this.project.id],
            });

            if (
                !this.eventsParamExists("startedAt") ||
                !this.eventsParamExists("endedAt")
            ) {
                this.setWeekDate(new Date());
            }

            this.setMinMaxHour();

            this.fetchEvents();
            store.dispatch(FETCH_GLOBAL_EVENTS);
        },

        fetchEvents() {
            let fields = ["calendar", "prospect", "user", "users"];
            if (this.projectUserSettingsEventsColorByLabel) {
                fields.push(
                    "category->" + this.projectUserSettingsEventsColorByLabel
                );
            }

            store.commit(SET_EVENTS_FIELDS, fields.join(","));
            store.dispatch(FETCH_EVENTS);
        },

        addPastEvents(add) {
            let date = new Date(this.weekDates[0]);
            date = this.getDateAddDays(date, -this.eventsToAddCount);
            // date.setDate(date.getDate() - this.eventsToAddCount);
            this.setDate(date);
            this.fetchEvents();
            store.dispatch(FETCH_GLOBAL_EVENTS);
        },

        /**
         *
         */
        addFutureEvents() {
            let date = new Date(this.weekDates[this.weekDates.length - 1]);
            date = this.getDateAddDays(date, this.eventsToAddCount);
            // date.setDate(date.getDate() + this.eventsToAddCount);

            store.commit(ADD_EVENT_PARAMS, {
                key: "endedAt",
                value: dateToString(date).substring(0, 10),
            });

            store.commit(ADD_GLOBAL_EVENT_PARAMS, {
                key: "endedAt",
                value: dateToString(date).substring(0, 10),
            });

            date = new Date(this.weekDates[this.weekDates.length - 1]);
            date = this.getDateAddDays(date, -9);
            // date.setDate(date.getDate() - 9);

            store.commit(ADD_EVENT_PARAMS, {
                key: "startedAt",
                value: dateToString(date).substring(0, 10),
            });

            store.commit(ADD_GLOBAL_EVENT_PARAMS, {
                key: "startedAt",
                value: dateToString(date).substring(0, 10),
            });

            // this.setDate(date);
            this.fetchEvents();
            store.dispatch(FETCH_GLOBAL_EVENTS);
        },

        /**
         *
         */
        addMobilePastEvents() {
            let date = new Date(this.eventsParamsValue("startedAt"));
            date = this.getDateAddDays(date, -8);
            // date.setDate(date.getDate() + this.eventsToAddCount);

            store.commit(ADD_EVENT_PARAMS, {
                key: "startedAt",
                value: dateToString(date).substring(0, 10),
            });

            store.commit(ADD_GLOBAL_EVENT_PARAMS, {
                key: "startedAt",
                value: dateToString(date).substring(0, 10),
            });

            // this.setDate(date);
            this.fetchEvents();
            store.dispatch(FETCH_GLOBAL_EVENTS);
        },

        /**
         *
         */
        addMobileFutureEvents() {
            let date = new Date(this.eventsParamsValue("endedAt"));
            date = this.getDateAddDays(date, 8);
            // date.setDate(date.getDate() + this.eventsToAddCount);

            store.commit(ADD_EVENT_PARAMS, {
                key: "endedAt",
                value: dateToString(date).substring(0, 10),
            });

            store.commit(ADD_GLOBAL_EVENT_PARAMS, {
                key: "endedAt",
                value: dateToString(date).substring(0, 10),
            });

            // this.setDate(date);
            this.fetchEvents();
            store.dispatch(FETCH_GLOBAL_EVENTS);
        },

        /**
         *
         * @param {*} date
         */
        setMinMaxHour() {
            store.commit(ADD_EVENT_PARAMS, {
                key: "startedAfterTime",
                value:
                    (this.projectUserSettingsAgendaMinimumHour < 10
                        ? "0"
                        : "") +
                    this.projectUserSettingsAgendaMinimumHour +
                    ":00:00",
            });

            store.commit(ADD_EVENT_PARAMS, {
                key: "startedBeforeTime",
                value:
                    (this.projectUserSettingsAgendaMaximumHour < 10
                        ? "0"
                        : "") +
                    this.projectUserSettingsAgendaMaximumHour +
                    ":00:00",
            });

            store.commit(ADD_GLOBAL_EVENT_PARAMS, {
                key: "startedAfterTime",
                value:
                    (this.projectUserSettingsAgendaMinimumHour < 10
                        ? "0"
                        : "") +
                    this.projectUserSettingsAgendaMinimumHour +
                    ":00:00",
            });

            store.commit(ADD_GLOBAL_EVENT_PARAMS, {
                key: "startedBeforeTime",
                value:
                    (this.projectUserSettingsAgendaMaximumHour < 10
                        ? "0"
                        : "") +
                    this.projectUserSettingsAgendaMaximumHour +
                    ":00:00",
            });
        },

        /**
         *
         * @param {*} date
         */
        setWeekDate(date) {
            let currentDate = new Date(date);

            if (deviceType() == "desktop") {
                currentDate = this.getDateAddDays(date, -4);
            }

            this.setDate(currentDate);
        },

        /**
         *
         * @param {*} date
         */
        async setDate(date) {
            this.currentDate = date;
            const sundayDate = new Date(date);

            store.commit(ADD_EVENT_PARAMS, {
                key: "startedAt",
                value: dateToString(sundayDate).substring(0, 10),
            });

            store.commit(ADD_GLOBAL_EVENT_PARAMS, {
                key: "startedAt",
                value: dateToString(sundayDate).substring(0, 10),
            });

            let saturdayDate = new Date(sundayDate);
            saturdayDate = this.getDateAddDays(saturdayDate, 9);
            // saturdayDate.setDate(sundayDate.getDate() + 9);

            store.commit(ADD_EVENT_PARAMS, {
                key: "endedAt",
                value: dateToString(saturdayDate).substring(0, 10),
            });

            store.commit(ADD_GLOBAL_EVENT_PARAMS, {
                key: "endedAt",
                value: dateToString(saturdayDate).substring(0, 10),
            });

            this.scrollToDate(date);
        },

        /**
         *
         */
        prevDate() {
            this.setDate(
                this.getDateAddDays(
                    this.currentDate,
                    this.agendaWeekEnd ? -7 : -5
                )
            );
        },

        /**
         *
         */
        async nextDate() {
            this.setDate(
                this.getDateAddDays(
                    this.currentDate,
                    this.agendaWeekEnd ? 7 : 5
                )
            );
        },

        /**
         *
         * @param {*} date
         */
        scrollToDate(date) {
            let column = document.getElementById(
                "hc-agenda-column-" + dateToString(date).substring(0, 10)
            );

            if (!column) {
                return;
            }

            this.$refs.body.scrollLeft =
                -25 +
                column.offsetLeft -
                (this.$refs.body.getBoundingClientRect().width -
                    column.getBoundingClientRect().width) /
                    2;
        },

        /**
         * Scroll to time
         */
        scrollToTime(hour, minute) {
            if (this.agendaList) {
                return;
            }

            let mn = (hour - 8) * 60 + minute;

            mn -= mn % 15;

            this.$refs.body.scrollTop = mn * 2;
        },

        columnKey(date) {
            return dateToString(date).substring(0, 10);
        },

        getDateAddDays(date, days) {
            const increment = days < 0 ? -1 : 1;
            days = days < 0 ? -days : days;
            let d = new Date(date);

            for (let i = 0; i < days; ) {
                d.setDate(d.getDate() + increment);

                if (
                    this.agendaWeekEnd ||
                    (d.getDay() != 0 && d.getDay() != 6)
                ) {
                    ++i;
                }
            }

            return d;
        },
    },

    watch: {
        async agendaDate() {
            await this.$nextTick();
            this.scrollToDate(new Date(this.agendaDate));
        },

        projectUserSettingsAgendaMinimumHour() {
            this.setMinMaxHour();
            this.fetchEvents();
            store.dispatch(FETCH_GLOBAL_EVENTS);
        },

        projectUserSettingsAgendaMaximumHour() {
            this.setMinMaxHour();
            this.fetchEvents();
            store.dispatch(FETCH_GLOBAL_EVENTS);
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "draggedEvent",
            "resizedEvent",
            "eventsParamsValue",
            "eventsParamExists",
            "agendaFilter",
            "agendaList",
            "agendaWeekEnd",
            "agendaDate",
            "projectUserSettingsAgendaMinimumHour",
            "projectUserSettingsAgendaMaximumHour",
            "projectUserSettingsEventsColorByLabel",
        ]),

        /**
         *
         */
        paramsStartedAt() {
            return this.eventsParamsValue("startedAt");
        },

        /**
         *
         */
        paramsEndedAt() {
            return this.eventsParamsValue("endedAt");
        },

        /**
         * List of date of the week of the current date
         * from sunday to saturday
         */
        weekDates() {
            if (!this.paramsEndedAt || !this.paramsEndedAt) {
                return null;
            }

            if (this.paramsStartedAt > this.paramsEndedAt) {
                return null;
            }

            // Create an array to store the dates for each day of the week
            const weekDates = [];

            let date = new Date(this.paramsStartedAt);
            const endDate = dateToString(
                new Date(this.paramsEndedAt)
            ).substring(0, 10);

            // Loop through the days of the week and add the corresponding dates
            while (dateToString(date).substring(0, 10) <= endDate) {
                if (
                    this.agendaWeekEnd ||
                    (date.getDay() != 0 && date.getDay() != 6)
                ) {
                    weekDates.push(new Date(date));
                }
                date.setDate(date.getDate() + 1);
            }

            return weekDates;
        },

        width() {
            return 330; /*window.innerWidth <= 767
                ? window.innerWidth - 50
                : Math.max((window.innerWidth - 50) / 7, 330);*/
        },
    },
};
</script>
