<template>
    <span class="hc-tag hc-event-row" :style="style">
        <i v-once :class="icon" :style="iconStyle"></i>{{ date
        }}<a class="hc-event-row-delete" @click.prevent.stop="remove"
            >&times;</a
        >
    </span>
</template>

<script>
import store from "@/store";

import { SET_PROSPECT } from "@/actions/project/prospect";

import { REMOVE_PROSPECT_EVENT } from "@/actions/project/prospect/event";

// window.currentDate = dateToString(new Date());

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
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                store.commit(SET_PROSPECT, this.prospect);
                store.dispatch(REMOVE_PROSPECT_EVENT, this.event.id);
            });
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
                color:
                    this.event.calendar && !this.event.done
                        ? this.event.calendar.color
                        : "#333333",
                // Background color
                backgroundColor:
                    this.event.calendar && !this.event.done
                        ? this.event.calendar.bgcolor
                        : "#EEEEEE",
            };
        },

        /**
         */
        iconStyle() {
            return {
                // Text color
                color:
                    this.event.calendar && this.event.done
                        ? this.event.calendar.bgcolor
                        : "inherit",
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
    },
};
</script>
