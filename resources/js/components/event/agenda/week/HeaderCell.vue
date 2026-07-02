<template>
    <div class="hc-agenda-event-header-cell">
        <div class="hc-agenda-event-header-cell-date" v-text="dateLabel"></div>
        <div
            v-if="events.length"
            class="hc-agenda-event-header-cell-events-count"
            v-text="events.length"
        ></div>
        <icon
            v-if="fetchingDirection || Object.keys(dailyDirections).length > 0"
            class="fa fa-route"
            tag="a"
            :size="34"
            :style="{
                color: this.directionColor,
            }"
            @click.prevent="showDirection"
        >
            <loading :loading="fetchingDirection" />
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

// Actions
import { SET_EVENT } from "@/actions/project/event";

import { OPEN_SLIDE } from "@/actions/slide";
import { TOGGLE_USER_DAILY_DIRECTION } from "@/actions/project/user/daily-direction";

import {
    SET_PROSPECT,
    UPDATE_SELECTED_PROSPECTS,
} from "@/actions/project/prospect";

export default {
    props: {
        date: {
            type: String,
        },
        events: {
            type: Array,
        },
        dailyDirections: {
            type: Object,
        },
        fetchingDirection: {
            type: Object,
        },
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

        showDirection() {
            for (let i in this.dailyDirections) {
                store.commit(
                    TOGGLE_USER_DAILY_DIRECTION,
                    this.dailyDirections[i]
                );
            }
            store.commit(OPEN_SLIDE, "user-daily-direction");
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

    computed: {
        ...mapGetters(["project", "directionFor"]),

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
