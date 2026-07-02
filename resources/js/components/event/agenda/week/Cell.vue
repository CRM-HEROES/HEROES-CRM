<template>
    <div
        :class="['hc-agenda-event-cell', acceptDrop ? 'accept-drop' : '']"
        @mousedown="dragStart"
        @mouseenter="dragEnter"
        @mouseleave="dragLeave"
        @mouseup="dragEnd"
        @dragover.prevent
    >
        <span v-text="label"></span>
    </div>
</template>

<style>
.hc-agenda-event-cell {
    height: 20px;
    width: 100%;
    position: relative;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.hc-agenda-event-cell > span {
    display: none;
    color: #999;
    font-size: 11px;
    position: absolute;
}

.hc-agenda-event-cell:hover > span {
    display: inline;
}

.hc-agenda-event-cell:hover {
    background-color: #eee;
}

.hc-agenda-event-cell:before {
    border: 1px solid #f7f7f7;
    bottom: 0px;
    content: "";
    display: inline-block;
    left: -1px;
    pointer-events: none;
    position: absolute;
    right: 0px;
    top: -1px;
}

.week-end .hc-agenda-event-cell {
    background-color: rgba(255, 143, 0, 0.04);
}

@media (max-width: 767px) {
    .hc-agenda-event-cell {
        height: 30px;
    }
}
/*
.hc-agenda-event-cell.accept-drop {
    z-index: 2;
}

.hc-agenda-event-cell.accept-drop:before {
    border: 2px solid #1e88e5;
}*/
</style>

<script>
import store from "@/store";
import { mapGetters } from "vuex";

import {
    SET_EVENT,
    UPDATE_EVENT,
    SET_NEW_EVENT,
    SET_DRAGGED_EVENT,
    SET_RESIZED_EVENT,
} from "@/actions/project/event";

import { SET_PROSPECT } from "@/actions/project/prospect";
import { OPEN_SLIDE } from "@/actions/slide";

export default {
    props: {
        date: {
            type: String,
        },

        hour: {
            type: Number,
        },

        minute: {
            type: Number,
        },
    },

    data() {
        return {
            acceptDrop: false,
        };
    },

    methods: {
        /**
         *
         */
        dragStart() {
            if (deviceType() != "desktop") {
                return;
            }

            if (!this.draggedEvent) {
                let endDate = new Date(this.fullDate);
                endDate.setMinutes(endDate.getMinutes() + 15);

                store.commit(SET_NEW_EVENT, {
                    id: 0,
                    name: "Nouveau RDV",
                    started_at: this.fullDate,
                    ended_at: dateToString(endDate),
                });
            }
        },

        dragEnter() {
            this.acceptDrop = this.draggedEvent != null;
            if (this.draggedEvent) {
                const delay = this.getEventDelayInMinutes(
                    this.draggedEvent.started_at,
                    this.draggedEvent.ended_at
                );

                let endDate = new Date(this.fullDate);
                endDate.setMinutes(endDate.getMinutes() + delay);

                this.draggedEvent.started_at = this.fullDate;
                this.draggedEvent.ended_at = dateToString(endDate);
            } else if (this.resizedEvent) {
                if (
                    this.fullDate.substring(0, 10) !=
                    this.resizedEvent.started_at.substring(0, 10)
                ) {
                    return;
                }

                if (this.resizedEvent.started_at > this.fullDate) {
                    return;
                }

                let endDate = new Date(this.fullDate);
                endDate.setMinutes(endDate.getMinutes() + 15);

                this.resizedEvent.ended_at = dateToString(endDate);
            } else if (this.newEvent && !this.newEvent.user) {
                if (
                    this.fullDate.substring(0, 10) !=
                    this.newEvent.started_at.substring(0, 10)
                ) {
                    return;
                }

                if (this.newEvent.started_at > this.fullDate) {
                    return;
                }

                let endDate = new Date(this.fullDate);
                endDate.setMinutes(endDate.getMinutes() + 15);

                store.commit(SET_NEW_EVENT, {
                    ...this.newEvent,
                    ended_at: dateToString(endDate),
                });
            }
        },

        dragLeave() {
            this.acceptDrop = false;
        },

        dragEnd() {
            if (this.draggedEvent) {
                const delay = this.getEventDelayInMinutes(
                    this.draggedEvent.started_at,
                    this.draggedEvent.ended_at
                );

                let endDate = new Date(this.fullDate);
                endDate.setMinutes(endDate.getMinutes() + delay);

                store.commit(UPDATE_EVENT, {
                    id: this.draggedEvent.id,
                    started_at: this.fullDate,
                    ended_at: dateToString(endDate),
                });
                store.dispatch(UPDATE_EVENT, {
                    id: this.draggedEvent.id,
                    started_at: this.fullDate,
                    ended_at: dateToString(endDate),
                });
                store.commit(SET_DRAGGED_EVENT, null);
            } else if (this.resizedEvent) {
                if (
                    this.fullDate.substring(0, 10) !=
                    this.resizedEvent.started_at.substring(0, 10)
                ) {
                    return;
                }

                if (this.resizedEvent.started_at > this.fullDate) {
                    return;
                }

                let endDate = new Date(this.fullDate);
                endDate.setMinutes(endDate.getMinutes() + 15);

                store.commit(UPDATE_EVENT, {
                    id: this.resizedEvent.id,
                    ended_at: dateToString(endDate),
                });
                store.dispatch(UPDATE_EVENT, {
                    id: this.resizedEvent.id,
                    ended_at: dateToString(endDate),
                });
                store.commit(SET_RESIZED_EVENT, null);
            } else if (this.newEvent) {
                store.commit(SET_NEW_EVENT, {
                    ...this.newEvent,
                    // user: this.$store.state.auth.user,
                });
                store.commit(SET_PROSPECT, null);
                store.commit(SET_EVENT, this.newEvent);

                // Open event edit slide
                store.commit(OPEN_SLIDE, "prospect-manage-events");
            }
        },

        getEventDelayInMinutes(start, end) {
            let ms = Math.abs(new Date(end) - new Date(start));
            return Math.floor(ms / 1000 / 60);
        },
    },

    watch: {
        draggedEvent(newValue) {
            if (!newValue) {
                this.acceptDrop = false;
            }
        },
    },

    computed: {
        ...mapGetters(["newEvent", "draggedEvent", "resizedEvent"]),

        fullDate() {
            return (
                dateToString(this.date).substring(0, 10) +
                " " +
                (this.hour < 10 ? "0" : "") +
                this.hour +
                ":" +
                (this.minute < 10 ? "0" : "") +
                this.minute +
                ":00"
            );
        },

        label() {
            return (
                (this.hour < 10 ? "0" : "") +
                this.hour +
                ":" +
                (this.minute < 10 ? "0" : "") +
                this.minute
            );
        },
    },
};
</script>
