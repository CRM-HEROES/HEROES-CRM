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
        <div class="hc-agenda-table-footer-select" id="hc-agenda-table-date">
            <input type="date" v-model="currentDate" />
            <label v-text="dateLabel"></label>
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
            icon="fa fa-list"
            label="Liste des RDVs"
            @click="showAgendaTable"
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
.hc-agenda-table-footer-select {
    position: relative;
    width: unset !important;
    min-width: unset !important;
}

.hc-agenda-table-footer-select > input,
.hc-agenda-table-footer-select > select {
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

#hc-agenda-table-date {
    position: relative;
}

#hc-agenda-table-date input::-webkit-calendar-picker-indicator {
    background: transparent;
    bottom: 0;
    color: transparent;
    cursor: pointer;
    height: auto;
    left: 0;
    position: absolute;
    right: 0;
    top: 0;
    width: auto;
}

#hc-agenda-table-date > label {
    border: 1px solid #ddd;
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    font-size: 15px;
    pointer-events: none;
    background-color: white;
    border-radius: 7px;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import {
    FETCH_EVENTS,
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

        currentDate: {
            get() {
                return this.agendaDate;
            },
            set(value) {
                store.commit(SET_AGENDA_DATE, value);
            },
        },

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

        dateLabel() {
            return dayjs(this.currentDate).format("MMM YYYY");
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
