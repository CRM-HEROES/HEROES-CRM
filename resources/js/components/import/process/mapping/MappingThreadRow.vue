<template>
    <item>
        <icon class="fa fa-envelope" :style="style" />
        <div class="hc-item-main-content" v-text="thread.name"></div>
        <icon
            v-if="can('all.project.thread.update')"
            class="fa fa-cog hc-show-on-hover"
            @click.stop.prevent="edit"
        />
    </item>
</template>

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
    },
};
</script>
