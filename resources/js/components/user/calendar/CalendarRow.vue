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
        <checkbox :model-value="value" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    ADD_USER_CALENDAR,
    REMOVE_USER_CALENDAR,
} from "@/actions/project/user/calendar";
import { OPEN_MODAL } from "@/actions/modal";
import { SET_CALENDAR } from "@/actions/project/calendar";

export default {
    props: {
        value: {
            type: Boolean,
            default: false,
        },

        calendar: {
            type: Object,
        },
    },

    data() {
        return {
            currentValue: this.value,
        };
    },

    methods: {
        change(event) {
            store.dispatch(
                event.target.checked ? ADD_USER_CALENDAR : REMOVE_USER_CALENDAR,
                this.calendar
            );
            this.$emit("change", event);
        },

        edit() {
            store.commit(OPEN_MODAL, "calendar-update");
            store.commit(SET_CALENDAR, this.calendar);
        },
    },

    computed: {
        ...mapGetters(["can"]),

        /**
         *
         */
        style() {
            return {
                color: this.calendar.bgcolor,
            };
        },
    },
};
</script>
