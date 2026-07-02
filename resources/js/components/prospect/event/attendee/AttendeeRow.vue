<template>
    <item tag="label">
        <icon class="fa fa-user-plus" />
        <div class="hc-item-main-content" v-text="user.name"></div>
        <checkbox :model-value="value" @change="change" />
    </item>
</template>

<script>
import store from "@/store";

// Actions
import {
    ADD_PROSPECT_USER,
    REMOVE_PROSPECT_USER,
} from "@/actions/project/prospect/user";
import { OPEN_MODAL } from "@/actions/modal";
import { SET_USER } from "@/actions/project/user";

export default {
    props: {
        value: {
            type: Boolean,
            default: false,
        },

        user: {
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
            this.$emit(event.target.checked ? "add" : "remove", this.user);
        },
    },
};
</script>
