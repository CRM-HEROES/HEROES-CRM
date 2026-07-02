<template>
    <item tag="label">
        <icon class="fa fa-envelope" :style="style" />
        <div class="hc-item-main-content" v-text="thread.name"></div>
        <icon
            v-if="can('all.project.thread.update')"
            tag="a"
            class="fa fa-cog hc-show-on-hover"
            @click.prevent.stop="edit"
        />
        <checkbox :model-value="value" :disabled="disabled" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    ADD_ROLE_THREAD,
    REMOVE_ROLE_THREAD,
} from "@/actions/project/role/thread";
import { OPEN_MODAL } from "@/actions/modal";
import { SET_THREAD } from "@/actions/project/thread";

export default {
    props: {
        value: {
            type: Boolean,
            default: false,
        },

        disabled: {
            type: Boolean,
        },

        thread: {
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
                event.target.checked ? ADD_ROLE_THREAD : REMOVE_ROLE_THREAD,
                this.thread
            );
            this.$emit("change", event);
        },

        edit() {
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
