<template>
    <div class="hc-stat-chart-event hc-flex-column" :style="style">
        <item>
            <icon :style="iconStyle" :class="icon" :icon-size="12" />
            <div
                class="hc-item-main-content hc-stat-chart-event-calendar"
                v-text="name"
            ></div>
            <div class="hc-stat-chart-event-date" v-text="date"></div>
            <icon color="#AAAAAA" :class="statusIcon" :icon-size="12" />
        </item>
    </div>
</template>

<style>
.hc-stat-chart-event {
    border-radius: 5px;
    margin: 1px 0;
    cursor: pointer;
}

.hc-stat-chart-event .hc-item {
    color: inherit;
}

.hc-stat-chart-event .hc-item:hover {
    background-color: transparent;
}

.hc-stat-chart-event-user {
    padding: 8px 0px 2px 12px;
    font-size: 11px;
    display: flex;
    flex-direction: row;
}

.hc-stat-chart-event-user > i {
    margin-right: 5px;
}

.hc-stat-chart-event-user > span {
    flex: 1;
}

.hc-stat-chart-event-calendar {
    padding-left: 0;
}

.hc-stat-chart-event-date {
    font-size: 13px;
    font-weight: 600;
}
</style>

<script>
import store from "@/store";

import { SET_EVENT } from "@/actions/project/event";

import { SET_PROSPECT } from "@/actions/project/prospect";
import {
    SET_PROSPECT_ORDER,
    SET_PROSPECT_ORDER_TAB,
} from "@/actions/project/prospect/order";
import { OPEN_SLIDE } from "@/actions/slide";

export default {
    props: {
        event: {
            type: Object,
        },
        prospect: {
            type: Object,
        },
    },

    data() {
        return {};
    },

    methods: {
        edit() {
            // store.commit(SET_PROSPECT, null);
            store.commit(SET_EVENT, this.event);
            store.commit(OPEN_SLIDE, "prospect-manage-events");
        },

        /**
         * Edit a prospect order
         */
        showOrder() {
            store.commit(SET_PROSPECT, this.prospect);
            store.commit(SET_PROSPECT_ORDER, this.event.order);

            // Open event edit slide
            store.commit(SET_PROSPECT_ORDER_TAB, 0);
            store.commit(OPEN_SLIDE, "prospect-manage-orders");
        },

        action() {
            if (this.event.calendar.type == "physical") {
                window.open(this.gmapURL, "_blank");
            } else if (this.event.calendar.type == "telephone") {
                store.commit(OPEN_SLIDE, "prospect-manage-interactions");
            } else if (this.event.calendar.type == "hangout" && this.hangout) {
                window.open(this.hangout, "_blank");
            }
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
                /*color: this.event.calendar
                    ? this.event.calendar.color
                    : "#333333",*/
                // Background color
                backgroundColor:
                    (this.event.calendar
                        ? this.event.calendar.bgcolor
                        : "#EEEEEE") + "11",
            };
        },

        /**
         */
        iconStyle() {
            return {
                // Text color
                color: this.event.calendar
                    ? this.event.calendar.bgcolor
                    : "#EEEEEE",
            };
        },

        /**
         * Is past event
         */
        isPastEvent() {
            return this.event.started_at < currentDate;
        },

        /**
         * Event name
         */
        name() {
            return this.event.name;
        },

        /**
         * Formatted date
         * that will be shown
         * in the event item
         * from the event.started_at field
         */
        date() {
            const date = dayjs(new Date(this.event.started_at)).format("HH:mm");

            if (date == "Invalid date") {
                return this.event.started_at;
            }

            return date;
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

        /**
         * Icon
         * that will be shown
         * in the event item
         * in terms of
         * the event status
         */
        statusIcon() {
            // Done
            if (this.event.done) {
                return "fa fa-check";
                // Confirmed
            } else if (this.event.confirmed) {
                return "fa fa-thumbs-up";
                // Other
            } else {
                return "fa fa-calendar";
            }
        },

        gmapURL() {
            return (
                "https://www.google.com/maps/search/?api=1&query=" +
                encodeURI(this.event.location)
            );
        },

        /**
         *
         */
        hangout() {
            if (
                this.event.google_accounts &&
                this.event.google_accounts.length > 0 &&
                this.event.google_accounts[0].pivot &&
                this.event.google_accounts[0].pivot.hangout
            ) {
                return this.event.google_accounts[0].pivot.hangout;
            }

            return null;
        },
    },
};
</script>
