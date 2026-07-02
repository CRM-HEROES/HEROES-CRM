<template>
    <item @click="edit">
        <icon class="fa fa-clipboard" :size="28" :style="style" />
        <div class="hc-item-main-content" v-text="questionnaire.name"></div>
    </item>
</template>

<script>
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_QUESTIONNAIRE } from "@/actions/project/questionnaire";

export default {
    props: {
        questionnaire: {
            type: Object,
        },
    },

    methods: {
        edit(e) {
            store.commit(OPEN_MODAL, "questionnaire-update");
            store.commit(SET_QUESTIONNAIRE, this.questionnaire);
        },
    },

    computed: {
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
