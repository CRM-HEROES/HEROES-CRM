<template>
    <tab-layout :count="4" :tab="tab" class="hc-flex-1">
        <template #1>
            <div class="hc-flex-column" style="height: 100%">
                <search v-model="questionnaireKeyword" />
                <item-list class="hc-flex-1" padding="5px">
                    <questionnaire-row
                        v-for="c in filteredQuestionnaires"
                        :key="c.id"
                        :questionnaire="c"
                        @click="setQuestionnaire(c)"
                    />
                </item-list>
                <buttons v-if="can('all.project.questionnaire.add')">
                    <a
                        @click.prevent="addQuestionnaire"
                        v-text="$t('questionnaire.questionnaire.add.title')"
                    ></a>
                </buttons>
            </div>
        </template>

        <template #2>
            <div
                class="hc-flex-column"
                style="height: 100%"
                v-if="questionnaire"
            >
                <item @click="setQuestionnaire(null)" class="bordered">
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content"
                        v-text="questionnaire.name"
                    ></div>
                </item>
                <search v-model="sectionKeyword" />
                <item-list class="hc-flex-1" padding="5px">
                    <section-row
                        v-for="c in filteredSections"
                        :key="c.id"
                        :section="c"
                    />
                </item-list>
                <buttons>
                    <a
                        @click.prevent="addSection"
                        v-text="$t('questionnaire.section.add.title')"
                    ></a>
                </buttons>
            </div>
            <loading :loading="fetchingQuestionnaire" />
        </template>

        <template #3>
            <div
                class="hc-flex-column"
                style="height: 100%"
                v-if="questionnaireQuestion"
            >
                <item @click="setQuestion(null)" class="bordered">
                    <icon class="fa fa-caret-left" />
                    <div
                        class="hc-item-main-content"
                        v-text="questionnaireQuestion.name"
                    ></div>
                </item>
                <search v-model="optionKeyword" />
                <item-list class="hc-flex-1" padding="5px">
                    <option-row
                        v-for="c in questionnaireQuestion.options"
                        :key="c.id"
                        :option="c"
                        @click=""
                    />
                </item-list>
                <buttons>
                    <a
                        @click.prevent="addOption"
                        v-text="$t('questionnaire.option.add.title')"
                    ></a>
                </buttons>
            </div>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    SET_QUESTIONNAIRE,
    SHOW_QUESTIONNAIRE,
} from "@/actions/project/questionnaire";
import { SET_QUESTIONNAIRE_QUESTION } from "@/actions/project/questionnaire/question";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import QuestionnaireRow from "./QuestionnaireRow.vue";
import SectionRow from "./SectionRow.vue";
import OptionRow from "./OptionRow.vue";
import questionnaire from "../../store/project/questionnaire";

export default {
    components: {
        QuestionnaireRow,
        SectionRow,
        OptionRow,
    },

    data() {
        return {
            name: "questionnaires",
            tab: 0,
            questionnaireKeyword: "",
            sectionKeyword: "",
            optionKeyword: "",
            fetchingQuestionnaire: false,
        };
    },

    methods: {
        setQuestionnaire(questionnaire) {
            store.commit(SET_QUESTIONNAIRE, questionnaire);
        },

        setQuestion(question) {
            store.commit(SET_QUESTIONNAIRE_QUESTION, question);
        },

        addQuestionnaire() {
            store.commit(OPEN_MODAL, "questionnaire-add");
        },

        addSection() {
            store.commit(OPEN_MODAL, "questionnaire-section-add");
        },

        addQuestion() {
            store.commit(OPEN_MODAL, "questionnaire-question-add");
        },

        addOption() {
            store.commit(OPEN_MODAL, "questionnaire-option-add");
        },
    },

    watch: {
        async questionnaire(newValue) {
            if (!this.slideOpen(this.name)) {
                return;
            }

            if (newValue) {
                this.tab = 1;

                if (!newValue.sections) {
                    this.fetchingQuestionnaire = true;

                    try {
                        await store.dispatch(SHOW_QUESTIONNAIRE, newValue.id);
                    } finally {
                        this.fetchingQuestionnaire = false;
                    }
                }
            } else {
                this.tab = 0;
            }
        },

        questionnaireQuestion(newValue) {
            if (!this.slideOpen(this.name)) {
                return;
            }

            if (newValue) {
                this.tab = 2;
            } else {
                this.tab = 1;
            }
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "questionnaires",
            "questionnaire",
            "questionnaireQuestion",
            "questionnaireOption",
            "slideOpen",
            "can",
        ]),

        /**
         *
         */
        filteredQuestionnaires() {
            const keyword = removeStringAccent(this.questionnaireKeyword);

            return this.questionnaires.filter(
                (questionnaire) =>
                    removeStringAccent(questionnaire.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        filteredSections() {
            if (!this.questionnaire) {
                return [];
            }

            if (!this.questionnaire.sections) {
                return [];
            }

            const keyword = removeStringAccent(this.sectionKeyword);

            return this.questionnaire.sections.map((section) => ({
                ...section,
                questions: section.questions
                    ? section.questions.filter(
                          (question) =>
                              removeStringAccent(question.name).indexOf(
                                  keyword
                              ) >= 0
                      )
                    : [],
            }));
            // .filter((section) => section.questions.length > 0);
        },
    },
};
</script>
