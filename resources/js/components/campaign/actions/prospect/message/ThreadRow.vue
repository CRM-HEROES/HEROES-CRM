<template>
    <item>
        <icon :class="icon" :style="style" />
        <div class="hc-item-main-content" v-text="thread.name"></div>
        <icon
            v-if="can('all.project.thread.update')"
            class="fa fa-cog hc-show-on-hover"
            @click.stop.prevent="edit" />
        <div
            v-if="count"
            class="hc-prospect-thread-row-message-count"
            v-text="count"
        ></div
    ></item>
</template>

<style>
.hc-prospect-thread-row-message-count {
    height: 18px;
    line-height: 18px;
    padding: 0 5px;
    background-color: #7939b8;
    color: white;
    margin: 0 5px;
    font-family: monospace;
    border-radius: 9px;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_THREAD } from "@/actions/project/thread";

export default {
    props: {
        thread: {
            type: Object,
        },
        count: {
            type: Number,
        },
    },

    methods: {
        edit(e) {
            store.commit(OPEN_MODAL, "thread-update");
            store.commit(SET_THREAD, this.thread);
        },
    },

    computed: {
        ...mapGetters(["can"]),

        /**
         *
         */
        style() {
            return {
                color: this.thread.bgcolor,
            };
        },

        /**
         *
         */
        icon() {
            if (this.thread.private) {
                return "fa fa-envelope";
            } else {
                return "fa fa-envelope-open";
            }
        },
    },
};
</script>
