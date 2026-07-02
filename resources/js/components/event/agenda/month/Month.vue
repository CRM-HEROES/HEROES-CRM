<template>
    <div
        id="hc-user-agenda-month"
        :class="[
            agendaFilter ? 'agenda-filter' : '',
            scrollingOldLeft ? 'scrolling-old-left' : '',
            scrollingNewLeft ? 'scrolling-new-left' : '',
            scrollingOldRight ? 'scrolling-old-right' : '',
            scrollingNewRight ? 'scrolling-new-right' : '',
            agendaFilter ? 'agenda-filter' : '',
            agendaWeekEnd ? 'agenda-week-end' : '',
        ]"
    >
        <div class="hc-user-agenda-month-scroll-btn-container left">
            <div><a @click.prevent="scrollLeft"></a></div>
        </div>

        <div class="hc-user-agenda-month-scroll-btn-container right">
            <div><a @click.prevent="scrollRight"></a></div>
        </div>

        <div id="hc-user-agenda-month-body" class="hc-table">
            <users />

            <table id="hc-user-agenda-month-table" @wheel="wheel">
                <thead>
                    <th></th>
                    <header-cell
                        v-for="i in agendaWeekEnd ? 7 : 5"
                        :key="agendaWeekEnd ? i : i + 1"
                        :day="agendaWeekEnd ? i - 1 : i"
                    ></header-cell>
                </thead>
                <tbody>
                    <tr
                        v-for="(week, i) in weeks"
                        :key="year + '-' + month + '-' + i"
                        :style="rowStyle"
                    >
                        <td class="hc-user-agenda-month-table-week">
                            <div class="hc-user-agenda-month-table-week-menus">
                                <icon
                                    v-if="weekProspects(i).length > 0"
                                    class="fa fa-comment icon-purple"
                                    tag="a"
                                    :size="30"
                                    :icon-size="10"
                                    @click.prevent.stop="
                                        bulkSms(weekProspects(i))
                                    "
                                />
                            </div>
                        </td>
                        <cell
                            v-for="(d, j) in week"
                            :key="
                                d.getYear() +
                                '-' +
                                d.getMonth() +
                                '-' +
                                d.getDate()
                            "
                            :current-month="
                                d.getFullYear() == date.year &&
                                d.getMonth() == date.month
                            "
                            :current-day="dateToString(d) == currentDay"
                            :date="d"
                        />
                    </tr>
                </tbody>
            </table>
        </div>

        <card-menus />
        <user-daily-direction-slide />
        <ocr-modal />
    </div>
</template>

<style>
#hc-user-agenda-month {
    border-top: 1px solid silver;
    display: flex;
    flex-direction: column;
    width: 100%;
    height: 100%;
    user-select: none;
    position: relative;
}

#hc-user-agenda-month-body {
    display: flex;
    flex-direction: row;
    flex: 1;
    width: 100%;
    overflow: auto;
    margin-left: 0;
    transform: translateX(0);
    transition: transform 100ms cubic-bezier(0.23, 1, 0.32, 1),
        opacity 100ms cubic-bezier(0.23, 1, 0.32, 1);
}

#hc-user-agenda-month.scrolling-old-left #hc-user-agenda-month-body {
    opacity: 0;
    margin-left: 20px;
    transition: margin-left 100ms cubic-bezier(0.23, 1, 0.32, 1),
        opacity 200ms cubic-bezier(0.23, 1, 0.32, 1);
}

#hc-user-agenda-month.scrolling-new-left #hc-user-agenda-month-body {
    opacity: 0;
    transform: translateX(-20px);
    transition: none;
}

#hc-user-agenda-month.scrolling-old-right #hc-user-agenda-month-body {
    opacity: 0;
    margin-left: -20px;
    transition: margin-left 100ms cubic-bezier(0.23, 1, 0.32, 1),
        opacity 200ms cubic-bezier(0.23, 1, 0.32, 1);
}

#hc-user-agenda-month.scrolling-new-right #hc-user-agenda-month-body {
    opacity: 0;
    transform: translateX(20px);
    transition: none;
}

#hc-user-agenda-month-table {
    width: 100%;
    height: 100%;
}

#hc-user-agenda-month-table > tbody > tr:first-child > td {
    border-top: none !important;
}

#hc-user-agenda-month-table > thead > th:before {
    border: 1px solid #ddd !important;
    border-bottom: none !important;
}

#hc-user-agenda-month-table > thead > th:first-child {
    width: 30px !important;
    max-width: 30px !important;
    min-width: 30px !important;
    background-color: #fcfcfc !important;
}

#hc-user-agenda-month-table > thead > th:first-child:before {
    border-right: none !important;
}

#hc-user-agenda-month-table > thead > th:first-child + th:before {
    border-left: none !important;
}

.hc-user-agenda-month-scroll-btn-container {
    position: absolute;
    top: 0;
    height: 100%;
    width: 0;
    z-index: 1;
}

.hc-user-agenda-month-scroll-btn-container:before {
    content: "";
    display: inline-block;
    height: 100%;
    vertical-align: middle;
}

.hc-user-agenda-month-scroll-btn-container > div {
    display: inline-block;
    position: relative;
    width: 0;
    height: 0;
    vertical-align: middle;
}

.hc-user-agenda-month-scroll-btn-container > div > a {
    position: absolute;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-color: #ddda;
    line-height: 60px;
    cursor: pointer;
    transform: translateY(-50%);
}

.hc-user-agenda-month-scroll-btn-container > div > a:hover {
    background-color: #ddd;
}

.hc-user-agenda-month-scroll-btn-container > div > a:before {
    content: "";
    display: inline-block;
    width: 0;
    height: 0;
    border-radius: 2px;
    position: absolute;
    top: 24px;
}

.hc-user-agenda-month-scroll-btn-container.left {
    left: 0;
}

.hc-user-agenda-month-scroll-btn-container.left > div > a {
    left: -30px;
}

.hc-user-agenda-month-scroll-btn-container.left > div > a:before {
    right: 15px;
    border-top: 6px solid transparent;
    border-right: 8px solid #000a;
    border-bottom: 6px solid transparent;
}

.hc-user-agenda-month-scroll-btn-container.right {
    right: 0;
}

.hc-user-agenda-month-scroll-btn-container.right > div > a {
    right: -30px;
}

.hc-user-agenda-month-scroll-btn-container.right > div > a:before {
    left: 15px;
    border-top: 6px solid transparent;
    border-left: 8px solid #000a;
    border-bottom: 6px solid transparent;
}

.hc-user-agenda-month-table-week {
    width: 30px !important;
    max-width: 30px !important;
    min-width: 30px !important;
    border-right: none !important;
    background-color: #fcfcfc !important;
}

.hc-user-agenda-month-table-week + td {
    border-left: none !important;
}

.hc-user-agenda-month-table-week-menus {
    width: 30px !important;
    max-width: 30px !important;
    min-width: 30px !important;
    height: 100%;
    display: flex;
    flex-direction: column;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import {
    FETCH_EVENTS,
    ADD_EVENT_PARAMS,
    SET_EVENT_PARAMS,
    SET_AGENDA_DATE,
} from "@/actions/project/event";

import {
    FETCH_GLOBAL_EVENTS,
    ADD_GLOBAL_EVENT_PARAMS,
    SET_GLOBAL_EVENT_PARAMS,
} from "@/actions/event";

import {
    SET_PROSPECT,
    UPDATE_SELECTED_PROSPECTS,
} from "@/actions/project/prospect";

import { OPEN_SLIDE } from "@/actions/slide";

import HeaderCell from "./HeaderCell.vue";
import Cell from "./Cell.vue";
import Users from "../../filters/Users.vue";
import CardMenus from "./CardMenus.vue";
import UserDailyDirectionSlide from "../../slides/daily-direction/Slide.vue";
import OcrModal from "@/components/ocr/Modal.vue";

export default {
    components: {
        HeaderCell,
        Cell,
        Users,
        CardMenus,
        UserDailyDirectionSlide,
        OcrModal,
    },

    data() {
        const date = new Date();
        return {
            date: { year: date.getFullYear(), month: date.getMonth() },
            scrollingOldLeft: false,
            scrollingNewLeft: false,
            scrollingOldRight: false,
            scrollingNewRight: false,
        };
    },

    created() {
        this.updateParamsFromUrl();

        const date = new Date();
        date.setDate(0);
        this.currentDate = this.dateToString(date);
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

            store.dispatch(FETCH_EVENTS);
            store.dispatch(FETCH_GLOBAL_EVENTS);
        },

        fetchEvents() {
            if (this.weeks.length == 0) {
                return;
            }

            const startedAtParam = {
                key: "startedAt",
                value: dateToString(this.weeks[0][0]).substring(0, 10),
            };

            store.commit(ADD_EVENT_PARAMS, startedAtParam);
            store.commit(ADD_GLOBAL_EVENT_PARAMS, startedAtParam);

            const endedAtParam = {
                key: "endedAt",
                value: dateToString(
                    this.weeks[this.weeks.length - 1][
                        this.weeks[this.weeks.length - 1].length - 1
                    ]
                ).substring(0, 10),
            };

            store.commit(ADD_EVENT_PARAMS, endedAtParam);
            store.commit(ADD_GLOBAL_EVENT_PARAMS, endedAtParam);

            // this.setDate(date);
            store.dispatch(FETCH_EVENTS);
            store.dispatch(FETCH_GLOBAL_EVENTS);
        },

        dateToString(date) {
            return dateToString(date).substring(0, 10);
        },

        scrollLeft() {
            let date = new Date();
            date.setYear(this.date.year);
            date.setMonth(this.date.month - 1);

            this.scrollingOldLeft = true;
            setTimeout(() => {
                this.currentDate =
                    date.getFullYear() +
                    "-" +
                    (date.getMonth() < 9 ? "0" : "") +
                    (date.getMonth() + 1) +
                    "-01";

                this.scrollingOldLeft = false;
                this.scrollingNewLeft = true;

                setTimeout(() => {
                    this.scrollingNewLeft = false;
                }, 20);
            }, 50);
        },

        scrollRight() {
            let date = new Date();
            date.setYear(this.date.year);
            date.setMonth(this.date.month + 1);

            this.scrollingOldRight = true;
            setTimeout(() => {
                this.currentDate =
                    date.getFullYear() +
                    "-" +
                    (date.getMonth() < 9 ? "0" : "") +
                    (date.getMonth() + 1) +
                    "-01";

                this.scrollingOldRight = false;
                this.scrollingNewRight = true;

                setTimeout(() => {
                    this.scrollingNewRight = false;
                }, 20);
            }, 50);
        },

        weekProspects(i) {
            return this.events
                .filter(
                    (e) =>
                        e.prospect &&
                        e.started_at.substring(0, 10) >=
                            this.dateToString(this.weeks[i][0]) &&
                        e.started_at.substring(0, 10) <=
                            this.dateToString(
                                this.weeks[i][this.weeks[i].length - 1]
                            )
                )
                .map((e) => e.prospect);
        },

        bulkSms(prospects) {
            store.commit(SET_PROSPECT, null);
            store.commit(
                UPDATE_SELECTED_PROSPECTS,
                prospects.map((p) => p.id)
            );
            store.commit(OPEN_SLIDE, "prospect-manage-sms");
        },
    },

    watch: {
        weeks() {
            this.fetchEvents();
        },

        agendaDate() {
            const date = new Date(this.agendaDate);

            this.date = {
                year: date.getFullYear(),
                month: date.getMonth(),
            };
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "events",
            "agendaList",
            "agendaFilter",
            "agendaDate",
            "agendaWeekEnd",
        ]),

        currentDate: {
            get() {
                return this.agendaDate;
            },
            set(value) {
                store.commit(SET_AGENDA_DATE, value);
            },
        },

        currentDay() {
            return dateToString(new Date()).substring(0, 10);
        },

        previousSunday() {
            // Get the first day of the month
            var firstDayOfMonth = new Date(this.date.year, this.date.month, 1);

            // Calculate the difference in days from Sunday (0)
            var dayOfWeek = firstDayOfMonth.getDay();

            // If the first day of the month is Sunday, return this date
            if (dayOfWeek === (this.agendaWeekEnd ? 0 : 1)) {
                return firstDayOfMonth;
            }

            // Calculate the difference in days to the previous Sunday
            var daysToPreviousSunday = dayOfWeek;

            // Subtract the days to get the previous Sunday
            var previousSunday = new Date(firstDayOfMonth);
            previousSunday.setDate(
                firstDayOfMonth.getDate() -
                    daysToPreviousSunday +
                    (this.agendaWeekEnd ? 0 : 1)
            );

            return previousSunday;
        },

        weeks() {
            let weeks = [];

            // Loop through days in the month
            for (
                let currentMonth = this.previousSunday;
                currentMonth.getFullYear() * 100 + currentMonth.getMonth() <=
                this.date.year * 100 + this.date.month;

            ) {
                weeks.push([]);

                for (let i = 0; i < (this.agendaWeekEnd ? 7 : 5); i++) {
                    weeks[weeks.length - 1].push(new Date(currentMonth));
                    currentMonth.setDate(currentMonth.getDate() + 1);

                    if (i == 4 && !this.agendaWeekEnd) {
                        currentMonth.setDate(currentMonth.getDate() + 2);
                    }
                }
            }

            return weeks;
        },

        rowHeight() {
            return 100 / this.weeks.length + "%";
        },

        rowStyle() {
            return {
                height: this.rowHeight,
                minHeight: this.rowHeight,
                maxHeight: this.rowHeight,
            };
        },
    },
};
</script>
