<template>
    <bloc
        icon="fa fa-calendar icon-purple"
        :name="$t('user.profile.blocs.calendars')"
    >
        <template #options>
            <icon
                tag="a"
                class="fa fa-plus icon-blue"
                @click.prevent.stop="addCalendar"
            />
        </template>
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
                <calendar-row
                    v-for="(calendar, i) in userCalendars"
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
import { OPEN_SLIDE } from "@/actions/slide";

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
            store.commit(OPEN_SLIDE, "user-manage-calendars");
        },
    },

    computed: {
        ...mapGetters(["project", "userCalendars"]),
    },
};
</script>
