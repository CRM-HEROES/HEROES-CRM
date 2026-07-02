<template>
    <div
        class="hc-prospect-profile-event hc-flex-column"
        :style="style"
        @click="edit"
    >
        <div class="hc-prospect-profile-event-user">
            <i class="fa fa-user"></i>
            <span v-if="event.user" v-text="event.user.name"></span>
            <i v-if="event.done" class="fa fa-check-circle"></i>
            <i v-if="event.confirmed" class="fa fa-thumbs-up"></i>
        </div>
        <item>
            <icon :style="iconStyle" :class="statusIcon" />
            <div class="hc-item-main-content hc-flex-column">
                <div
                    class="hc-prospect-profile-event-calendar"
                    v-text="event.calendar.name"
                ></div>
                <div class="hc-prospect-profile-event-date" v-text="date"></div>
            </div>
            <icon
                v-if="event.order"
                :style="iconStyle"
                class="fa fa-shopping-cart"
                v-tooltip="event.order.name"
                @click.prevent.stop="showOrder"
            />
            <icon
                :style="iconStyle"
                :class="icon"
                @click.prevent.stop="action"
            />
        </item>
    </div>
</template>

<style>
.hc-prospect-profile-event {
    border-radius: 5px;
    padding-bottom: 7px;
    margin: 2px 0;
    cursor: pointer;
}

.hc-prospect-profile-event .hc-item {
    color: inherit;
}

.hc-prospect-profile-event .hc-item:hover {
    background-color: transparent;
}

.hc-prospect-profile-event-user {
    padding: 8px 0px 2px 12px;
    font-size: 11px;
    display: flex;
    flex-direction: row;
    align-items: center;
}

.hc-prospect-profile-event-user > i {
    margin-right: 5px;
}

.hc-prospect-profile-event-user > span {
    flex: 1;
}

.hc-prospect-profile-event-date {
    opacity: 0.7;
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
                color: this.event.calendar
                    ? this.event.calendar.color
                    : "#333333",
                // Background color
                backgroundColor: this.event.calendar
                    ? this.event.calendar.bgcolor
                    : "#EEEEEE",
            };
        },

        /**
         */
        iconStyle() {
            return {
                // Text color
                color: "inherit",
            };
        },

        /**
         * Is past event
         */
        isPastEvent() {
            return this.event.started_at < currentDate;
        },

        /**
         * Formatted date
         * that will be shown
         * in the event item
         * from the event.started_at field
         */
        date() {
            const date = dayjs(new Date(this.event.started_at)).format(
                "DD MMM YYYY HH:mm"
            );

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
