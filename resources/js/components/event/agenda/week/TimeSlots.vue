<template>
    <div class="hc-agenda-event-time-slots">
        <div class="hc-agenda-event-time-slots-header"></div>
        <div class="hc-agenda-event-time-slots-hour-blocs">
            <div
                class="hc-agenda-event-time-slots-hour-bloc"
                v-for="i in projectUserSettingsAgendaMaximumHour -
                projectUserSettingsAgendaMinimumHour"
                :key="i"
            >
                <time-slot
                    v-for="j in 4"
                    :key="j"
                    :hour="i + projectUserSettingsAgendaMinimumHour - 1"
                    :minute="(j - 1) * 15"
                />
            </div>
        </div>
    </div>
</template>

<style>
.hc-agenda-event-time-slots-header {
    display: flex;
    flex-direction: row;
    width: 100%;
    height: 34px;
    position: sticky;
    top: 0;
    z-index: 3;
    background-color: white;
}

.hc-agenda-event-time-slots-header:before {
    border: 1px solid #c0c0c0;
    bottom: 0px;
    content: "";
    display: inline-block;
    left: 0px;
    pointer-events: none;
    position: absolute;
    right: -1px;
    top: -1px;
}

.hc-agenda-event-time-slots {
    flex: 1;
    min-width: 50px;
    max-width: 50px;
    position: sticky;
    left: 0;
    z-index: 3;
    display: inline-block;
    vertical-align: top;
}

.agenda-list .hc-agenda-event-time-slots {
    display: none;
}

.hc-agenda-event-time-slots-hour-blocs {
    width: 100%;
    display: flex;
    flex-direction: column;
}

.hc-agenda-event-time-slots-hour-bloc {
    display: flex;
    flex-direction: column;
    position: relative;
}

.hc-agenda-event-time-slots-hour-bloc:before {
    border: 1px solid #ddd;
    bottom: -1px;
    content: "";
    display: inline-block;
    left: -1px;
    pointer-events: none;
    position: absolute;
    right: -1px;
    top: 0px;
    z-index: 2;
}

.hc-agenda-events {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    pointer-events: none;
    padding: 0 5px;
}

.hc-agenda-events-list {
    position: relative;
    width: 100%;
    height: 100%;
}
</style>

<script>
import { mapGetters } from "vuex";

import TimeSlot from "./TimeSlot.vue";

export default {
    components: {
        TimeSlot,
    },

    props: {
        date: {
            type: String,
        },
    },

    methods: {},

    computed: {
        ...mapGetters([
            "projectUserSettingsAgendaMinimumHour",
            "projectUserSettingsAgendaMaximumHour",
        ]),
    },
};
</script>
