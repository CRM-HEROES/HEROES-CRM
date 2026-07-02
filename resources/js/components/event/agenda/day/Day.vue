<template>
    <div
        id="hc-user-agenda-day"
        :class="[
            draggedEvent || resizedEvent ? 'dragged-event' : '',
            agendaList ? 'agenda-list' : '',
            agendaFilter ? 'agenda-filter' : '',
        ]"
    >
        <div id="hc-user-agenda-day-body">
            <users />

            <div id="hc-user-agenda-day-table">
                <div id="hc-user-agenda-day-columns">
                    <time-slots />
                    <column :date="currentDate" />
                </div>
            </div>
        </div>

        <card-menus />
        <user-daily-direction-slide />
        <ocr-modal />
    </div>
</template>

<style>
#hc-user-agenda-day {
    border-top: 1px solid silver;
    display: flex;
    flex-direction: column;
    width: 100%;
    height: 100%;
    user-select: none;
    position: relative;
}

#hc-user-agenda-day-body {
    display: flex;
    flex-direction: row;
    flex: 1;
    width: 100%;
    overflow: hidden;
}

#hc-user-agenda-day-table {
    width: 100%;
    height: 100%;
    overflow: auto;
    position: relative;
    background: white;
    display: flex;
    flex-direction: row;
}

#hc-user-agenda-day-columns {
    width: 100%;
    display: flex;
    flex-direction: row;
    overflow-x: hidden;
}

#hc-user-agenda-day-columns .hc-user-agenda-week-column {
    flex: 1;
    width: auto !important;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import {
    SET_EVENTS,
    FETCH_EVENTS,
    ADD_EVENT_PARAMS,
    SET_NEW_EVENT,
    SET_DRAGGED_EVENT,
    SET_RESIZED_EVENT,
    SET_EVENT_PARAMS,
} from "@/actions/project/event";

import Users from "../../filters/Users.vue";
import Column from "../week/Column.vue";
import TimeSlots from "../week/TimeSlots.vue";
import CardMenus from "../week/CardMenus.vue";
import UserDailyDirectionSlide from "../../slides/daily-direction/Slide.vue";
import OcrModal from "@/components/ocr/Modal.vue";

export default {
    components: {
        Users,
        Column,
        TimeSlots,
        CardMenus,
        UserDailyDirectionSlide,
        OcrModal,
    },

    data() {
        return {
            currentDate: null,
        };
    },

    created() {
        this.currentDate = new Date();
        store.commit(SET_EVENTS, []);
        store.commit(SET_NEW_EVENT, null);
        store.commit(SET_DRAGGED_EVENT, null);
        store.commit(SET_RESIZED_EVENT, null);

        this.updateParamsFromUrl();
    },

    mounted() {},

    methods: {
        updateParamsFromUrl() {
            const url = new URL(window.location.href);
            const searchParams = new URLSearchParams(url.search);
            const filters = {};

            searchParams.forEach(function (value, param) {
                if (param == "filters") {
                    try {
                        value = JSON.parse(value);

                        for (let key in value) {
                            filters[key] = value[key];
                        }
                    } catch (e) {}
                }
            });

            store.commit(SET_EVENT_PARAMS, filters);

            store.dispatch(FETCH_EVENTS);
        },
    },

    watch: {
        async agendaDate() {
            store.commit(ADD_EVENT_PARAMS, {
                key: "startedAt",
                value: this.agendaDate,
            });
            store.commit(ADD_EVENT_PARAMS, {
                key: "endedAt",
                value: this.agendaDate,
            });

            store.dispatch(FETCH_EVENTS);
        },
    },

    computed: {
        ...mapGetters([
            "draggedEvent",
            "resizedEvent",
            "eventsParamsValue",
            "eventsParamExists",
            "agendaFilter",
            "agendaList",
            "agendaWeekEnd",
            "agendaDate",
        ]),

        currentDate() {
            return new Date(this.agendaDate);
        },
    },
};
</script>
