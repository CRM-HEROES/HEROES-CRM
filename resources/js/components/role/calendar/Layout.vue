<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="calendarKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label">
                <icon class="fa fa-check-square" />
                <div class="hc-item-main-content" v-text="$t('all')"></div>
                <checkbox v-model="all" @change="change" :disabled="canAll" />
            </item>
            <calendar-row
                v-for="calendar in filteredCalendars"
                :key="calendar.id"
                :calendar="calendar"
                :value="isCalendarChecked(calendar)"
                :disabled="canAll"
            />
        </item-list>
        <buttons v-if="can('all.project.calendar.add')">
            <a @click.prevent="addCalendar" v-text="$t('add')"></a>
        </buttons>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    BULK_ADD_ROLE_CALENDAR,
    BULK_REMOVE_ROLE_CALENDAR,
    ADD_ROLE_CALENDAR,
    REMOVE_ROLE_CALENDAR,
} from "@/actions/project/role/calendar";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import CalendarRow from "./CalendarRow.vue";

export default {
    components: {
        CalendarRow,
    },

    data() {
        return {
            name: "role-manage-calendars",
            tab: 0,
            calendarKeyword: "",
        };
    },

    methods: {
        /**
         *
         * @param {*} calendar
         */
        isCalendarChecked(calendar) {
            return (
                this.canAll ||
                this.roleCalendars.findIndex((l) => l.id == calendar.id) >= 0
            );
        },

        /**
         *
         */
        addCalendar() {
            store.commit(OPEN_MODAL, "calendar-add");
        },
    },

    computed: {
        ...mapGetters([
            "calendars",
            "role",
            "roleCalendars",
            "rolePermissions",
            "can",
        ]),

        /**
         *
         */
        canAll() {
            return this.rolePermissions.includes("all");
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
        all: {
            get: function () {
                return (
                    this.canAll ||
                    this.roleCalendars.length == this.calendars.length
                );
            },
            set: async function (value) {
                if (value) {
                    this.calendars.forEach((f) => {
                        store.commit(ADD_ROLE_CALENDAR, f);
                    });
                    store.dispatch(BULK_ADD_ROLE_CALENDAR, {
                        roles: [this.role.id],
                        calendars: this.calendars.map((f) => f.id),
                    });
                } else {
                    this.calendars.forEach((f) => {
                        store.commit(REMOVE_ROLE_CALENDAR, f);
                    });
                    store.dispatch(BULK_REMOVE_ROLE_CALENDAR, {
                        roles: [this.role.id],
                        calendars: this.calendars.map((f) => f.id),
                    });
                }
            },
        },
    },
};
</script>
