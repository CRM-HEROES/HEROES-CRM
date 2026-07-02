<template>
    <slide
        :name="name"
        @open="fetchQuestionnaires(), fetchProspectQuestionnaireResults()"
        :title="
            (prospectQuestionnaire
                ? prospectQuestionnaire.name
                : $t('prospect.form.title')) +
            ' (' +
            prospectFullName +
            ')'
        "
        :url="
            prospect
                ? {
                      name: 'prospect.show',
                      params: {
                          project: project.slug,
                          prospect: prospect.id,
                      },
                  }
                : null
        "
        icon="fa fa-questionnaire"
        style="width: 340px"
    >
        <tab-layout :count="3" :tab="tab" class="hc-flex-1">
            <!-- List of questionnaires -->
            <template #1>
                <div
                    class="hc-flex-column"
                    style="height: 100%; position: relative"
                >
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

            <!-- List of questions -->
            <template #2>
                <div
                    class="hc-flex-column"
                    style="height: 100%; position: relative"
                    v-if="prospectQuestionnaire"
                >
                    <!-- Back to the list of questionnaires -->

                    <item
                        @click="(prospectQuestionnaire = null), (tab = 0)"
                        class="bordered"
                    >
                        <icon class="fa fa-caret-left" />
                        <div
                            class="hc-item-main-content"
                            v-text="prospectQuestionnaire.name"
                        ></div>
                    </item>

                    <!-- List of sections -->

                    <item class="bordered">
                        <div
                            class="hc-item-main-content"
                            v-text="section ? section.name : ''"
                        ></div>
                        <icon
                            class="fa fa-caret-up"
                            @click.prevent="prevQuestion"
                        />
                        <icon
                            class="fa fa-caret-down"
                            @click.prevent="nextQuestion"
                        />
                    </item>

                    <div id="hc-prospect-questionnaire-progress">
                        <div
                            id="hc-prospect-questionnaire-progress-bar"
                            :style="'width: ' + progress + '%'"
                        ></div>
                        <div id="hc-prospect-questionnaire-progress-marks">
                            <div
                                v-for="s in prospectQuestionnaire.sections"
                                class="hc-prospect-questionnaire-progress-mark"
                                :title="s.name"
                                :style="{
                                    width:
                                        (s.questions.length * 100) /
                                            questionnaireQuestions.length +
                                        '%',
                                }"
                                @click="section = s"
                            ></div>
                        </div>
                    </div>

                    <div
                        class="hc-flex-1 hc-flex-column"
                        style="
                            gap: 0px;
                            padding: 0px;
                            overflow: auto;
                            position: relative;
                            scroll-behavior: smooth;
                        "
                        ref="prospectQuestionnaireQuestions"
                    >
                        <div
                            v-for="q in questionnaireQuestions"
                            :key="q.id"
                            :id="'prospect-questionnaire-question-' + q.id"
                            :class="[
                                'hc-prospect-questionnaire-question',
                                question && question.id == q.id
                                    ? ' current'
                                    : '',
                            ]"
                        >
                            <div
                                class="hc-prospect-questionnaire-question-question"
                                v-text="q.name"
                            ></div>
                            <div
                                class="hc-prospect-questionnaire-question-response"
                            >
                                <!-- List of options -->
                                <template
                                    v-if="
                                        q.type == 'radio' ||
                                        q.type == 'checkbox'
                                    "
                                >
                                    <label
                                        v-for="option in q.options"
                                        class="hc-prospect-questionnaire-question-option"
                                    >
                                        <span
                                            class="hc-prospect-questionnaire-question-option-input"
                                        >
                                            <input
                                                :type="q.type"
                                                v-model.lazy="q.response"
                                                :value="option.id"
                                                :name="
                                                    'prospect-questionnaire-' +
                                                    q.id +
                                                    (q.type == 'radio'
                                                        ? ''
                                                        : '[]')
                                                "
                                                @change="
                                                    validateQuestion(
                                                        q.type == 'radio'
                                                    )
                                                "
                                            />
                                            <span></span>
                                        </span>
                                        <div
                                            class="hc-prospect-questionnaire-question-option-name"
                                            v-text="option.name"
                                        ></div>
                                    </label>
                                </template>

                                <input
                                    v-if="q.type == 'text'"
                                    v-model.lazy="q.response"
                                    @change="validateQuestion()"
                                    class="hc-prospect-questionnaire-question-response-text-input"
                                    :placeholder="
                                        $t('prospect.form.your_response')
                                    "
                                />

                                <input
                                    v-if="q.type == 'date'"
                                    v-model="q.response"
                                    @change="validateQuestion()"
                                    type="date"
                                    class="hc-prospect-questionnaire-question-response-text-input"
                                    :placeholder="
                                        $t('prospect.form.your_response')
                                    "
                                />

                                <label
                                    class="hc-prospect-questionnaire-question-response-image-input"
                                    v-if="q.type == 'image'"
                                >
                                    <div
                                        class="hc-prospect-questionnaire-question-response-image-input-container"
                                    >
                                        <img
                                            v-if="q.response"
                                            :src="q.response"
                                        />
                                        <i v-else class="fa fa-plus"></i>
                                    </div>
                                    <input
                                        type="file"
                                        :placeholder="
                                            $t('prospect.form.your_response')
                                        "
                                        accept="image/png, image/jpeg"
                                        @change="onImageSelected($event, q)"
                                    />
                                </label>

                                <input
                                    v-if="q.type == 'file'"
                                    type="file"
                                    class="hc-prospect-questionnaire-question-response-file-input"
                                    :placeholder="
                                        $t('prospect.form.your_response')
                                    "
                                />

                                <textarea
                                    v-if="q.type == 'textarea'"
                                    v-model.lazy="q.response"
                                    @change="validateQuestion()"
                                    class="hc-prospect-questionnaire-question-response-textarea-input"
                                    :placeholder="
                                        $t('prospect.form.your_response')
                                    "
                                ></textarea>
                            </div>
                            <div
                                class="hc-prospect-questionnaire-question-overlay"
                                @click.stop="question = q"
                            ></div>
                            <loading
                                v-if="question && question.id == q.id"
                                :loading="addingProspectQuestionResults"
                            />
                        </div>
                    </div>
                    <loading :loading="fetchingQuestionnaire" />
                </div>
            </template>
        </tab-layout>
    </slide>
</template>

<style>
#hc-prospect-questionnaire-progress {
    position: relative;
    width: 100%;
    height: 6px;
    background-color: #0002;
}

#hc-prospect-questionnaire-progress-bar {
    height: 100%;
    background-color: #7939b8;
    transition: width 0.5s cubic-bezier(0.23, 1, 0.32, 1);
}

#hc-prospect-questionnaire-progress-marks {
    position: absolute;
    z-index: 1;
    width: 100%;
    height: 100%;
    top: 0px;
    left: 0px;
}

.hc-prospect-questionnaire-progress-mark {
    position: relative;
    height: 100%;
    float: left;
    cursor: pointer;
}

.hc-prospect-questionnaire-progress-mark:hover {
    background-color: #0002;
}

.hc-prospect-questionnaire-progress-mark:after {
    content: "";
    position: absolute;
    top: 0px;
    right: -1px;
    width: 2px;
    height: 100%;
    display: inline-block;
    background-color: white;
}

.hc-prospect-questionnaire-question {
    width: 100%;
    white-space: normal;
    position: relative;
    padding: 7px 15px;
}

.hc-prospect-questionnaire-question-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: #e4e2eb;
    z-index: 1;
    transition: transform 0.5s cubic-bezier(0.23, 1, 0.32, 1);
    opacity: 0.3;
    visibility: visible;
}

.hc-prospect-questionnaire-question.current
    .hc-prospect-questionnaire-question-overlay {
    opacity: 0;
    visibility: hidden;
}

.hc-prospect-questionnaire-question-question {
    line-height: 20px;
}

.hc-prospect-questionnaire-question-response {
}

.hc-prospect-questionnaire-question-option {
    width: 100%;
    display: flex;
    flex-direction: row;
}

.hc-prospect-questionnaire-question-option-input {
}

.hc-prospect-questionnaire-question-option-input > input {
    display: none;
}

.hc-prospect-questionnaire-question-option-input > input + span {
    display: inline-block;
    width: 12px;
    height: 12px;
    border: 1px solid #999999;
    margin-top: 4px;
}

.hc-prospect-questionnaire-question-option-input > input[type="radio"] + span {
    border-radius: 50%;
}

.hc-prospect-questionnaire-question-option-input > input:checked + span {
    background-color: #7939b8;
    border: 1px solid #7939b8;
}

.hc-prospect-questionnaire-question-option-name {
    padding-left: 10px;
    color: #555555;
}

.hc-prospect-questionnaire-question-response-text-input {
    border: none;
    background: none;
    width: 100%;
    height: 30px;
}

.hc-prospect-questionnaire-question-response-image-input {
    width: 160px;
    border-radius: 5px;
    overflow: hidden;
    position: relative;
    background-color: #ffffff;
    outline: 1px solid #cccccc;
    margin-top: 5px;
    cursor: pointer;
}

.hc-prospect-questionnaire-question-response-image-input > input {
    display: none;
}

.hc-prospect-questionnaire-question-response-image-input > div {
    text-align: center;
    width: 100%;
    padding-top: 61.8%;
}

.hc-prospect-questionnaire-question-response-image-input > div > img {
    bottom: 0;
    height: auto;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
}

.hc-prospect-questionnaire-question-response-image-input > div > i {
    bottom: 0;
    height: auto;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
    width: 50px;
    line-height: 100px;
    font-size: 30px;
    color: #aaa;
}

.hc-prospect-questionnaire-question-response-file-input {
    width: 100%;
    height: 30px;
}

.hc-prospect-questionnaire-question-response-textarea-input {
    border: none;
    background: none;
    width: 100%;
    height: 30px;
}
</style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

import QuestionnaireService from "@/apis/project/questionnaire";

import { FETCH_QUESTIONNAIRES } from "@/actions/project/questionnaire";
import {
    FETCH_PROSPECT_QUESTIONNAIRES,
    ADD_PROSPECT_QUESTIONNAIRE,
    SHOW_PROSPECT_QUESTIONNAIRE,
} from "@/actions/project/prospect/questionnaire";
import { ADD_PROSPECT_RESPONSE } from "@/actions/project/prospect/question";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import QuestionnaireRow from "./QuestionnaireRow.vue";

export default {
    components: {
        QuestionnaireRow,
    },

    data() {
        return {
            name: "prospect-manage-questionnaires",
            tab: 0,
            questionnaireKeyword: "",
            questionnaireQuestions: [],
            prospectQuestionnaire: null,
            fileSelected: null,
            fetchingQuestionnaire: false,
            addingProspectQuestionResults: false,
        };
    },

    methods: {
        /**
         *
         */
        addQuestionnaire() {
            store.commit(OPEN_MODAL, "questionnaire-add");
        },

        /**
         *
         * @param {*} category
         */
        async setQuestionnaire(questionnaire) {
            this.tab = 1;

            this.prospectQuestionnaire = questionnaire;

            this.fetchingQuestionnaire = true;
            const { data } = await QuestionnaireService.show(
                this.project.slug,
                questionnaire.id
            );
            this.prospectQuestionnaire = data;
            this.fetchingQuestionnaire = false;

            this.questionnaireQuestions =
                this.prospectQuestionnaire.sections.reduce((carry, section) => {
                    return [...carry, ...section.questions];
                }, []);

            this.createProspectQuestionnaireResults();
        },

        /**
         *
         */
        fetchQuestionnaires() {
            store.dispatch(FETCH_QUESTIONNAIRES);
        },

        /**
         * Fetch prospect questionnaire results
         */
        fetchProspectQuestionnaireResults() {
            // To do
            store.dispatch(FETCH_PROSPECT_QUESTIONNAIRES);
        },

        /**
         * Create prospect questionnaire results
         */
        async createProspectQuestionnaireResults() {
            if (!this.prospectQuestionnaire) {
                return;
            }

            if (!this.prospect) {
                return;
            }

            if (
                !this.prospectQuestionnaires.find(
                    (q) => q.id == this.prospectQuestionnaire.id
                )
            ) {
                store.dispatch(
                    ADD_PROSPECT_QUESTIONNAIRE,
                    this.prospectQuestionnaire
                );
            } else {
                store.dispatch(
                    SHOW_PROSPECT_QUESTIONNAIRE,
                    this.prospectQuestionnaire.id
                );
            }

            if (!this.question && this.questionnaireQuestions.length > 0) {
                this.question = this.questionnaireQuestions[0];
            }
        },

        /**
         * Set current section
         */
        setCurrentSection(section) {
            if (section.questions.length > 0) {
                this.questionnaireResult.question_id = questions[0].id;
            }
        },

        /**
         *
         */
        refreshQuestionResults() {
            let questionnaireQuestions = [...this.questionnaireQuestions];

            questionnaireQuestions.forEach((question) => {
                if (question.type == "checkbox") {
                    question.response = [];
                } else {
                    question.response = "";
                }
            });

            this.prospectQuestionnaireResults.forEach((result) => {
                let question = questionnaireQuestions.find(
                    (q) => q.id == result.question_id
                );

                if (question) {
                    if (question.type == "checkbox") {
                        question.response.push(result.option_id);
                    } else if (question.type == "radio") {
                        question.response = result.option_id;
                    } else if (question.type == "image") {
                        question.response = result.url;
                    } else {
                        question.response = result.response;
                    }
                }
            });

            this.questionnaireQuestions = questionnaireQuestions;
        },

        /**
         * On question image selected
         */
        onImageSelected(event, quest) {
            this.fileSelected = event.target.files[0];

            var reader = new FileReader();

            reader.onload = (e) => {
                quest.response = e.target.result;
            };

            reader.readAsDataURL(this.fileSelected);

            this.validateQuestion(true);
        },

        /**
         * Validate response
         * @param {*} nextQuestion
         */
        async validateQuestion(passToNextQuestion) {
            if (!this.prospectQuestionnaire) {
                return;
            }

            if (!this.question) {
                return;
            }

            if (!this.questionnaireResult) {
                return;
            }

            this.scrollToQuestion();

            let data = {};

            if (
                this.question.is_required &&
                ((this.question.type == "checkbox" &&
                    this.question.response.length == 0) ||
                    (this.question.type == "image" &&
                        this.fileSelected == null) ||
                    (this.question.type != "image" && !this.question.response))
            ) {
                return; // flash("Veuillez répondre à la question svp!", "warning");
            }

            if (passToNextQuestion === undefined) {
                passToNextQuestion = true;
            }

            if (passToNextQuestion) {
                this.addingProspectQuestionResults = true;
            }

            if (document.activeElement) {
                document.activeElement.blur();
            }

            if (this.question.type == "checkbox") {
                data.response = this.question.response;
            } else if (this.question.type == "radio") {
                data.response = this.question.response;
            } else if (
                this.question.type == "image" ||
                this.question.type == "file"
            ) {
                data = new FormData();

                if (this.fileSelected) {
                    data.append("response", this.fileSelected);
                }
            } else {
                data.response = this.question.response;
            }

            try {
                await store.dispatch(ADD_PROSPECT_RESPONSE, {
                    question: this.question.id,
                    params: data,
                });

                if (passToNextQuestion) {
                    this.nextQuestion();
                    this.addingProspectQuestionResults = false;
                }
            } finally {
            }
        },

        /**
         *
         */
        prevQuestion() {
            let index = this.questionnaireQuestions.findIndex(
                (qq) => qq.id == this.question.id
            );

            if (index > 0) {
                this.question = this.questionnaireQuestions[index - 1];
            }
        },

        /**
         *
         */
        nextQuestion() {
            // next question
            if (this.question.type == "radio" && this.question.response) {
                let response = this.question.options.find(
                    (o) => o.id == this.question.response
                );

                if (response && response.redirection_id) {
                    this.question = this.questionnaireQuestions.find(
                        response.redirection_id
                    );
                    return;
                }
            }

            let index = this.questionnaireQuestions.findIndex(
                (qq) => qq.id == this.question.id
            );

            if (index >= 0) {
                if (index < this.questionnaireQuestions.length - 1) {
                    this.question = this.questionnaireQuestions[index + 1];
                } else {
                    this.question = null;
                }
            }
        },

        /**
         *
         */
        scrollToQuestion() {
            if (!this.question) {
                return;
            }

            const q = document.getElementById(
                "prospect-questionnaire-question-" + this.question.id
            );

            if (q) {
                this.$refs.prospectQuestionnaireQuestions.scrollTop =
                    q.offsetTop;
            }
        },

        /**
         *
         * @param {*} questionnaire
         */
        getQuestionnaireResult(questionnaire) {
            let questionnaireResult = this.prospectQuestionnaires.find(
                (qr) => qr.id == questionnaire.id
            );

            return questionnaireResult ? questionnaireResult : null;
        },
    },

    watch: {
        async prospect(newValue) {
            if (newValue && this.slideOpen(this.name)) {
                this.fetchProspectQuestionnaireResults();
            }
        },

        question(newValue) {
            if (newValue) {
                this.scrollToQuestion();
            }
        },

        async questionnaireQuestions() {
            await this.$nextTick();
            this.scrollToQuestion();
        },

        async prospectQuestionnaireResults() {
            this.refreshQuestionResults();
        },

        async prospectQuestionnaires() {
            this.refreshQuestionResults();
        },
    },

    computed: {
        ...mapGetters([
            "project",
            "questionnaires",
            "prospect",
            "prospectFullName",
            "slideOpen",
            "prospectQuestionnaireResults",
            "prospectQuestionnaires",
            "can",
        ]),

        /**
         * Do not allow user
         * to edit some field
         * or on certain conditions applied to the prospect
         */
        disabled() {
            return (
                // deleted or archived prospect
                this.prospect.deleted_at || this.prospect.processed_at
            );
        },

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

        questionnaireResult() {
            if (!this.prospectQuestionnaire) {
                return null;
            }

            return this.getQuestionnaireResult(this.prospectQuestionnaire);
        },

        question: {
            get() {
                if (!this.questionnaireResult) {
                    return null;
                }

                if (!this.questionnaireResult.pivot) {
                    if (this.questionnaireQuestions.length > 0) {
                        return this.questionnaireQuestions[0];
                    }

                    return null;
                }

                let question = this.questionnaireQuestions.find(
                    (q) => q.id == this.questionnaireResult.pivot.question_id
                );

                return question ? question : null;
            },
            set(value) {
                if (!this.questionnaireResult) {
                    return;
                }

                if (!value) {
                    return;
                }

                if (!this.questionnaireResult.pivot) {
                    this.questionnaireResult.pivot = {};
                }

                this.questionnaireResult.pivot.question_id = value.id;
            },
        },

        /**
         * Sections
         */
        questionnaireSections() {
            if (!this.prospectQuestionnaire) {
                return [];
            }

            if (!this.prospectQuestionnaire.sections) {
                return [];
            }

            if (!Array.isArray(this.prospectQuestionnaire.sections)) {
                return [];
            }

            return this.prospectQuestionnaire.sections;
        },

        /**
         * Current question section
         */
        section: {
            get() {
                let question = this.question;

                if (!question) {
                    return null;
                }

                let section = this.questionnaireSections.find(
                    (s) => s.id == question.section_id
                );

                return section ? section : null;
            },
            set(value) {
                if (!this.prospectQuestionnaire) {
                    return;
                }

                if (!this.prospectQuestionnaire.sections) {
                    return;
                }

                const currentSection = this.prospectQuestionnaire.sections.find(
                    (s) => s.id == value.id
                );

                if (!currentSection) {
                    return;
                }

                if (
                    !currentSection.questions ||
                    currentSection.questions.length == 0
                ) {
                    return;
                }

                this.question = currentSection.questions[0];
            },
        },

        /**
         * Progression of the prospect questionnaire
         */
        progress() {
            if (!this.question) {
                return 0;
            }

            let index = this.questionnaireQuestions.findIndex(
                (qq) => qq.id == this.question.id
            );

            return ((index + 1) / this.questionnaireQuestions.length) * 100;
        },
    },
};
</script>
