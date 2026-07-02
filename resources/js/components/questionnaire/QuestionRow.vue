<template>
    <item>
        <icon class="fa fa-question" />
        <div class="hc-item-main-content" v-text="question.name"></div>
        <icon
            v-if="can('all.project.questionnaire.update')"
            class="fa fa-cog hc-show-on-hover"
            @click.stop.prevent="edit"
        />
        <div
            v-if="question.options && question.options.length"
            class="hc-item-count"
            v-text="question.options.length"
        ></div>
        <icon
            v-if="question.type == 'radio' || question.type == 'checkbox'"
            class="fa fa-caret-right"
        />
    </item>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_QUESTIONNAIRE_SECTION } from "@/actions/project/questionnaire/section";
import { SET_QUESTIONNAIRE_QUESTION } from "@/actions/project/questionnaire/question";

export default {
    props: {
        section: {
            type: Object,
        },
        question: {
            type: Object,
        },
    },

    methods: {
        edit(e) {
            store.commit(OPEN_MODAL, "questionnaire-question-update");
            store.commit(SET_QUESTIONNAIRE_SECTION, this.section);
            store.commit(SET_QUESTIONNAIRE_QUESTION, this.question);
        },
    },

    computed: {
        ...mapGetters(["can"]),
    },
};
</script>
