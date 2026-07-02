<template>
    <item tag="label">
        <icon
            :class="['fa', thread.private ? 'fa-envelope' : 'fa-envelope-open']"
            :style="style"
        />
        <div class="hc-item-main-content" v-text="thread.name"></div>
        <icon
            v-if="can('all.project.thread.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
        <checkbox v-model="selected" :value="thread.id" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_THREAD } from "@/actions/project/thread";
import { UPDATE_USER_BULK_THREADS } from "@/actions/project/user";

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
        ...mapGetters(["userBulkThreads", "can"]),

        /**
         *
         */
        style() {
            return {
                color: this.thread.bgcolor,
            };
        },

        selected: {
            get() {
                return this.userBulkThreads;
            },
            set(value) {
                store.commit(UPDATE_USER_BULK_THREADS, value);
            },
        },
    },
};
</script>
