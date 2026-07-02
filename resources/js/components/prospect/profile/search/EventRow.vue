<template>
    <item tag="a" v-tooltip="name" @click.prevent="action">
        <icon class="fa fa-calendar" :style="style" :size="30" />
        <div class="hc-item-main-content" v-text="name"></div>
    </item>
</template>

<script>
import store from "@/store";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";
import { SET_EVENT } from "@/actions/project/event";

export default {
    props: {
        event: {
            type: Object,
        },
    },

    methods: {
        action() {
            store.commit(SET_EVENT, this.event);
            store.commit(OPEN_SLIDE, "prospect-manage-events");
        },
    },

    computed: {
        /**
         *
         */
        style() {
            return {
                color: this.event.calendar
                    ? this.event.calendar.bgcolor
                    : "#CCCCCC",
            };
        },

        name() {
            return (
                (this.event.name ? this.event.name : this.event.calendar.name) +
                " " +
                dayjs(this.event.started_at).format("ddd, DD MMM YYYY")
            );
        },
    },
};
</script>
