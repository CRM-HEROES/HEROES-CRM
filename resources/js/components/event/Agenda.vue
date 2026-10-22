<template>
    <template v-if="project && project.slug">
        <day v-if="agendaDisplayMode == 'day'" :key="'day-' + projectKey" />
        <month v-else-if="agendaDisplayMode == 'month'" :key="'month-' + projectKey" />
        <planning v-else-if="agendaDisplayMode == 'planning'" :key="'planning-' + projectKey" />
        <week v-else :key="'week-' + projectKey" />
    </template>
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
import { FETCH_GLOBAL_EVENTS } from "@/actions/event";

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

    watch: {
        selectedUsers: {
            deep: true,
            handler() {
                if (this.project?.slug && this.agendaOtherProjects && this.agendaDisplayMode !== "planning") {
                    store.dispatch(FETCH_GLOBAL_EVENTS);
                }
            },
        },
    },

    computed: {
        ...mapGetters(["agendaDisplayMode", "project", "agendaOtherProjects", "eventsParamsValue"]),

        selectedUsers() {
            return this.eventsParamsValue("withUsers");
        },

        projectKey() {
            return this.project?.id || this.project?.slug || "agenda";
        },
    },
};
</script>
