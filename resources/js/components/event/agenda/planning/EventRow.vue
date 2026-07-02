<template>
    <div class="hc-agenda-event-row">
        <div class="hc-agenda-event-row-options">
            <icon
                tag="a"
                class="fa fa-calendar icon-orange"
                @click.prevent="edit"
            />
            <icon
                tag="a"
                :class="[
                    'fa',
                    'fa-phone',
                    'icon-green',
                    prospect &&
                    (prospect.phone_number || prospect.mobile_phone_number)
                        ? ''
                        : 'hidden',
                ]"
                @click.prevent="manageInteractions"
            />
            <icon
                tag="a"
                :class="[
                    'fa',
                    'fa-comment',
                    'icon-purple',
                    prospect && prospect.mobile_phone_number ? '' : 'hidden',
                ]"
                @click.prevent="manageSms"
            />
            <icon
                tag="a"
                :class="[
                    'fa',
                    'fa-folder',
                    'icon-blue',
                    prospect ? '' : 'hidden',
                ]"
                @click.prevent="manageFiles"
            />
            <icon
                tag="a"
                :class="[
                    'fa',
                    'fa-envelope',
                    'icon-green',
                    prospect ? '' : 'hidden',
                ]"
                @click.prevent="manageMessages"
            />
            <icon
                tag="a"
                :class="[
                    'fa',
                    'fa-shopping-cart',
                    'icon-cyan',
                    prospect ? '' : 'hidden',
                ]"
                @click.prevent="manageOrders"
            />
            <icon
                tag="a"
                :class="[
                    'fa',
                    'fa-file-pdf',
                    'icon-garnet',
                    prospect ? '' : 'hidden',
                ]"
                @click.prevent="manageDocuments"
            />
            <icon
                tag="a"
                :class="[
                    'fa',
                    'fa-map-marker',
                    'icon-green',
                    event.latitude && event.longitude ? '' : 'hidden',
                ]"
                @click.prevent="map"
            />
            <icon
                tag="a"
                :class="[
                    'fa',
                    'fa-check-circle',
                    eventDone ? 'icon-green' : 'icon-grey',
                ]"
                @click.prevent="eventDone = !eventDone"
            />
            <icon
                tag="a"
                :class="[
                    'fa',
                    'fa-thumbs-up',
                    eventConfirmed ? 'icon-blue' : 'icon-grey',
                ]"
                @click.prevent="eventConfirmed = !eventConfirmed"
            />
        </div>
        <div class="hc-agenda-event-row-title">
            <icon :size="22" class="fa fa-info" />
            <input v-model="event.name" @change="update" />
        </div>
        <div class="hc-agenda-event-row-prospect">
            <icon :size="22" class="fa fa-user" />
            <template v-if="prospect">
                <router-link
                    :to="{
                        name: 'prospect.show',
                        params: {
                            project: this.project.slug,
                            prospect: this.prospect.id,
                        },
                    }"
                    v-text="prospectName"
                ></router-link>
            </template>
        </div>
        <div class="hc-agenda-event-row-location">
            <icon :size="22" class="fa fa-map-marker" />
            <google-map-input v-model="event.location" />
        </div>
        <div class="hc-agenda-event-row-calendar">
            <span
                v-if="event.calendar"
                v-text="event.calendar.name"
                :style="calendarStyle"
            ></span>
        </div>
        <div class="hc-agenda-event-row-date">
            <icon :size="22" class="fa fa-calendar" />
            <input v-model="eventDate" type="date" @change="update" />
            <select v-model="eventStartHour" @change="update">
                <option value="">Début...</option>
                <option
                    v-for="hour in eventHours"
                    :value="hour"
                    v-text="hour"
                ></option>
            </select>
            <select v-model="eventEndHour" @change="update">
                <option value="">Fin...</option>
                <option
                    v-for="hour in eventHours"
                    :value="hour"
                    v-text="hour"
                ></option>
            </select>
        </div>
    </div>
</template>

<style>
.hc-agenda-event-row {
    width: 100%;
    display: flex;
    flex-direction: row;
    line-height: 24px;
}

.hc-agenda-event-row > div {
    display: flex;
    flex-direction: row;
    padding: 2px;
    border-right: 1px solid #0001;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 12px;
}

.hc-agenda-event-row > div > .hc-icon {
    display: inline-block;
    width: 26px;
    height: 26px;
    cursor: pointer;
    line-height: 26px;
    text-align: center;
    font-size: 13px;
}

.hc-agenda-event-row > div > a.hidden {
    visibility: hidden;
    pointer-events: none;
}

.hc-agenda-event-row > div > input {
    border: none;
}

.hc-agenda-event-row-title {
    width: 200px;
}

.hc-agenda-event-row-prospect {
    width: 200px;
}

.hc-agenda-event-row-prospect > span {
    flex: 1;
    overflow: hidden;
}

.hc-agenda-event-row-location {
    flex: 1;
}

.hc-agenda-event-row-location > input {
    flex: 1;
}

.hc-agenda-event-row-calendar {
    width: 170px;
    overflow: hidden;
    text-overflow: ellipsis;
    padding: 3px !important;
}

.hc-agenda-event-row-calendar > span {
    display: inline-block;
    border-radius: 3px;
    padding: 0px 5px;
    height: 24px;
    line-height: 24px;
    font-size: 11px;
}

.hc-agenda-event-row-date {
    width: 280px;
    padding: 0 10px;
}

.hc-agenda-event-row-date > input,
.hc-agenda-event-row-date > select {
    border-radius: 2px;
    border: 1px solid #ccc !important;
    width: 100px;
    height: 26px;
    padding: 0 5px;
    margin: 0 2px;
    float: left;
}

.hc-agenda-event-row-date > select {
    width: 70px;
}

@media (min-width: 768px) {
    .hc-agenda-event-row {
        width: 100%;
        display: flex;
        flex-direction: row;
        line-height: 24px;
        border-bottom: 1px solid #0001 !important;
    }
}

@media (max-width: 767px) {
    .hc-agenda-event-row {
        flex-direction: column;
        margin-bottom: 10px;
        border-width: 1px;
        border-style: solid;
        border-radius: 10px;
        padding: 7px;
    }

    .hc-agenda-event-row > div {
        border: none;
        width: 100% !important;
        padding: 0px;
    }

    .hc-agenda-event-row-options {
        justify-content: center;
    }

    .hc-agenda-event-row > div > a.hidden {
        display: none !important;
    }
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import { SET_EVENT, UPDATE_EVENT } from "@/actions/project/event";
import { SET_PROSPECT } from "@/actions/project/prospect";
import { SET_INTERACTION_PROSPECT } from "@/actions/project/prospect/interaction";
import { OPEN_SLIDE, OPEN_LEFT_SLIDE } from "@/actions/slide";
import { SET_PROSPECT_ORDER_TAB } from "@/actions/project/prospect/order";

export default {
    props: {
        event: {
            type: Object,
        },
    },

    methods: {
        update() {
            const {
                id,
                name,
                location,
                started_at,
                ended_at,
                done,
                confirmed,
            } = this.event;
            store.dispatch(UPDATE_EVENT, {
                id,
                name,
                location,
                started_at,
                ended_at,
                done,
                confirmed,
            });
        },

        edit() {
            store.commit(SET_PROSPECT, null);
            store.commit(SET_EVENT, this.event);
            store.commit(OPEN_SLIDE, "prospect-manage-events");
        },

        /**
         * Manage prospect files
         * See: @/components/prospect/file/Slide.vue
         */
        manageFiles() {
            store.commit(SET_PROSPECT, this.event.prospect);
            store.commit(OPEN_SLIDE, "prospect-manage-files");
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
         * Manage prospect orders
         * See: @/components/prospect/order/Slide.vue
         */
        manageOrders() {
            store.commit(SET_PROSPECT, this.event.prospect);
            store.commit(SET_PROSPECT_ORDER_TAB, 0);
            store.commit(OPEN_SLIDE, "prospect-manage-orders");
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

        /**
         * Manage prospect documents
         * See: @/components/prospect/document/Slide.vue
         */
        manageDocuments() {
            store.commit(SET_PROSPECT, this.event.prospect);
            store.commit(OPEN_SLIDE, "prospect-manage-documents");
        },

        /**
         *
         */
        map() {
            this.$router.push({
                name: "map",
                params: {
                    project: this.project.slug,
                },
                query: {
                    filters: JSON.stringify({
                        validAddress: 1,
                        position: [
                            this.event.latitude,
                            this.event.longitude,
                            30,
                        ].join(","),
                    }),
                },
            });
        },
    },

    computed: {
        ...mapGetters(["project"]),

        calendarStyle() {
            return {
                color: this.event.calendar.color,
                backgroundColor: this.event.calendar.bgcolor,
            };
        },

        /**
         */
        prospect() {
            return this.event.prospect ? this.event.prospect : null;
        },

        /**
         */
        prospectName() {
            return [this.prospect.first_name, this.prospect.last_name]
                .filter((e) => e)
                .join(" ");
        },

        eventDone: {
            get: function () {
                return this.event.done;
            },
            set: function (value) {
                this.event.done = value;
                this.update();
            },
        },

        eventConfirmed: {
            get: function () {
                return this.event.confirmed;
            },
            set: function (value) {
                this.event.confirmed = value;
                this.update();
            },
        },

        /**
         * Event date without time
         */
        eventDate: {
            get: function () {
                return this.event.started_at.substring(0, 10);
            },
            set: function (value) {
                let hourStart = this.event.started_at
                    ? this.event.started_at.substring(10)
                    : null;
                let hourEnd = this.event.ended_at
                    ? this.event.ended_at.substring(10)
                    : null;

                this.event.started_at =
                    value + (hourStart ? hourStart : " 08:00:00");
                this.event.ended_at = value + (hourEnd ? hourEnd : " 08:00:00");
            },
        },

        /**
         * Event start hour
         */
        eventStartHour: {
            get: function () {
                return this.event.started_at.substring(11, 16);
            },
            set: function (value) {
                if (!value) {
                    return;
                }

                let deltaMn = 30;

                if (this.event.ended_at) {
                    deltaMn = parseInt(
                        (new Date(this.event.ended_at).getTime() -
                            new Date(this.event.started_at).getTime()) /
                            60000
                    );
                }

                this.event.started_at =
                    this.event.started_at.substring(0, 11) +
                    value +
                    this.event.started_at.substring(16);

                if (
                    !this.event.ended_at ||
                    this.event.started_at >= this.event.ended_at
                ) {
                    var date = new Date(this.event.started_at);
                    date.setMinutes(date.getMinutes() + deltaMn);
                    this.event.ended_at = dateToString(date);
                }
            },
        },

        /**
         * Event end hour
         */
        eventEndHour: {
            get: function () {
                if (!this.event || !this.event.ended_at) {
                    return "";
                }

                return this.event.ended_at.substring(11, 16);
            },
            set: function (value) {
                if (!this.event) {
                    return;
                }

                if (!value) {
                    return;
                }

                this.event.ended_at =
                    this.event.started_at.substring(0, 11) +
                    value +
                    this.event.started_at.substring(16);
            },
        },

        /**
         * Selectable event hours
         */
        eventHours() {
            let eh = [];

            for (var i = 0; i < 14; ++i) {
                for (var j = 0; j < 4; ++j) {
                    eh.push(
                        (i < 2 ? "0" : "") +
                            (i + 8) +
                            ":" +
                            (j == 0 ? "0" : "") +
                            j * 15
                    );
                }
            }

            return eh;
        },
    },
};
</script>
