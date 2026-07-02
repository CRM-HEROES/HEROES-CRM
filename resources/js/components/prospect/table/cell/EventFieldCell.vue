<template>
    <div :class="['hc-event-field-cell']" @click.stop="edit">
        <span v-if="meta" v-text="event && event.meta && event.meta[field] ? event.meta[field] : ''"></span>
        <span v-if="field == 'name'" v-text="name"></span>
        <span v-else-if="field == 'description'" v-text="description"></span>
        <span v-else-if="field == 'location'" v-text="location"></span>
        <span v-else-if="field == 'drop_off_location'" v-text="dropOffLocation"></span>
        
        <span v-else-if="field == 'started_hour'" v-text="startedHour"></span>
        <span v-else-if="field == 'ended_hour'" v-text="endedHour"></span>
        <span v-else-if="field == 'started_date'" v-text="startedDate"></span>
        <span v-else-if="field == 'ended_date'" v-text="endedDate"></span>

        <span v-else-if="field == 'calendar' && calendar" v-text="calendar.name" class="hc-tag" :style="{color: calendar.color, backgroundColor: calendar.bgcolor}"></span>
        <span v-else-if="field == 'user' && user" v-text="user.name" class="hc-tag" style="color: #333333; background-color: #eeeeee;"></span>
        <span v-else-if="field == 'vehicle' && vehicle" v-text="vehicle.name" class="hc-tag" style="color: #333333; background-color: #eeeeee;"></span>
        <span v-else-if="field == 'prospect' && prospect" v-text="prospect.first_name" class="hc-tag" style="color: #333333; background-color: #eeeeee;"></span>
        <span v-else-if="field == 'order' && order" v-text="order.name" @click.stop="showOrder(order)" class="hc-tag" style="color: #333333; background-color: #eeeeee;"></span>
    </div>
</template>

<style>
.hc-event-field-cell {
    width: 100%;
    height: 100%;
    cursor: pointer;
}

.hc-event-field-cell:hover {
    background-color: #F5F5F5;
}

.hc-event-field-cell > span:not(.hc-tag) {
    display: inline-block;
    width: 100%;
    height: 100%;
    padding: 0 4px;
}
</style>

<script>
import store from "@/store";
import { mapGetters } from "vuex";

// Components
import EventRow from "./EventRow.vue";

import { OPEN_SLIDE } from "@/actions/slide";
import { SET_EVENT } from "@/actions/project/event";
import { SET_PROSPECT } from "@/actions/project/prospect";

import {
    SHOW_PROSPECT_ORDER,
    SET_PROSPECT_ORDER_TAB
} from "@/actions/project/prospect/order";

export default {
    components: {
        EventRow,
    },

    props: {
        /**
         * Prospect
         */
        prospect: {
            type: Object,
        },

        /**
         * Calendar
         */
        calendar: {
            type: Number,
            default: null,
        },

        /**
         * Field
         */
        field: {
            type: String,
            default: "name",
        },

        /**
         * Meta
         */
        meta: {
            type: Boolean,
            default: false,
        },
    },

    methods: {
        /**
         * Edit a prospect event
         */
        edit() {
            let event;

            if (this.event) {
                event = this.event;
            } else {
                const startDate = new Date();
                startDate.setMinutes(60);

                const endDate = new Date();
                endDate.setMinutes(120);

                event = {
                    prospect: this.prospect,
                    // user: this.$store.state.auth.user,
                    started_at: dateToString(startDate),
                    ended_at: dateToString(endDate),
                };
            }

            store.commit(SET_PROSPECT, this.prospect);
            store.commit(SET_EVENT, event);

            // Open event edit slide
            store.commit(OPEN_SLIDE, "prospect-manage-events");
        },
        

        /**
         *
         * @param {*} order
         */
         showOrder(order) {
            store.commit(SET_PROSPECT_ORDER_TAB, 0);
            store.commit(OPEN_SLIDE, "prospect-manage-orders");

            store.commit(SET_PROSPECT, this.prospect);
            store.dispatch(SHOW_PROSPECT_ORDER, order.id);
        },
    },

    computed: {
        ...mapGetters(["project", "canEvent"]),

        /**
         *
         */
        event() {
            if (!this.prospect.events) {
                return null;
            }

            const events = this.calendar
                ? this.prospect.events.filter(
                      (e) => e.calendar_id == this.calendar
                  )
                : [...this.prospect.events];

            if (events.length == 0) {
                return null;
            }

            events.sort((m1, m2) => (m2.started_at > m1.started_at ? 1 : -1));

            return events[0];
        },

        /**
         *
         */
        name() {
            if (!this.event) {
                return "";
            }

            return this.event.name;
        },

        /**
         *
         */
        description() {
            if (!this.event) {
                return "";
            }

            return this.event.description;
        },

        /**
         *
         */
         location() {
            if (!this.event) {
                return "";
            }

            return this.event.location;
        },

        /**
         *
         */
        dropOffLocation() {
            if (!this.event) {
                return "";
            }

            return this.event.drop_off_location;
        },

        /**
         *
         */
         calendar() {
            if (!this.event) {
                return null;
            }

            if (!this.event.calendar) {
                return null;
            }

            return this.event.calendar;
        },

        /**
         *
         */
         user() {
            if (!this.event) {
                return null;
            }

            if (!this.event.user) {
                return null;
            }

            return this.event.user;
        },

        /**
         *
         */
         vehicle() {
            if (!this.event) {
                return null;
            }

            if (!this.event.vehicle) {
                return null;
            }

            return this.event.vehicle;
        },

        /**
         *
         */
         prospect() {
            if (!this.event) {
                return null;
            }

            if (!this.event.prospect) {
                return null;
            }

            return this.event.prospect;
        },

        /**
         *
         */
         order() {
            if (!this.event) {
                return null;
            }

            if (!this.event.order) {
                return null;
            }

            return this.event.order;
        },

        /**
         *
         */
         startedHour() {
            if (!this.event) {
                return "";
            }

            if (!this.event.started_at) {
                return "";
            }

            return this.event.started_at.substring(11, 16);
        },

        /**
         *
         */
         endedHour() {
            if (!this.event) {
                return "";
            }

            if (!this.event.ended_at) {
                return "";
            }

            return this.event.ended_at.substring(11, 16);
        },

        /**
         *
         */
         startedDate() {
            if (!this.event) {
                return "";
            }

            if (!this.event.started_at) {
                return "";
            }

            const date = this.event.started_at.substring(0, 10);
            
            const formatedDate = dayjs(new Date(date)).format(
                "DD MMM YYYY"
            );

            if (formatedDate == "Invalid date") {
                return date;
            }

            return formatedDate;
        },

        /**
         *
         */
         endedDate() {
            if (!this.event) {
                return "";
            }

            if (!this.event.ended_at) {
                return "";
            }

            const date = this.event.ended_at.substring(0, 10);
            
            const formatedDate = dayjs(new Date(date)).format(
                "DD MMM YYYY"
            );

            if (formatedDate == "Invalid date") {
                return date;
            }

            return formatedDate;
        },

    },
};
</script>
