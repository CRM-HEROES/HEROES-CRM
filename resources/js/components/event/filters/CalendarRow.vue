<template>
    <item tag="a" @click.prevent="select">
        <icon :class="icon" :style="style" />
        <div class="hc-item-main-content" v-text="calendar.name"></div>
        <checkbox @click.stop :model-value="isChecked" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import {
    FETCH_EVENTS,
    ADD_EVENT_PARAMS,
    REMOVE_EVENT_PARAMS,
} from "@/actions/project/event";

export default {
    props: {
        calendar: {
            type: Object,
        },
    },

    methods: {
        select() {
            store.commit(REMOVE_EVENT_PARAMS, {
                key: this.withoutKey,
            });

            store.commit(REMOVE_EVENT_PARAMS, {
                key: this.withKey,
            });

            store.commit(ADD_EVENT_PARAMS, {
                key: this.withKey,
                value: this.value,
                multiple: true,
            });
            store.dispatch(FETCH_EVENTS);
        },

        change(event) {
            // Remove without param
            store.commit(REMOVE_EVENT_PARAMS, {
                key: this.withoutKey,
                value: this.value,
                multiple: true,
            });

            // Add or remove with param
            store.commit(
                event.target.checked ? ADD_EVENT_PARAMS : REMOVE_EVENT_PARAMS,
                {
                    key: this.withKey,
                    value: this.value,
                    multiple: true,
                }
            );
            store.dispatch(FETCH_EVENTS);
            this.$emit("change", event);
        },
    },

    computed: {
        ...mapGetters(["eventsParamExists"]),

        roles() {
            if (!this.calendar.roles || this.calendar.roles.length == 0) {
                return "Aucun rôle";
            }

            return this.calendar.roles
                .map((role) => role.name)
                .filter((role) => role)
                .join(", ");
        },

        /**
         *
         */
        withKey() {
            return "withCalendars";
        },

        /**
         *
         */
        value() {
            return this.calendar.id;
        },

        /**
         *
         */
        isChecked() {
            return this.eventsParamExists(this.withKey, this.value);
        },
        /**
         *
         */
        style() {
            return {
                color: this.calendar.bgcolor,
            };
        },

        /**
         *
         */
        icon() {
            if (this.calendar.type == "physical") {
                return "fa fa-map-marker";
            } else if (this.calendar.type == "telephone") {
                return "fa fa-phone";
            } else if (this.calendar.type == "hangout") {
                return "fa fa-video";
            } else if (this.calendar.type == "task") {
                return "fa fa-task";
            } else {
                return "fa fa-calendar";
            }
        },
    },
};
</script>
