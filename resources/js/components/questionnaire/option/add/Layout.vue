<template>
    <tab-layout :count="2" :tab="tab" class="hc-flex-1">
        <template #1>
            <form
                class="hc-flex-column"
                style="height: 100%"
                @submit.prevent="storeOption"
            >
                <item-list gap="5px">
                    <v-field :label="$t('name')" required v-slot="{ label }"
                        ><input
                            :placeholder="label + ' ...'"
                            v-model="option.name"
                            required
                    /></v-field>
                </item-list>
                <buttons>
                    <button v-text="$t('add')"></button>
                </buttons>
                <loading :loading="addingOption" />
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
                                v-text="
                                    $t(
                                        'questionnaire.option.add.choose_section'
                                    )
                                "
                            ></div>
                        </item>
                        <search v-model="sectionKeyword" />
                        <item-list class="hc-flex-1" padding="5px">
                            <section-row
                                v-for="c in filteredSections"
                                :key="c.id"
                                :section="c"
                                @click=""
                            />
                        </item-list>
                    </div>
                </template>
                <template #2>
                    <div
                        class="hc-flex-column"
                        style="height: 100%"
                        v-if="section"
                    >
                        <item @click="sectionTab = 0" class="bordered">
                            <icon class="fa fa-caret-left" />
                            <div
                                class="hc-item-main-content"
                                v-text="section.name"
                            ></div>
                        </item>
                        <search v-model="questionKeyword" />
                        <item-list class="hc-flex-1" padding="5px">
                            <question-row
                                v-for="c in filteredQuestions"
                                :key="c.id"
                                :question="c"
                                @click=""
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
import { ADD_QUESTIONNAIRE_OPTION } from "@/actions/project/questionnaire/option";
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
            sectionKeyword: "",
            questionKeyword: "",
            option: this.newOption(),
            addingOption: false,
        };
    },

    methods: {
        /**
         *
         */
        newOption() {
            return {
                name: "",
                color: "#FFFFFF",
                bgcolor: "#000000",
            };
        },

        /**
         *
         */
        async storeOption() {
            this.addingOption = true;

            try {
                await store.dispatch(ADD_QUESTIONNAIRE_OPTION, {
                    questionnaire: this.questionnaire.id,
                    section: this.questionnaireSection.id,
                    question: this.questionnaireQuestion.id,
                    params: this.option,
                });
            } finally {
                this.addingOption = false;
                this.option = this.newOption();
                store.commit(CLOSE_MODAL);
            }
        },
    },

    computed: {
        ...mapGetters([
            "questionnaire",
            "questionnaireSection",
            "questionnaireQuestion",
        ]),
    },
};
</script>
