<template>
    <div>
        <item @click="$emit('back')" style="border-bottom: 1px solid #eee">
            <icon class="fa fa-caret-left" />
            <div
                class="hc-item-main-content hc-flex-column"
                v-text="$t('event.choose_calendar')"
            ></div>
        </item>
        <search v-model="calendarKeyword" />
        <item-list class="hc-flex-1" padding="12px" style="position: relative">
            <calendar-row
                v-for="calendar in filteredCalendars"
                :key="calendar.id"
                :calendar="calendar"
                @click="$emit('calendar-selected', calendar)"
            />
            <loading :loading="fetchingCalendars" />
        </item-list>
        <buttons v-if="can('all.project.calendar.add')">
            <a @click.prevent="addCalendar" v-text="$t('add')"></a>
        </buttons>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Components
import CalendarRow from "./CalendarRow.vue";

import { OPEN_MODAL } from "@/actions/modal";

export default {
    components: {
        CalendarRow,
    },

    data() {
        return {
            calendarKeyword: "",
        };
    },

    methods: {
        addCalendar() {
            store.commit(OPEN_MODAL, "calendar-add");
        },
    },

    computed: {
        ...mapGetters(["calendars", "can"]),

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
    },
};
</script>
