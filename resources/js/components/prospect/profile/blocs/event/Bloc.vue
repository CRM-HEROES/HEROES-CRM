<template>
    <bloc icon="fa fa-calendar" :name="$t('prospect.profile.bloc.events')">
        <template #options>
            <icon
                tag="a"
                class="fa fa-plus"
                @click.prevent.stop="manageEvents"
            />
        </template>
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
                <event-row
                    v-for="(event, i) in prospectEvents"
                    :key="event.id"
                    :event="event"
                    :prospect="prospect"
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
import { SET_PROSPECT } from "@/actions/project/prospect";
import { SET_EVENT } from "@/actions/project/event";

// Components
import Bloc from "@/components/prospect/profile/blocs/Bloc.vue";
import EventRow from "./EventRow.vue";

export default {
    components: {
        Bloc,
        EventRow,
    },

    data() {
        return {
            shownEventsCount: 5,
        };
    },

    methods: {
        showMoreEvents() {
            this.shownEventsCount += 20;
        },

        /**
         * Manage prospect events
         * See: @/components/prospect/event/Slide.vue
         */
        manageEvents() {
            const startDate = new Date();
            startDate.setMinutes(60);

            const endDate = new Date();
            endDate.setMinutes(90);

            store.commit(SET_PROSPECT, this.prospect);
            store.commit(SET_EVENT, {
                prospect: this.prospect,
                user: this.$store.state.auth.user,
                started_at: dateToString(startDate),
                ended_at: dateToString(endDate),
            });
            store.commit(OPEN_SLIDE, "prospect-manage-events");
        },
    },

    computed: {
        ...mapGetters(["project", "prospect", "prospectEvents"]),
    },
};
</script>
