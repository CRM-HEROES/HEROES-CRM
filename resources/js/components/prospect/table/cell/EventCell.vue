<template>
    <div :class="['hc-event-cell', open ? 'open' : '']">
        <a
            class="hc-tag hc-event-row hc-event-row-add"
            v-text="
                project.slug == 'professionnel'
                    ? 'Nouvelle mission'
                    : $t('prospect.table.cell.event.add')
            "
            v-if="!prospect.processed_at"
        ></a>
        <span
            class="hc-event-cell-count"
            v-if="events.length > 1"
            v-text="events.length"
        ></span>
        <event-row
            v-for="event in events"
            :key="event.id"
            :event="event"
            :prospect="prospect"
            @click.stop="edit(event)"
        />
        <a
            class="hc-event-cell-dropdown fa fa-caret-down"
            @click.prevent.stop="open = !open"
        ></a>
    </div>
</template>

<script>
import store from "@/store";
import { mapGetters } from "vuex";

// Components
import EventRow from "./EventRow.vue";

import { OPEN_SLIDE } from "@/actions/slide";
import { SET_EVENT } from "@/actions/project/event";
import { SET_PROSPECT } from "@/actions/project/prospect";

export default {
    components: {
        EventRow,
    },

    props: {
        /**
         * Prospect
         */
        prospect: {
            type: Object,
        },

        /**
         * Calendar
         */
        calendar: {
            type: Number,
            default: null,
        },
    },

    data() {
        return {
            open: false,
        };
    },

    methods: {
        /**
         * Edit a prospect event
         */
        edit(event) {
            store.commit(SET_PROSPECT, this.prospect);
            store.commit(SET_EVENT, event);

            // Open event edit slide
            store.commit(OPEN_SLIDE, "prospect-manage-events");
            this.open = false;
        },
    },

    computed: {
        ...mapGetters(["project"]),

        /**
         */
        events() {
            if (!this.prospect.events) {
                return [];
            }

            const events = this.calendar
                ? this.prospect.events.filter(
                      (e) => e.calendar_id == this.calendar
                  )
                : [...this.prospect.events];

            events.sort((m1, m2) => (m2.started_at > m1.started_at ? 1 : -1));

            return events;
        },
    },
};
</script>
