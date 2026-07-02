<template>
    <div class="hc-flex-column" style="height: 100%">
        <div class="hc-flex-column hc-flex-1" style="overflow: hidden">
            <search v-model="keyword" />
            <item-list class="hc-flex-1" padding="5px" style="overflow: auto">
                <item tag="label">
                    <icon class="fa fa-check-square" />
                    <div class="hc-item-main-content" v-text="$t('all')"></div>

                    <checkbox v-model="all" />
                </item>

                <calendar-row
                    v-for="calendar in calendars"
                    :key="calendar.id"
                    :calendar="calendar"
                    v-model="selectedCalendars"
                />

                <event-row
                    v-for="event in events"
                    :key="event.id"
                    :event="event"
                    v-model="selectedEvents"
                />
                <loading :loading="removing" />
            </item-list>
            <buttons>
                <button
                    @click.prevent="restore"
                    v-text="$t('restore')"
                ></button>
                <button
                    @click.prevent="remove"
                    class="hc-button-danger"
                    v-text="$t('delete')"
                ></button>
            </buttons>
        </div>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import TrashService from "@/apis/project/trash";
import CalendarRow from "./CalendarRow.vue";
import EventRow from "./EventRow.vue";

export default {
    components: {
        CalendarRow,
        EventRow,
    },

    data() {
        return {
            keyword: "",
            calendars: [],
            events: [],
            selectedCalendars: [],
            selectedEvents: [],
            removing: false,
        };
    },

    mounted() {
        this.fetch();
    },

    methods: {
        async remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                try {
                    this.removing = true;

                    await TrashService.bulkDestroy(this.project.slug, {
                        calendars: this.selectedCalendars,
                        events: this.selectedEvents,
                    });
                    this.fetch();
                } finally {
                    this.removing = false;
                }
            });
        },

        async restore() {
            try {
                await TrashService.bulkRestore(this.project.slug, {
                    calendars: this.selectedCalendars,
                    events: this.selectedEvents,
                });
                this.fetch();
            } finally {
            }
        },

        /**
         * Fetch trashed calendars and events
         */
        async fetch() {
            this.fetchCalendars();
            this.fetchEvents();
        },

        /**
         * Fetch trashed calendars
         */
        async fetchCalendars() {
            const params = {};

            if (this.keyword) {
                params.query = this.keyword;
            }

            const { data } = await TrashService.calendar(this.project.slug, {
                params,
            });

            this.calendars = data.data;
        },

        /**
         * Fetch trashed events
         */
        async fetchEvents() {
            const params = {};

            if (this.keyword) {
                params.query = this.keyword;
            }

            const { data } = await TrashService.event(this.project.slug, {
                params,
            });

            this.events = data.data;
        },
    },

    watch: {
        keyword() {
            this.fetch();
        },
    },

    computed: {
        ...mapGetters(["project"]),

        all: {
            get() {
                return (
                    this.selectedCalendars.length == this.calendars.length &&
                    this.selectedEvents.length == this.events.length
                );
            },
            set(value) {
                this.selectedCalendars = value
                    ? this.calendars.map((calendar) => calendar.id)
                    : [];
                this.selectedEvents = value
                    ? this.events.map((event) => event.id)
                    : [];
            },
        },
    },
};
</script>
