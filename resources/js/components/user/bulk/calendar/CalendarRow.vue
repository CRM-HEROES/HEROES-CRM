<template>
    <item tag="label">
        <icon class="fa fa-calendar" :style="style" />
        <div class="hc-item-main-content" v-text="calendar.name"></div>
        <icon
            v-if="can('all.project.calendar.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
        <checkbox v-model="selected" :value="calendar.id" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_CALENDAR } from "@/actions/project/calendar";
import { UPDATE_USER_BULK_CALENDARS } from "@/actions/project/user";

export default {
    props: {
        calendar: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "calendar-update");
            store.commit(SET_CALENDAR, this.calendar);
        },
    },

    computed: {
        ...mapGetters(["userBulkCalendars", "can"]),

        /**
         *
         */
        style() {
            return {
                color: this.calendar.bgcolor,
            };
        },

        selected: {
            get() {
                return this.userBulkCalendars;
            },
            set(value) {
                store.commit(UPDATE_USER_BULK_CALENDARS, value);
            },
        },
    },
};
</script>
