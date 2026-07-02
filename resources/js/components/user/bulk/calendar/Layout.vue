<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="calendarKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label">
                <icon class="fa fa-check-square" />
                <div class="hc-item-main-content" v-text="$t('all')"></div>
                <checkbox v-model="all" />
            </item>
            <calendar-row
                v-for="calendar in filteredCalendars"
                :key="calendar.id"
                :calendar="calendar"
            />
        </item-list>
        <buttons>
            <button @click.prevent="attach" :disabled="disabled">
                Assigner
            </button>
            <button
                @click.prevent="detach"
                :disabled="disabled"
                class="hc-button-danger"
            >
                Retirer
            </button>
            <a
                v-if="can('all.project.calendar.add')"
                @click.prevent="addCalendar"
                v-text="$t('add')"
            ></a>
        </buttons>
        <loading :loading="bulking" />
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    BULK_ADD_USER_CALENDAR,
    BULK_REMOVE_USER_CALENDAR,
} from "@/actions/project/user/calendar";
import {
    FETCH_USERS,
    UPDATE_SELECTED_USERS,
    UPDATE_USER_BULK_CALENDARS,
} from "@/actions/project/user";
import { OPEN_MODAL } from "@/actions/modal";
import { CLOSE_SLIDE } from "@/actions/slide";

// Components
import CalendarRow from "./CalendarRow.vue";

export default {
    components: {
        CalendarRow,
    },

    data() {
        return {
            bulking: false,
            calendarKeyword: "",
        };
    },

    methods: {
        /**
         *
         */
        addCalendar() {
            store.commit(OPEN_MODAL, "calendar-add");
        },

        /**
         *
         */
        async attach() {
            this.bulking = true;
            const usersSelected = this.usersSelected;
            store.commit(UPDATE_SELECTED_USERS, []);

            try {
                await store.dispatch(BULK_ADD_USER_CALENDAR, {
                    users: usersSelected,
                    calendars: this.userBulkCalendars,
                });
                store.dispatch(FETCH_USERS);
            } finally {
                this.bulking = false;
                store.commit(UPDATE_USER_BULK_CALENDARS, []);
                store.commit(CLOSE_SLIDE, "user-bulk-manage-calendars");
            }
        },

        /**
         *
         */
        async detach() {
            this.bulking = true;
            const usersSelected = this.usersSelected;
            store.commit(UPDATE_SELECTED_USERS, []);

            try {
                await store.dispatch(BULK_REMOVE_USER_CALENDAR, {
                    users: usersSelected,
                    calendars: this.userBulkCalendars,
                });
                store.dispatch(FETCH_USERS);
            } finally {
                this.bulking = false;
                store.commit(UPDATE_USER_BULK_CALENDARS, []);
                store.commit(CLOSE_SLIDE, "user-bulk-manage-calendars");
            }
        },
    },

    computed: {
        ...mapGetters([
            "calendars",
            "usersSelected",
            "userBulkCalendars",
            "can",
        ]),

        /**
         *
         */
        disabled() {
            return this.userBulkCalendars.length == 0;
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
                for (let i in this.filteredCalendars) {
                    if (
                        !this.userBulkCalendars.find(
                            (calendar) =>
                                calendar == this.filteredCalendars[i].id
                        )
                    ) {
                        return false;
                    }
                }

                return true;
            },
            set: async function (value) {
                if (value) {
                    store.commit(
                        UPDATE_USER_BULK_CALENDARS,
                        this.filteredCalendars.map((calendar) => calendar.id)
                    );
                } else {
                    store.commit(UPDATE_USER_BULK_CALENDARS, []);
                }
            },
        },
    },
};
</script>
