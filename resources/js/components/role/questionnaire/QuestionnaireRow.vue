<template>
    <item tag="label">
        <icon class="fa fa-clipboard" :style="style" />
        <div class="hc-item-main-content" v-text="questionnaire.name"></div>
        <icon
            v-if="can('all.project.questionnaire.update')"
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
    ADD_ROLE_QUESTIONNAIRE,
    REMOVE_ROLE_QUESTIONNAIRE,
} from "@/actions/project/role/questionnaire";
import { OPEN_MODAL } from "@/actions/modal";
import { SET_QUESTIONNAIRE } from "@/actions/project/questionnaire";

export default {
    props: {
        value: {
            type: Boolean,
            default: false,
        },

        disabled: {
            type: Boolean,
        },

        questionnaire: {
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
                event.target.checked
                    ? ADD_ROLE_QUESTIONNAIRE
                    : REMOVE_ROLE_QUESTIONNAIRE,
                this.questionnaire
            );
            this.$emit("change", event);
        },

        edit() {
            store.commit(OPEN_MODAL, "questionnaire-update");
            store.commit(SET_QUESTIONNAIRE, this.questionnaire);
        },
    },

    computed: {
        ...mapGetters(["can"]),

        /**
         *
         */
        style() {
            return {
                color: this.questionnaire.bgcolor,
            };
        },
    },
};
</script>
