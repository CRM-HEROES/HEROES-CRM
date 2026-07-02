<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <form
                class="hc-flex-column"
                style="height: 100%"
                v-if="optionToUpdate"
                @submit.prevent="update"
            >
                <item-list gap="5px" class="hc-flex-1" padding="10px 0">
                    <v-field :label="$t('name')" required v-slot="{ label }"
                        ><input
                            ref="optionName"
                            :placeholder="label + ' ...'"
                            v-model="optionToUpdate.name"
                            required
                    /></v-field>
                    <item @click="(tab = 1), (sectionTab = 0)">
                        <icon class="fa fa-external-link" />
                        <div
                            class="hc-item-main-content"
                            v-text="
                                'Rédiriger vers ' +
                                (redirectTo ? redirectTo.name : '...')
                            "
                        ></div>
                        <icon class="fa fa-caret-right" />
                    </item>
                </item-list>
                <buttons>
                    <button
                        @click.prevent="remove"
                        class="hc-button-danger"
                        v-text="$t('delete')"
                    ></button>
                    <button v-text="$t('update')"></button>
                </buttons>
                <loading :loading="updatingOption || removingOption" />
            </form>
        </template>

        <template #2>
            <tab-layout :count="2" :tab="sectionTab" class="hc-flex-1">
                <template #1>
                    <div class="hc-flex-column" style="height: 100%">
                        <item @click="tab = 0" class="bordered">
                            <icon class="fa fa-caret-left" />
                            <div
                                class="hc-item-main-content"
                                v-text="$t('back')"
                            ></div>
                        </item>
                        <search v-model="sectionKeyword" />
                        <item-list class="hc-flex-1" padding="5px">
                            <item
                                @click="
                                    (optionToUpdate.redirection_id = null),
                                        (tab = 0)
                                "
                            >
                                <icon class="fa fa-times icon-red" />
                                <div
                                    class="hc-item-main-content"
                                    v-text="$t('none')"
                                ></div>
                            </item>
                            <section-row
                                v-for="c in filteredSections"
                                :key="c.id"
                                :section="c"
                                @click="(section = c), (sectionTab = 1)"
                            />
                        </item-list>
                    </div>
                </template>
                <template #2>
                    <div class="hc-flex-column" style="height: 100%">
                        <item @click="sectionTab = 0" class="bordered">
                            <icon class="fa fa-caret-left" />
                            <div
                                class="hc-item-main-content"
                                v-text="$t('back')"
                            ></div>
                        </item>
                        <search v-model="questionKeyword" />
                        <item-list class="hc-flex-1" padding="5px">
                            <question-row
                                v-for="c in filteredQuestions"
                                :key="c.id"
                                :question="c"
                                @click="
                                    (optionToUpdate.redirection_id = c.id),
                                        (tab = 0)
                                "
                            />
                        </item-list>
                    </div>
                </template>
            </tab-layout>
        </template>
    </tab-layout>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    SHOW_QUESTIONNAIRE_OPTION,
    UPDATE_QUESTIONNAIRE_OPTION,
    REMOVE_QUESTIONNAIRE_OPTION,
} from "@/actions/project/questionnaire/option";
import { CLOSE_MODAL } from "@/actions/modal";

import SectionRow from "./SectionRow.vue";
import QuestionRow from "./QuestionRow.vue";

export default {
    components: {
        SectionRow,
        QuestionRow,
    },

    data() {
        return {
            tab: 0,
            sectionTab: 0,
            updatingOption: false,
            removingOption: false,
            fetchingOption: false,
            optionToUpdate: this.option,
            sectionKeyword: "",
            section: null,
            questionKeyword: "",
        };
    },

    created() {
        this.optionToUpdate = this.option;
    },

    methods: {
        /**
         *
         */
        async update() {
            this.updatingOption = true;

            try {
                await store.dispatch(UPDATE_QUESTIONNAIRE_OPTION, {
                    questionnaire: this.questionnaire.id,
                    section: this.questionnaireSection.id,
                    question: this.questionnaireQuestion.id,
                    params: this.optionToUpdate,
                });
            } finally {
                this.updatingOption = false;
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingOption = true;

                try {
                    await store.dispatch(REMOVE_QUESTIONNAIRE_OPTION, {
                        questionnaire: this.questionnaire.id,
                        section: this.questionnaireSection.id,
                        question: this.questionnaireQuestion.id,
                        slug: this.optionToUpdate.id,
                    });
                } finally {
                    this.removingOption = false;
                    store.commit(CLOSE_MODAL);
                }
            });
        },
    },

    watch: {
        async questionnaireOption(newValue) {
            if (newValue) {
                this.optionToUpdate = newValue;

                /*this.fetchingOption = true;
                this.optionToUpdate = await store.dispatch(
                    SHOW_QUESTIONNAIRE_OPTION,
                    newValue.id
                );
                this.fetchingOption = false;*/
            }
        },
    },

    computed: {
        ...mapGetters([
            "questionnaire",
            "questionnaireSection",
            "questionnaireQuestion",
            "questionnaireOption",
        ]),

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

        /**
         *
         */
        filteredQuestions() {
            if (!this.section) {
                return [];
            }

            if (!this.section.questions) {
                return [];
            }

            const keyword = removeStringAccent(this.questionKeyword);

            return this.section.questions.filter(
                (question) =>
                    removeStringAccent(question.name).indexOf(keyword) >= 0
            );
        },

        /**
         *
         */
        redirectTo() {
            if (!this.optionToUpdate.redirection_id) {
                return null;
            }

            if (!this.questionnaire) {
                return null;
            }

            if (!this.questionnaire.sections) {
                return null;
            }

            for (let i in this.questionnaire.sections) {
                if (!this.questionnaire.sections[i].questions) {
                    continue;
                }

                for (let j in this.questionnaire.sections[i].questions) {
                    if (
                        this.questionnaire.sections[i].questions[j].id ==
                        this.optionToUpdate.redirection_id
                    ) {
                        return this.questionnaire.sections[i].questions[j];
                    }
                }
            }

            return null;
        },
    },
};
</script>
