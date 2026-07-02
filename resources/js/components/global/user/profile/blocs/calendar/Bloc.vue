<template>
    <bloc
        icon="fa fa-calendar icon-purple"
        :name="$t('user.profile.blocs.calendars')"
    >
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

// Components
import Bloc from "@/components/project/profile/blocs/Bloc.vue";
import CalendarRow from "./CalendarRow.vue";

export default {
    components: {
        Bloc,
        CalendarRow,
    },

    computed: {
        ...mapGetters(["globalUser"]),

        userCalendars() {
            return Array.isArray(this.globalUser.calendars)
                ? this.globalUser.calendars
                : [];
        },
    },
};
</script>
