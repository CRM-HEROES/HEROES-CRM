<template>
    <tree-layout :tabulation="5" @open="fetchQuestions">
        <template #header>
            <item>
                <icon class="fa fa-columns" color="#e58b00" />
                <div class="hc-item-main-content" v-text="section.name"></div>
                <icon
                    v-if="can('all.project.questionnaire.update')"
                    tag="a"
                    class="fa fa-cog hc-show-on-hover"
                    :size="30"
                    @mousedown.stop
                    @click.prevent.stop="edit"
                />
                <icon
                    tag="a"
                    class="fa fa-plus"
                    @click.prevent.stop="addQuestion"
                />
                <icon class="fa fa-caret-down">
                    <loading :loading="fetchingQuestions" />
                </icon>
            </item>
        </template>
        <template #body>
            <questionnaire-question-row
                v-for="question in section.questions
                    ? section.questions.filter((q) => q.type != 'file')
                    : []"
                :key="question.id"
                :questionnaire="questionnaire"
                :section="section"
                :question="question"
                @dragging="dragNewField"
                @dragged="dropNewField"
            />
        </template>
    </tree-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import FieldTemplate from "../components/FieldTemplate.vue";
import QuestionnaireQuestionRow from "./QuestionnaireQuestionRow.vue";

import { FETCH_QUESTIONNAIRE_QUESTIONS } from "@/actions/project/questionnaire/question";
import { SET_QUESTIONNAIRE } from "@/actions/project/questionnaire";
import { SET_QUESTIONNAIRE_SECTION } from "@/actions/project/questionnaire/section";
import { OPEN_MODAL } from "@/actions/modal";

export default {
    components: {
        FieldTemplate,
        QuestionnaireQuestionRow,
    },

    props: {
        questionnaire: {
            type: Object,
        },
        section: {
            type: Object,
        },
    },

    data() {
        return {
            fetchingQuestions: false,
        };
    },

    methods: {
        async fetchQuestions() {
            this.fetchingQuestions = true;

            try {
                await store.dispatch(FETCH_QUESTIONNAIRE_QUESTIONS, {
                    questionnaire: this.questionnaire.id,
                    section: this.section.id,
                });
            } finally {
                this.fetchingQuestions = false;
            }
        },

        edit() {
            store.commit(SET_QUESTIONNAIRE, this.questionnaire);
            store.commit(SET_QUESTIONNAIRE_SECTION, this.section);
            store.commit(OPEN_MODAL, "questionnaire-section-update");
        },

        /**
         *
         */
        addQuestion() {
            store.commit(SET_QUESTIONNAIRE, this.questionnaire);
            store.commit(SET_QUESTIONNAIRE_SECTION, this.section);
            store.commit(OPEN_MODAL, "questionnaire-question-add");
        },

        dragNewField(field) {
            this.$emit("dragging", field);
        },

        dropNewField(x, y) {
            this.$emit("dragged", x, y);
        },
    },

    computed: {
        ...mapGetters(["can"]),
    },
};
</script>
