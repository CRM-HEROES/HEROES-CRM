<template>
    <bloc icon="fa fa-calendar" :name="$t('project.profile.blocs.calendars')">
        <template #options>
            <icon
                tag="a"
                class="fa fa-plus"
                @click.prevent.stop="addCalendar"
            />
            <icon v-if="calendars.length > 0" class="fa fa-caret-down" />
        </template>
        <template #body>
            <div
                style="padding: 10px 10px; float: left; width: 100%"
                v-if="calendars.length > 0"
            >
                <calendar-row
                    v-for="(calendar, i) in calendars"
                    :key="calendar.id"
                    :calendar="calendar"
                />
            </div>
        </template>
    </bloc>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";

// Components
import Bloc from "@/components/project/profile/blocs/Bloc.vue";
import CalendarRow from "./CalendarRow.vue";

export default {
    components: {
        Bloc,
        CalendarRow,
    },

    methods: {
        /**
         * Add calendar
         * See: @/components/calendar/add/Modal.vue
         */
        addCalendar() {
            store.commit(OPEN_MODAL, "calendar-add");
        },
    },

    computed: {
        ...mapGetters(["project", "calendars"]),
    },
};
</script>
