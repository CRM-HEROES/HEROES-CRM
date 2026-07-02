<template>
    <item @click.prevent.stop="edit">
        <icon :class="['fa', icon]" :style="style" :size="30" />
        <div class="hc-item-main-content" v-text="thread.name"></div>
        <icon tag="a" class="fa fa-cog" />
    </item>
</template>

<script>
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_THREAD } from "@/actions/project/thread";

export default {
    props: {
        thread: {
            type: Object,
        },
    },

    methods: {
        edit() {
            store.commit(OPEN_MODAL, "thread-update");
            store.commit(SET_THREAD, this.thread);
        },
    },

    computed: {
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
            return this.thread.private ? "fa-envelope" : "fa-envelope-open";
        },
    },
};
</script>
