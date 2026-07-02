<template>
    <card-menu-list>
        <card-menu
            icon="fa fa-filter"
            label="Afficher/Masquer les filtres"
            @click="toggleAgendaFilter"
            :color="agendaList ? '#1a73e8' : '#484848'"
        />
        <card-menu
            icon="fa fa-plus icon-green"
            label="Nouveau RDV"
            @click="addEvent"
        />
        <card-menu
            icon="fa fa-road icon-garnet"
            label="Itinéraire"
            @click="showDirections"
        />
        <div id="hc-agenda-table-footer-date-select">
            <input type="date" v-model="currentDate" />
        </div>
        <div class="hc-agenda-table-footer-select">
            <select v-model="displayMode">
                <option value="day">Jour</option>
                <option value="week">Semaine</option>
                <option value="month">Mois</option>
                <option value="planning">Planning</option>
            </select>
        </div>
        <card-menu
            icon="fa fa-align-left"
            label="Affichage liste"
            @click="toggleAgendaList"
            :color="agendaList ? '#1a73e8' : '#484848'"
        />
        <card-menu
            v-if="can('all.prospect.ocr')"
            icon="fa fa-qrcode"
            :label="$t('prospect.table.footer.scan')"
            @click="ocr"
        />
    </card-menu-list>
</template>

<style>
#hc-agenda-table-footer-date-select {
    position: relative;
    width: unset !important;
    min-width: unset !important;
}

#hc-agenda-table-footer-date-select > input {
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
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import {
    FETCH_EVENTS,
    SET_EVENT,
    ADD_EVENT_PARAMS,
    SET_AGENDA_FILTER,
    SET_AGENDA_LIST,
    SHOW_AGENDA_TABLE,
    SET_AGENDA_DATE,
    SET_AGENDA_DISPLAY_MODE,
} from "@/actions/project/event";

import { OPEN_MODAL } from "@/actions/modal";
import { OPEN_SLIDE } from "@/actions/slide";

export default {
    methods: {
        /**
         * Previous week
         */
        previousPage() {
            let date = new Date(this.paramsStartedAt);
            date.setDate(date.getDate() - 7);

            this.setDate(date);
        },

        /**
         *
         */
        addEvent() {
            const startDate = new Date();
            startDate.setMinutes(60);

            const endDate = new Date();
            endDate.setMinutes(120);

            store.commit(SET_EVENT, {
                started_at: dateToString(startDate),
                ended_at: dateToString(endDate),
            });

            store.commit(OPEN_SLIDE, "prospect-manage-events");
        },

        /**
         *
         */
        showDirections() {
            store.commit(OPEN_SLIDE, "user-daily-direction");
        },

        /**
         * Next week
         */
        nextPage() {
            let date = new Date(this.paramsStartedAt);
            date.setDate(date.getDate() + 7);

            this.setDate(date);
        },

        setDate(date) {
            // Start date
            let startDate = new Date(date);

            // End date
            let endDate = new Date(startDate);
            endDate.setDate(startDate.getDate() + 6);

            store.commit(ADD_EVENT_PARAMS, {
                key: "startedAt",
                value: dateToString(startDate).substring(0, 10),
            });

            store.commit(ADD_EVENT_PARAMS, {
                key: "endedAt",
                value: dateToString(endDate).substring(0, 10),
            });

            store.dispatch(FETCH_EVENTS);
        },

        toggleAgendaFilter() {
            store.commit(SET_AGENDA_FILTER, !this.agendaFilter);
        },

        toggleAgendaList() {
            store.commit(SET_AGENDA_LIST, !this.agendaList);
        },

        showAgendaTable() {
            store.commit(SHOW_AGENDA_TABLE, true);
        },

        /**
         * Scan CIN
         */
        ocr() {
            store.commit(OPEN_MODAL, "prospects-table-ocr");
        },
    },

    watch: {
        agendaDate() {
            this.setDate(new Date(this.agendaDate));
        },
    },

    computed: {
        ...mapGetters([
            "eventsParamsValue",
            "eventsParamExists",
            "agendaFilter",
            "agendaList",
            "agendaDisplayMode",
            "agendaDate",
            "can",
        ]),

        /**
         *
         */
        paramsStartedAt() {
            return this.eventsParamsValue("startedAt");
        },

        /**
         *
         */
        paramsEndedAt() {
            return this.eventsParamsValue("endedAt");
        },

        /**
         *
         */
        currentDate: {
            get() {
                return this.agendaDate;
            },
            set(value) {
                store.commit(SET_AGENDA_DATE, value);
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
