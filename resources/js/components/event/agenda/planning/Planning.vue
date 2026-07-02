<template>
    <div id="hc-agenda-events-list">
        <div id="hc-agenda-events-list-body">
            <event-row v-for="event in events" :key="event.id" :event="event" />
        </div>

        <card-menu-list>
            <div class="hc-agenda-events-list-date">
                <input type="date" v-model="start" />
            </div>
            <div class="hc-agenda-events-list-date">
                <input type="date" v-model="end" />
            </div>
            <div class="hc-agenda-table-footer-select">
                <select v-model="displayMode">
                    <option value="day">Jour</option>
                    <option value="week">Semaine</option>
                    <option value="month">Mois</option>
                    <option value="planning">Planning</option>
                </select>
            </div>
        </card-menu-list>
    </div>
</template>

<style>
#hc-agenda-events-list {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    background-color: white;
    top: 0;
    left: 0;
    z-index: 10;
    position: relative;
    border-top: 1px solid #ddd;
}

.hc-agenda-events-list-date {
    position: relative;
    width: unset !important;
    min-width: unset !important;
}

.hc-agenda-events-list-date > input {
    border: 1px solid #dddddd;
    background: none;
    padding: 3px 8px 3px 8px;
    border-radius: 7px;
    font-size: 12px;
    -webkit-appearance: none;
    -moz-appearance: none;
    height: 40px;
    width: auto;
}

#hc-agenda-events-list-body {
    width: 100%;
    flex: 1;
    overflow: auto;
}

@media (max-width: 767px) {
    #hc-agenda-events-list-header {
        justify-content: left;
    }

    #hc-agenda-events-list-body {
        padding: 10px;
    }

    #hc-agenda-events-list-close {
        border: 1px solid #ddd;
        background-color: white;
        line-height: 28px;
    }
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import EventService from "@/apis/project/event";

import EventRow from "./EventRow.vue";

import {
    ADD_EVENT_PARAMS,
    SET_AGENDA_DISPLAY_MODE,
} from "@/actions/project/event";

export default {
    components: {
        EventRow,
    },

    data() {
        return {
            events: [],
        };
    },

    mounted() {
        this.fetchEvents();
    },

    methods: {
        async fetchEvents() {
            const { data } = await EventService.get(this.project.slug, {
                params: {
                    filters: JSON.stringify({
                        startedAt: this.start,
                        endedAt: this.end,
                    }),
                },
            });

            this.events = data;
        },
    },

    computed: {
        ...mapGetters(["project", "eventsParamsValue", "agendaDisplayMode"]),

        /**
         * Start
         */
        start: {
            get: function () {
                const date = this.eventsParamsValue("startedAt");
                return date ? date : dateToString(new Date()).substring(0, 10);
            },
            set: function (value) {
                store.commit(ADD_EVENT_PARAMS, {
                    key: "startedAt",
                    value: value,
                });
                this.fetchEvents();
            },
        },

        /**
         * End
         */
        end: {
            get: function () {
                const date = this.eventsParamsValue("endedAt");
                return date ? date : dateToString(new Date()).substring(0, 10);
            },
            set: function (value) {
                store.commit(ADD_EVENT_PARAMS, {
                    key: "endedAt",
                    value: value,
                });
                this.fetchEvents();
            },
        },

        displayMode: {
            get() {
                return this.agendaDisplayMode;
            },
            set(value) {
                store.commit(SET_AGENDA_DISPLAY_MODE, value);
            },
        },
    },
};
</script>
