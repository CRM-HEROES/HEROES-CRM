<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="calendarKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label" class="hc-users-table-filter-calendar">
                <icon class="fa fa-tag" />
                <div
                    class="hc-item-main-content"
                    v-text="$t('user.table.filters.calendars.with')"
                ></div>
                <icon
                    tag="a"
                    class="fa fa-thumbs-down"
                    :style="excludeCalendarStyle"
                    :title="$t('user.table.filters.calendars.without')"
                    @click.prevent="toggleExcludeCalendar"
                />
                <checkbox
                    :model-value="isCheckedCalendar"
                    @change="changeCalendar"
                />
            </item>
            <calendar-row
                v-for="calendar in filteredCalendars"
                :key="calendar.id"
                :calendar="calendar"
            />
        </item-list>
        <buttons>
            <a @click.prevent="addCalendar" v-text="$t('add')"></a>
        </buttons>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    REMOVE_USER_PARAMS,
    ADD_USER_PARAMS,
    FETCH_USERS,
} from "@/actions/project/user";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import CalendarRow from "./CalendarRow.vue";

export default {
    components: {
        CalendarRow,
    },

    data() {
        return {
            tab: 0,
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

        changeCalendar(event) {
            // Remove without param
            store.commit(REMOVE_USER_PARAMS, {
                key: this.withoutKeyCalendar,
            });

            // Add or remove with param
            store.commit(
                event.target.checked ? ADD_USER_PARAMS : REMOVE_USER_PARAMS,
                {
                    key: this.withKeyCalendar,
                }
            );
            store.dispatch(FETCH_USERS);
        },

        toggleExcludeCalendar() {
            // Add or remove with param
            store.commit(
                this.isExcludedCalendar ? ADD_USER_PARAMS : REMOVE_USER_PARAMS,
                {
                    key: this.withKeyCalendar,
                }
            );

            // Add or remove without param
            store.commit(
                this.isExcludedCalendar ? REMOVE_USER_PARAMS : ADD_USER_PARAMS,
                {
                    key: this.withoutKeyCalendar,
                }
            );

            store.dispatch(FETCH_USERS);
        },
    },

    computed: {
        ...mapGetters(["calendars", "user", "usersParamValue"]),

        /**
         *
         */
        withKeyCalendar() {
            return "withCalendars";
        },

        /**
         *
         */
        withoutKeyCalendar() {
            return "withoutCalendars";
        },

        /**
         *
         */
        isCheckedCalendar() {
            return (
                this.usersParamValue(this.withKeyCalendar) === "" ||
                this.usersParamValue(this.withoutKeyCalendar) === ""
            );
        },

        /**
         *
         */
        isExcludedCalendar() {
            return this.usersParamValue(this.withoutKeyCalendar) === "";
        },

        /**
         *
         */
        excludeCalendarStyle() {
            return {
                color: this.isExcludedCalendar ? "#CC0000" : "#CCCCCC",
            };
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
    },
};
</script>
