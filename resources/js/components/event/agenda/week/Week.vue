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
                <a
                    class="fa fa-angle-left"
                    @click.prevent="scrollLeft"
                    title="Jour précédent"
                ></a>
                <a
                    class="fa fa-angle-double-left"
                    @click.prevent="prevDate"
                    title="Semaine précédente"
                ></a>
            </div>

            <div
                class="hc-user-agenda-week-pagination"
                id="hc-user-agenda-week-pagination-next"
            >
                <a
                    class="fa fa-angle-right"
                    @click.prevent="scrollRight"
                    title="Jour suivant"
                ></a>
                <a
                    class="fa fa-angle-double-right"
                    @click.prevent="nextDate"
                    title="Semaine suivante"
                ></a>
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
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;

    display: flex;
    align-items: center;
    gap: 3px;

    width: auto;
    height: auto;
    padding: 4px;

    background: rgba(255, 255, 255, 0.94);
    border: 1px solid #e2e8f0;
    border-radius: 10px;

    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.10);
    backdrop-filter: blur(8px);

    transition: left 100ms ease-out, right 100ms ease-out;
}

/*
 * Quand le panneau des filtres (Users.vue) est affiché, il occupe
 * la gauche de #hc-user-agenda-week-body : on décale la pagination
 * précédente d'autant pour qu'elle reste visible.
 */
#hc-user-agenda-week.agenda-filter #hc-user-agenda-week-pagination-prev {
    left: 240px;
}

/* .agenda-list .hc-user-agenda-week-pagination {
    display: none;
} */

.hc-user-agenda-week-pagination > a {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 30px;
    height: 34px;
    padding: 0;

    background: transparent;
    border-radius: 7px;

    color: #475569;
    font-size: 17px;
    text-decoration: none;
    cursor: pointer;

    transition:
        background 0.2s ease,
        color 0.2s ease,
        transform 0.2s ease;
}
.hc-user-agenda-week-pagination > a:hover {
    background: #eff6ff;
    color: #2563eb;
}

/* Clic */
.hc-user-agenda-week-pagination > a:active {
    transform: scale(0.9);
}

/* Gauche */
#hc-user-agenda-week-pagination-prev {
    left: 8px;
    top: 50%;
    transform: translateY(-50%);

    display: flex;
    flex-direction: column;
    gap: 6px;
}

/* Droite */
#hc-user-agenda-week-pagination-next {
    right: 8px;
    top: 50%;
    transform: translateY(-50%);

    display: flex;
    flex-direction: column;
    gap: 6px;
}

#hc-user-agenda-week-pagination-prev > a {
    border-radius: 0 1000px 1000px 0;
    padding-right: 6px;
}

#hc-user-agenda-week-pagination-next > a {
    border-radius: 1000px 0 0 1000px;
    padding-left: 6px;
}

.hc-user-agenda-week-scroll-btn-container {
    position: absolute;
    top: 0;
    height: 50%;
    width: 0;
    z-index: 2;
}

.hc-user-agenda-week-scroll-btn-container:before {
    content: "";
    display: inline-block;
    height: 100%;
    vertical-align: middle;
}

.hc-user-agenda-week-scroll-btn-container > div {
    display: inline-block;
    position: relative;
    width: 0;
    height: 0;
    vertical-align: middle;
}

.hc-user-agenda-week-scroll-btn-container > div > a {
    position: absolute;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-color: #ddda;
    line-height: 60px;
    cursor: pointer;
    transform: translateY(-50%);
}

.hc-user-agenda-week-scroll-btn-container > div > a:hover {
    background-color: #ddd;
}

.hc-user-agenda-week-scroll-btn-container > div > a:before {
    content: "";
    display: inline-block;
    width: 0;
    height: 0;
    border-radius: 2px;
    position: absolute;
    top: 24px;
}

.hc-user-agenda-week-scroll-btn-container.left {
    left: 0;
}

.hc-user-agenda-week-scroll-btn-container.left > div > a {
    left: -30px;
}

.hc-user-agenda-week-scroll-btn-container.left > div > a:before {
    right: 15px;
    border-top: 6px solid transparent;
    border-right: 8px solid #000a;
    border-bottom: 6px solid transparent;
}

.hc-user-agenda-week-scroll-btn-container.right {
    right: 0;
}

.hc-user-agenda-week-scroll-btn-container.right > div > a {
    right: -30px;
}

.hc-user-agenda-week-scroll-btn-container.right > div > a:before {
    left: 15px;
    border-top: 6px solid transparent;
    border-left: 8px solid #000a;
    border-bottom: 6px solid transparent;
}

@media (max-width: 767px) {
    .agenda-list #hc-user-agenda-week-table {
        flex-direction: column;
    }
    .agenda-list #hc-user-agenda-week-columns {
        white-space: normal;
    }

    /* #hc-user-agenda-week.agenda-filter #hc-user-agenda-week-pagination-prev {
        left: 448px;
    } */
     
    .fa-angle-left {
        display: none !important;
    }
    .fa-angle-right {
        display: none !important;
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
    SET_AGENDA_DATE,
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
            windowStartedAt: null,
            windowEndedAt: null,
            eventsToAddCount: 3,
            shiftingWindow: false,
        };
    },

    created() {
        store.commit(SET_EVENTS, []);
        store.commit(SET_NEW_EVENT, null);
        store.commit(SET_DRAGGED_EVENT, null);
        store.commit(SET_RESIZED_EVENT, null);
        this.updateParamsFromUrl();
    },

    async mounted() {
        this.scrollHandler = () => this.scheduleVisibleLoad();
        this.resizeHandler = () => this.scheduleVisibleLoad();
        this.$refs.body.addEventListener("scroll", this.scrollHandler, { passive: true });
        window.addEventListener("resize", this.resizeHandler);
        await this.$nextTick();
        this.scrollToDate(this.currentDate);
        const now = new Date();
        this.scrollToTime(now.getHours(), now.getMinutes());
        this.fetchEvents();
    },

    beforeUnmount() {
        this.agendaDisposed = true;
        this.$refs.body?.removeEventListener("scroll", this.scrollHandler);
        window.removeEventListener("resize", this.resizeHandler);
        if (this.scrollFrame != null) cancelAnimationFrame(this.scrollFrame);
    },

    methods: {
        updateParamsFromUrl() {
            const filters = {};
            const searchParams = new URL(window.location.href).searchParams;
            try {
                const value = JSON.parse(searchParams.get("filters") || "{}");
                if (value && typeof value === "object" && !Array.isArray(value)) {
                    Object.assign(filters, value);
                }
            } catch (e) {}

            store.commit(SET_EVENT_PARAMS, filters);
            store.commit(SET_GLOBAL_EVENT_PARAMS, {
                ...filters,
                withoutProjects: [this.project.id],
            });
            this.setEventsFields();
            this.setMinMaxHour();
            let date = this.selectedDate(this.agendaDate);
            if (filters.startedAt && filters.endedAt &&
                (this.columnKey(date) < filters.startedAt || this.columnKey(date) > filters.endedAt)) {
                date = this.selectedDate(filters.startedAt);
            }
            this.setWeekDate(date);
            // Child filters can fetch in their mounted hook, before this view mounts.
            this.setVisibleRange(this.estimatedVisibleRange());
        },

        selectedDate(value) {
            const match = typeof value === "string" && /^(\d{4})-(\d{2})-(\d{2})/.exec(value);
            const date = match
                ? new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]), 12)
                : new Date(value || Date.now());
            if (Number.isNaN(date.getTime())) return new Date();
            while (!this.agendaWeekEnd && (date.getDay() === 0 || date.getDay() === 6)) {
                date.setDate(date.getDate() + 1);
            }
            return date;
        },

        setEventsFields() {
            const fields = ["calendar", "prospect", "user", "users"];
            if (this.projectUserSettingsEventsColorByLabel) {
                fields.push("category->" + this.projectUserSettingsEventsColorByLabel);
            }
            store.commit(SET_EVENTS_FIELDS, fields.join(","));
        },

        setVisibleRange(range) {
            if (!range) return;
            for (const key of ["startedAt", "endedAt"]) {
                const param = { key, value: range[key] };
                store.commit(ADD_EVENT_PARAMS, param);
                store.commit(ADD_GLOBAL_EVENT_PARAMS, param);
            }
        },

        fetchEvents() {
            if (this.agendaDisposed) return;
            const range = this.visibleRange();
            if (!range) return;
            this.setVisibleRange(range);
            store.dispatch(FETCH_EVENTS, {
                range,
                retainRange: { startedAt: this.windowStartedAt, endedAt: this.windowEndedAt },
            });
            this.fetchGlobalEvents(range);
        },

        fetchGlobalEvents(range = this.visibleRange()) {
            if (this.agendaOtherProjects && range && !this.agendaDisposed) {
                this.setVisibleRange(range);
                store.dispatch(FETCH_GLOBAL_EVENTS, {
                    range,
                    retainRange: { startedAt: this.windowStartedAt, endedAt: this.windowEndedAt },
                });
            }
        },

        estimatedVisibleRange() {
            const dates = this.weekDates;
            if (!dates.length) return null;
            const selected = Math.max(0, dates.findIndex((date) => this.columnKey(date) === this.columnKey(this.currentDate)));
            const availableWidth = this.$refs.body?.clientWidth ||
                window.innerWidth - (this.agendaFilter ? 240 : 0) - 50;
            const count = this.isVerticalList() ? 1 : Math.max(1, Math.ceil(availableWidth / this.width));
            const first = Math.max(0, selected - Math.floor(count / 2));
            return this.rangeWithMargin(first, Math.min(dates.length - 1, first + count - 1));
        },

        visibleColumns() {
            const body = this.$refs.body;
            if (!body) return [];
            const viewport = body.getBoundingClientRect();
            const vertical = this.isVerticalList();
            return this.weekDates.map((date, index) => {
                const element = document.getElementById("hc-agenda-column-" + this.columnKey(date));
                return element ? { date, index, element, rect: element.getBoundingClientRect() } : null;
            }).filter((column) => column && (vertical
                ? column.rect.bottom > viewport.top && column.rect.top < viewport.bottom
                : column.rect.right > viewport.left && column.rect.left < viewport.right));
        },

        visibleRange() {
            const visible = this.visibleColumns();
            return visible.length
                ? this.rangeWithMargin(visible[0].index, visible[visible.length - 1].index)
                : this.estimatedVisibleRange();
        },

        rangeWithMargin(first, last) {
            const dates = this.weekDates;
            if (!dates.length) return null;
            return {
                startedAt: this.columnKey(dates[Math.max(0, first - 1)]),
                endedAt: this.columnKey(dates[Math.min(dates.length - 1, last + 1)]),
            };
        },

        isVerticalList() {
            return window.innerWidth <= 767 && this.agendaList;
        },

        scheduleVisibleLoad() {
            if (this.scrollFrame != null || this.agendaDisposed) return;
            this.scrollFrame = requestAnimationFrame(async () => {
                this.scrollFrame = null;
                if (this.agendaDisposed || this.shiftingWindow) return;
                const body = this.$refs.body;
                if (!body) return;
                const vertical = this.isVerticalList();
                const position = vertical ? body.scrollTop : body.scrollLeft;
                const size = vertical ? body.clientHeight : body.clientWidth;
                const total = vertical ? body.scrollHeight : body.scrollWidth;
                if (total > size + 20 && position >= total - size - 20) {
                    await this.shiftWindow(this.eventsToAddCount);
                } else if (total > size + 20 && position <= 20) {
                    await this.shiftWindow(-this.eventsToAddCount);
                }
                this.fetchEvents();
            });
        },

        async shiftWindow(days) {
            if (this.shiftingWindow || this.agendaDisposed || !this.weekDates.length) return;
            this.shiftingWindow = true;
            const body = this.$refs.body;
            const vertical = this.isVerticalList();
            const anchor = this.visibleColumns()[0];
            try {
                this.setDate(this.getDateAddDays(this.weekDates[0], days));
                await this.$nextTick();
                if (this.agendaDisposed || !anchor) return;
                const element = document.getElementById("hc-agenda-column-" + this.columnKey(anchor.date));
                if (element) {
                    const rect = element.getBoundingClientRect();
                    if (vertical) body.scrollTop += rect.top - anchor.rect.top;
                    else body.scrollLeft += rect.left - anchor.rect.left;
                }
            } finally {
                this.shiftingWindow = false;
            }
        },

        async addPastEvents() {
            await this.shiftWindow(-this.eventsToAddCount);
            this.fetchEvents();
        },

        async addFutureEvents() {
            await this.shiftWindow(this.eventsToAddCount);
            this.fetchEvents();
        },

        addMobilePastEvents() {
            return this.addPastEvents();
        },

        addMobileFutureEvents() {
            return this.addFutureEvents();
        },

        setMinMaxHour() {
            const hours = {
                startedAfterTime: this.projectUserSettingsAgendaMinimumHour,
                startedBeforeTime: this.projectUserSettingsAgendaMaximumHour,
            };
            for (const [key, hour] of Object.entries(hours)) {
                const param = { key, value: String(hour).padStart(2, "0") + ":00:00" };
                store.commit(ADD_EVENT_PARAMS, param);
                store.commit(ADD_GLOBAL_EVENT_PARAMS, param);
            }
        },

        setWeekDate(date) {
            this.currentDate = this.selectedDate(date);
            this.setDate(this.getDateAddDays(this.currentDate, -3));
        },

        setDate(date) {
            const count = Math.max(10, Math.ceil(window.innerWidth / this.width) + 6);
            this.windowStartedAt = this.columnKey(date);
            this.windowEndedAt = this.columnKey(this.getDateAddDays(date, count - 1));
        },

        async jumpToDate(value) {
            this.setWeekDate(this.selectedDate(value));
            this.setVisibleRange(this.estimatedVisibleRange());
            await this.$nextTick();
            if (this.agendaDisposed) return;
            this.scrollToDate(this.currentDate);
            this.fetchEvents();
        },

        prevDate() {
            const date = this.getDateAddDays(this.navigationDate(), this.agendaWeekEnd ? -7 : -5);
            store.commit(SET_AGENDA_DATE, this.columnKey(date));
        },

        nextDate() {
            const date = this.getDateAddDays(this.navigationDate(), this.agendaWeekEnd ? 7 : 5);
            store.commit(SET_AGENDA_DATE, this.columnKey(date));
        },

        navigationDate() {
            const visible = this.visibleColumns();
            if (!visible.length) return this.currentDate;
            if (this.isVerticalList()) return visible[0].date;
            const viewport = this.$refs.body.getBoundingClientRect();
            const center = (viewport.left + viewport.right) / 2;
            const column = visible.find(({ rect }) => rect.left <= center && rect.right > center);
            return (column || visible[Math.floor(visible.length / 2)]).date;
        },

        scrollLeft() {
            this.$refs.body.scrollBy(this.isVerticalList()
                ? { top: -this.$refs.body.clientHeight, behavior: "smooth" }
                : { left: -this.width, behavior: "smooth" });
        },

        scrollRight() {
            this.$refs.body.scrollBy(this.isVerticalList()
                ? { top: this.$refs.body.clientHeight, behavior: "smooth" }
                : { left: this.width, behavior: "smooth" });
        },

        scrollToDate(date) {
            const body = this.$refs.body;
            const column = document.getElementById("hc-agenda-column-" + this.columnKey(date));
            if (!body || !column) return;
            if (this.isVerticalList()) {
                body.scrollTop += column.getBoundingClientRect().top - body.getBoundingClientRect().top;
            } else {
                body.scrollLeft = -25 + column.offsetLeft -
                    (body.getBoundingClientRect().width - column.getBoundingClientRect().width) / 2;
            }
        },

        scrollToTime(hour, minute) {
            if (this.agendaList || !this.$refs.body) return;
            const minutes = (hour - 8) * 60 + minute;
            this.$refs.body.scrollTop = (minutes - minutes % 15) * 2;
        },

        columnKey(date) {
            return dateToString(date).substring(0, 10);
        },

        getDateAddDays(date, days) {
            const increment = days < 0 ? -1 : 1;
            let remaining = Math.abs(days);
            const result = new Date(date);
            while (remaining > 0) {
                result.setDate(result.getDate() + increment);
                if (this.agendaWeekEnd || (result.getDay() !== 0 && result.getDay() !== 6)) remaining--;
            }
            return result;
        },
    },

    watch: {
        agendaDate(value) {
            this.jumpToDate(value);
        },

        agendaOtherProjects(enabled) {
            if (enabled) this.fetchGlobalEvents();
        },

        agendaWeekEnd() {
            this.jumpToDate(this.currentDate);
        },

        agendaList() {
            this.$nextTick(() => this.scheduleVisibleLoad());
        },

        agendaFilter() {
            this.$nextTick(() => this.scheduleVisibleLoad());
        },

        projectUserSettingsAgendaMinimumHour() {
            this.setMinMaxHour();
            this.fetchEvents();
        },

        projectUserSettingsAgendaMaximumHour() {
            this.setMinMaxHour();
            this.fetchEvents();
        },

        projectUserSettingsEventsColorByLabel() {
            this.setEventsFields();
            this.fetchEvents();
        },
    },

    computed: {
        ...mapGetters([
            "project", "draggedEvent", "resizedEvent", "agendaFilter", "agendaList",
            "agendaWeekEnd", "agendaDate", "agendaOtherProjects",
            "projectUserSettingsAgendaMinimumHour", "projectUserSettingsAgendaMaximumHour",
            "projectUserSettingsEventsColorByLabel",
        ]),

        weekDates() {
            if (!this.windowStartedAt || !this.windowEndedAt || this.windowStartedAt > this.windowEndedAt) return [];
            const dates = [];
            const date = this.selectedDate(this.windowStartedAt);
            while (this.columnKey(date) <= this.windowEndedAt) {
                if (this.agendaWeekEnd || (date.getDay() !== 0 && date.getDay() !== 6)) dates.push(new Date(date));
                date.setDate(date.getDate() + 1);
            }
            return dates;
        },

        width() {
            return 330;
        },
    },
};
</script>
