<template>
    <span
        :class="['hc-user-agenda-month-table-event']"
        @mousedown.stop="dragStart"
        @click.prevent.stop="edit"
    >
        <icon :size="20" :icon-size="10" :class="icon" :style="style" />
        <span
            class="hc-user-agenda-month-table-event-name"
            v-text="event.name"
        ></span>
        <span
            v-if="eventProject"
            class="hc-user-agenda-month-table-event-project"
            v-text="eventProject.name"
        ></span>
        <template v-else-if="event.prospect">
            <icon
                :size="20"
                :icon-size="10"
                class="fa fa-envelope icon-green"
                v-tooltip="'Message'"
                @click.prevent.stop="manageMessages"
            />
            <icon
                v-if="
                    event.prospect.phone_number ||
                    event.prospect.mobile_phone_number
                "
                :size="20"
                :icon-size="10"
                class="fa fa-comment icon-purple"
                v-tooltip="'SMS'"
                @click.prevent.stop="manageSms"
            />
            <icon
                v-if="event.prospect.mobile_phone_number"
                :size="20"
                :icon-size="10"
                class="fa fa-phone icon-orange"
                v-tooltip="'Appeler'"
                @click.prevent.stop="manageInteractions"
            />
        </template>
        <span
            class="hc-user-agenda-month-table-event-date"
            v-text="event.started_at.substring(11, 16)"
        ></span>
    </span>
</template>

<style>
.hc-user-agenda-month-table-event {
    display: flex;
    flex-direction: row;
    align-items: center;
    width: 100%;
    font-size: 11px;
    height: 25%;
    border-radius: 2px;
}

.hc-user-agenda-month-table-event:hover {
    background-color: #eee;
}

.hc-user-agenda-month-table-event > i {
    margin-right: 3px;
}

.hc-user-agenda-month-table-event-name {
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.hc-user-agenda-month-table-event-date {
    padding-right: 3px;
    opacity: 0.8;
}

.hc-user-agenda-month-table-event-project {
    border-radius: 3px;
    padding: 3px 4px;
    background: #ddd;
    color: #000;
    line-height: 11px;
    font-size: 11px;
    margin-right: 2px;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { OPEN_SLIDE, OPEN_LEFT_SLIDE } from "@/actions/slide";
import { SET_PROSPECT } from "@/actions/project/prospect";
import { SET_INTERACTION_PROSPECT } from "@/actions/project/prospect/interaction";
import { SET_EVENT, SET_DRAGGED_EVENT } from "@/actions/project/event";

export default {
    props: {
        event: {
            type: Object,
        },
    },

    methods: {
        dragStart() {
            if (!this.editable) {
                return;
            }

            store.commit(SET_DRAGGED_EVENT, { ...this.event });
        },

        edit() {
            if (this.eventProject) {
                const routeData = this.$router.resolve({
                    name: "event",
                    params: {
                        project: this.eventProject.slug,
                    },
                    query: {
                        filters: JSON.stringify({
                            startedAt: this.eventsParamsValue("startedAt"),
                            endedAt: this.eventsParamsValue("endedAt"),
                        }),
                        event: this.event.id,
                        "display-mode": this.agendaDisplayMode,
                    },
                });
                window.location.href = routeData.href;

                return;
            }

            if (!this.editable) {
                return;
            }

            store.commit(SET_PROSPECT, null);
            store.commit(SET_EVENT, this.event);
            store.commit(OPEN_SLIDE, "prospect-manage-events");
        },

        /**
         * Manage prospect messages
         * See: @/components/prospect/message/Slide.vue
         */
        manageMessages() {
            store.commit(SET_PROSPECT, this.event.prospect);
            store.commit(OPEN_SLIDE, "prospect-manage-messages");
        },

        /**
         * Manage prospect interactions
         * See: @/components/prospect/interaction/Slide.vue
         */
        manageInteractions() {
            store.commit(SET_INTERACTION_PROSPECT, this.event.prospect);
            store.commit(OPEN_LEFT_SLIDE, "prospect-manage-interactions");
        },

        /**
         * Manage prospect sms
         * See: @/components/prospect/sms/Slide.vue
         */
        manageSms() {
            store.commit(SET_PROSPECT, this.event.prospect);
            store.commit(OPEN_SLIDE, "prospect-manage-sms");
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "draggedEvent",
            "eventsParamsValue",
            "agendaDisplayMode",
            "can",
        ]),

        style() {
            return {
                color: this.event.calendar.bgcolor + "!important",
                // backgroundColor: this.event.calendar.bgcolor,
            };
        },

        /**
         * Icon
         * that will be shown
         * in the event item
         * in terms of
         * the type of the event calendar
         */
        icon() {
            if (!this.event.calendar) {
                return "fa fa-calendar";
                // Physical calendar
            } else if (this.event.calendar.type == "physical") {
                return "fa fa-map-marker";
                // Phone calendar
            } else if (this.event.calendar.type == "telephone") {
                return "fa fa-phone";
                // GMeet calendar
            } else if (this.event.calendar.type == "hangout") {
                return "fa fa-video";
                // Task calendar
            } else if (this.event.calendar.type == "task") {
                return "fa fa-tasks";
                // Other
            } else {
                return "fa fa-calendar";
            }
        },

        editable() {
            if (this.eventProject) {
                return false;
            }

            if (this.can("all")) {
                return true;
            }

            if (this.event.user_id == this.user.id) {
                return true;
            }

            if (this.event.creator_id == this.user.id) {
                return true;
            }

            return false;
        },

        eventProject() {
            return this.event.calendar && this.event.calendar.project
                ? this.event.calendar.project
                : null;
        },
    },
};
</script>
