<template>
    <div class="hc-agenda-event-header-cell">
        <div class="hc-agenda-event-header-cell-date" v-text="dateLabel"></div>
        <div
            v-if="events.length"
            class="hc-agenda-event-header-cell-events-count"
            v-text="events.length"
        ></div>
        <icon
            v-if="dayPhysicalEvents.length > 0"
            class="fa fa-route"
            tag="a"
            :size="34"
            :style="{
                color: this.directionColor,
            }"
            @click.prevent="showDirection"
        >
            <loading :loading="fetchingDirection || fetchingDirectionDetails" />
        </icon>
        <icon
            v-if="prospects.length > 0"
            class="fa fa-comment icon-purple"
            tag="a"
            :size="34"
            @click.prevent="bulkSms"
        />
        <icon
            class="fa fa-plus icon-green"
            tag="a"
            :size="34"
            @click.prevent="addEvent"
        />
    </div>
</template>

<style>
.hc-agenda-event-header-cell {
    display: flex;
    flex-direction: row;
    width: 100%;
    height: 34px;
    position: sticky;
    top: 0;
    z-index: 2;
    background-color: white;
}
.hc-agenda-event-header-cell:before {
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
.hc-agenda-event-header-cell-date {
    padding: 0 15px;
    flex: 1;
    font-size: 11px;
    line-height: 34px;
    text-transform: uppercase;
}

.hc-agenda-event-header-cell-events-count {
    font-size: 12px;
    line-height: 34px;
    padding: 0 4px;
    font-weight: 600;
}

@media (max-width: 767px) {
    .agenda-list .hc-agenda-event-header-cell:before {
        border: none;
        border-bottom: 1px solid #eeeeee;
    }
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";
import {
    dailyDirectionCacheScope,
    dailyDirectionEventsSignature,
} from "@/utils/event-daily-direction-cache";

// Actions
import { SET_EVENT, FETCH_EVENT_USER_DAILY_DIRECTION } from "@/actions/project/event";

import { OPEN_SLIDE } from "@/actions/slide";
import { TOGGLE_USER_DAILY_DIRECTION } from "@/actions/project/user/daily-direction";

import {
    SET_PROSPECT,
    UPDATE_SELECTED_PROSPECTS,
} from "@/actions/project/prospect";

export default {
    props: {
        date: {
            type: [Date, String],
        },
        events: {
            type: Array,
        },
        dailyDirections: {
            type: Object,
        },
        fetchingDirection: {
            type: Boolean,
        },
    },

    data() {
        return {
            fetchingDirectionDetails: false,
            directionDetailsRequestId: 0,
        };
    },

    methods: {
        addEvent() {
            const date = dateToString(this.date).substring(0, 10);

            store.commit(SET_PROSPECT, null);
            store.commit(SET_EVENT, {
                started_at: date + " 08:00:00",
                ended_at: date + " 09:00:00",
            });

            // Open event edit slide
            store.commit(OPEN_SLIDE, "prospect-manage-events");
        },

        async showDirection() {
            if (this.fetchingDirectionDetails) return;
            const requestId = ++this.directionDetailsRequestId;
            const signature = this.directionDetailsSignature;
            const date = this.dateString;
            const users = [...new Set(this.dayPhysicalEvents.map((event) => event.user_id))];
            this.fetchingDirectionDetails = users.length > 0;
            store.commit(OPEN_SLIDE, "user-daily-direction");

            try {
                await Promise.allSettled(users.map(async (userId) => {
                    const selected = this.directionFor(userId, date);
                    if (selected) {
                        store.commit(TOGGLE_USER_DAILY_DIRECTION, selected);
                        return;
                    }

                    const data = await store.dispatch(FETCH_EVENT_USER_DAILY_DIRECTION, {
                        user: { id: userId },
                        direction_at: date,
                        events: this.dayPhysicalEvents.filter((event) => event.user_id == userId),
                    });
                    if (requestId === this.directionDetailsRequestId &&
                        signature === this.directionDetailsSignature && data?.direction &&
                        !this.directionFor(userId, date)) {
                        store.commit(TOGGLE_USER_DAILY_DIRECTION, data);
                    }
                }));
            } finally {
                if (requestId === this.directionDetailsRequestId) this.fetchingDirectionDetails = false;
            }
        },

        bulkSms() {
            store.commit(SET_PROSPECT, null);
            store.commit(
                UPDATE_SELECTED_PROSPECTS,
                this.prospects.map((p) => p.id)
            );
            store.commit(OPEN_SLIDE, "prospect-manage-sms");
        },
    },

    watch: {
        directionDetailsSignature() {
            ++this.directionDetailsRequestId;
            this.fetchingDirectionDetails = false;
        },
    },

    beforeUnmount() {
        ++this.directionDetailsRequestId;
    },

    computed: {
        ...mapGetters(["project", "directionFor"]),

        dateString() {
            return dateToString(this.date).substring(0, 10);
        },

        dayPhysicalEvents() {
            return this.events.filter((event) => event.calendar?.type === "physical" && event.location);
        },

        directionDetailsSignature() {
            return JSON.stringify([dailyDirectionCacheScope(this.$store.state), this.dateString,
                dailyDirectionEventsSignature(this.dayPhysicalEvents)]);
        },

        dateLabel() {
            return dayjs(this.date).format("ddd, DD MMM YYYY");
        },

        directionColor() {
            const keys = Object.keys(this.dailyDirections);

            if (keys.length == 0) {
                return "#AAAAAA";
            }

            const dir = this.directionFor(
                this.dailyDirections[keys[0]].user_id,
                this.dailyDirections[keys[0]].direction_at
            );

            return dir ? dir.color : "#AAAAAA";
        },

        prospects() {
            return this.events.map((e) => e.prospect).filter((p) => p);
        },
    },
};
</script>
