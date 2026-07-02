<template>
    <item-list class="hc-questionnaire-section">
        <item class="hc-questionnaire-section-item">
            <icon class="fa fa-columns" />
            <div class="hc-item-main-content" v-text="section.name"></div>
            <icon
                v-if="can('all.project.questionnaire.update')"
                class="fa fa-cog hc-show-on-hover"
                @click.stop.prevent="edit"
            />
            <icon
                class="fa fa-plus"
                @click.stop.prevent="addQuestion"
                v-tooltip="$t('questionnaire.question.add.title')"
            />
        </item>

        <question-row
            v-for="question in section.questions"
            :key="question.id"
            :section="section"
            :question="question"
            @click="setQuestion(question)"
        />
    </item-list>
</template>

<style>
.hc-questionnaire-section {
    height: auto;
    display: contents;
}

.hc-questionnaire-section-item {
    background-color: #f2f2f5;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_QUESTIONNAIRE_SECTION } from "@/actions/project/questionnaire/section";
import { SET_QUESTIONNAIRE_QUESTION } from "@/actions/project/questionnaire/question";

import QuestionRow from "./QuestionRow.vue";

export default {
    props: {
        section: {
            type: Object,
        },
    },

    components: {
        QuestionRow,
    },

    methods: {
        edit(e) {
            store.commit(OPEN_MODAL, "questionnaire-section-update");
            store.commit(SET_QUESTIONNAIRE_SECTION, this.section);
        },

        /**
         * Set current question
         * and display options related to the question
         * @param {*} question
         */
        setQuestion(question) {
            if (question.type == "radio" || question.type == "checkbox") {
                store.commit(SET_QUESTIONNAIRE_SECTION, this.section);
                store.commit(SET_QUESTIONNAIRE_QUESTION, question);
            }
        },

        /**
         * Add question into the current section
         */
        addQuestion() {
            // Question modal
            store.commit(OPEN_MODAL, "questionnaire-question-add");
            // Set current section
            store.commit(SET_QUESTIONNAIRE_SECTION, this.section);
        },
    },

    computed: {
        ...mapGetters(["can"]),
    },
};
</script>
