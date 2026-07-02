<template>
    <day v-if="agendaDisplayMode == 'day'" />
    <month v-else-if="agendaDisplayMode == 'month'" />
    <planning v-else-if="agendaDisplayMode == 'planning'" />
    <week v-else />
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import Day from "./agenda/day/Day.vue";
import Week from "./agenda/week/Week.vue";
import Month from "./agenda/month/Month.vue";
import Planning from "./agenda/planning/Planning.vue";

import {
    SET_AGENDA_LIST,
    SET_AGENDA_DISPLAY_MODE,
    SET_AGENDA_FILTER,
} from "@/actions/project/event";

export default {
    components: {
        Day,
        Week,
        Month,
        Planning,
    },

    beforeCreate() {
        const url = new URL(window.location.href);
        const searchParams = new URLSearchParams(url.search);
        const displayMode = searchParams.get("display-mode");

        if (displayMode) {
            store.commit(SET_AGENDA_DISPLAY_MODE, displayMode);
        } else {
            store.commit(SET_AGENDA_LIST, true);
        }

        if (deviceType() != "desktop") {
            store.commit(SET_AGENDA_FILTER, false);
        }
    },

    data() {
        return {
            displayType: "week",
        };
    },

    computed: {
        ...mapGetters(["agendaDisplayMode"]),
    },
};
</script>
