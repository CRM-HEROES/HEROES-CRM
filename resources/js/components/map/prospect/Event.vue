<template>
    <item class="hc-map-prospect-event-row" @click.stop="edit">
        <icon size="28" :class="icon" />
        <span
            class="hc-tag hc-map-prospect-event-date"
            :style="style"
            v-text="date"
        ></span>
        <span
            class="hc-item-main-content hc-map-prospect-event-user"
            v-text="user"
        ></span>
        <icon size="20" class="fa fa-user" />
    </item>
</template>

<style>
.hc-map-prospect-event-row {
    width: 100%;
    white-space: nowrap;
    font-size: 11px;
}

.hc-map-prospect-event-row > i {
    font-size: 9px;
}

.hc-map-prospect-event-date {
    margin: 0;
    font-family: monospace;
    font-size: 12px;
}

.hc-map-prospect-event-user {
    text-align: right;
    color: #999999;
    padding: 0;
}
</style>

<script>
import store from "@/store";

import { OPEN_SLIDE } from "@/actions/slide";
import { SET_EVENT } from "@/actions/project/event";
import { SET_PROSPECT } from "@/actions/project/prospect";

export default {
    props: {
        /**
         * Event
         */
        event: {
            type: Object,
        },

        /**
         * Prospect associated to the event
         * We need it when we edit the event
         */
        prospect: {
            type: Object,
        },
    },

    methods: {
        /**
         * Edit a prospect event
         */
        edit() {
            store.commit(SET_PROSPECT, this.prospect);
            store.commit(SET_EVENT, this.event);

            // Open event edit slide
            store.commit(OPEN_SLIDE, "prospect-manage-events");
        },
    },

    computed: {
        /**
         * Color the event item
         * by the calendar text color and bg color
         */
        style() {
            return {
                // Text color
                color: this.event.calendar
                    ? this.event.calendar.color
                    : "#333333",
                // Background color
                backgroundColor: this.event.calendar
                    ? this.event.calendar.bgcolor
                    : "#CCCCCC",
            };
        },

        /**
         * Formatted date
         * that will be shown
         * in the event item
         * from the event.started_at field
         */
        date() {
            const date = dayjs(new Date(this.event.started_at)).format(
                "DD/MM/YYYY HH:mm"
            );

            if (date == "Invalid date") {
                return this.event.started_at;
            }

            return date;
        },

        /**
         * Event user
         */
        user() {
            if (!this.event.user) {
                return "";
            }

            return this.event.user.name;
        },

        /**
         * Icon
         * that will be shown
         * in the event item
         * in terms of
         * the type of the event calendar
         */
        icon() {
            if (!this.event.calendar) {
                return "fa fa-map-marker";
                // Physical calendar
            } else if (this.event.calendar.type == "physical") {
                return "fa fa-map-marker";
                // Phone calendar
            } else if (this.event.calendar.type == "telephone") {
                return "fa fa-phone";
                // GMeet calendar
            } else if (this.event.calendar.type == "hangout") {
                return "fa fa-video";
                // Task calendar
            } else if (this.event.calendar.type == "task") {
                return "fa fa-tasks";
                // Other
            } else {
                return "fa fa-calendar";
            }
        },
    },
};
</script>
