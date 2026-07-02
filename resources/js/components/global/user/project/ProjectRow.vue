<template>
    <item tag="label">
        <icon>
            <img
                :src="
                    project.thumbnail ? project.thumbnail : '/images/logo.png'
                "
                :alt="project.name"
        /></icon>
        <div class="hc-item-main-content" v-text="project.name"></div>
        <checkbox :model-value="value" @change="change" />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { ADD_USER_PROJECT, REMOVE_USER_PROJECT } from "@/actions/user/project";
import { OPEN_MODAL } from "@/actions/modal";

export default {
    props: {
        value: {
            type: Boolean,
            default: false,
        },

        project: {
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
                event.target.checked ? ADD_USER_PROJECT : REMOVE_USER_PROJECT,
                this.project
            );
            this.$emit("change", event);
        },
    },
};
</script>
