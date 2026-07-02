<template>
    <item tag="label">
        <icon class="fa fa-users" :style="style" />
        <div class="hc-item-main-content" v-text="group.name"></div>
        <icon
            v-if="can('all.project.group.update')"
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
    ADD_USER_GROUP,
    REMOVE_USER_GROUP,
} from "@/actions/project/user/group";
import { OPEN_MODAL } from "@/actions/modal";
import { SET_GROUP } from "@/actions/project/group";

export default {
    props: {
        value: {
            type: Boolean,
            default: false,
        },

        group: {
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
                event.target.checked ? ADD_USER_GROUP : REMOVE_USER_GROUP,
                this.group
            );
            this.$emit("change", event);
        },

        edit() {
            store.commit(OPEN_MODAL, "group-update");
            store.commit(SET_GROUP, this.group);
        },
    },

    computed: {
        ...mapGetters(["can"]),

        /**
         *
         */
        style() {
            return {
                color: this.group.bgcolor,
            };
        },
    },
};
</script>
