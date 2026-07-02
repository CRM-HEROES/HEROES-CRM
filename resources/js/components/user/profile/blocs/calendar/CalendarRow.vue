<template>
    <item @click.prevent.stop="edit">
        <icon :class="['fa', icon]" :style="style" :size="30" />
        <div class="hc-item-main-content" v-text="calendar.name"></div>
        <icon
            v-if="can('all.project.calendar.update')"
            tag="a"
            class="fa fa-cog"
            :size="28"
            :icon-size="9"
        />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_CALENDAR } from "@/actions/project/calendar";

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
        ...mapGetters(["can"]),

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
                return "fa fa-tasks";
            } else {
                return "fa fa-calendar";
            }
        },
    },
};
</script>
