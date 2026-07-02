<template>
    <form
        class="hc-flex-column"
        style="height: 100%"
        @submit.prevent="storeQuestion"
    >
        <item-list gap="5px">
            <v-field :label="$t('name')" required v-slot="{ label }"
                ><input
                    :placeholder="label + ' ...'"
                    v-model="question.name"
                    required
            /></v-field>
            <v-field :label="$t('type')" required>
                <select v-model="question.type">
                    <option
                        value="radio"
                        v-text="$t('questionnaire.question.types.radio')"
                    ></option>
                    <option
                        value="checkbox"
                        v-text="$t('questionnaire.question.types.checkbox')"
                    ></option>
                    <option
                        value="text"
                        v-text="$t('questionnaire.question.types.text')"
                    ></option>
                    <option
                        value="textarea"
                        v-text="$t('questionnaire.question.types.textarea')"
                    ></option>
                    <option
                        value="date"
                        v-text="$t('questionnaire.question.types.date')"
                    ></option>
                    <option
                        value="image"
                        v-text="$t('questionnaire.question.types.image')"
                    ></option>
                    <option
                        value="file"
                        v-text="$t('questionnaire.question.types.file')"
                    ></option>
                </select>
            </v-field>
        </item-list>
        <buttons>
            <button v-text="$t('add')"></button>
        </buttons>
        <loading :loading="addingQuestion" />
    </form>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { ADD_QUESTIONNAIRE_QUESTION } from "@/actions/project/questionnaire/question";
import { CLOSE_MODAL } from "@/actions/modal";

export default {
    data() {
        return {
            question: this.newQuestion(),
            addingQuestion: false,
        };
    },

    methods: {
        /**
         *
         */
        newQuestion() {
            return {
                name: "",
                type: "checkbox",
            };
        },

        /**
         *
         */
        async storeQuestion() {
            this.addingQuestion = true;

            try {
                await store.dispatch(ADD_QUESTIONNAIRE_QUESTION, {
                    questionnaire: this.questionnaire.id,
                    section: this.questionnaireSection.id,
                    params: this.question,
                });
            } finally {
                this.addingQuestion = false;
                this.question = this.newQuestion();
                store.commit(CLOSE_MODAL);
            }
        },
    },

    computed: {
        ...mapGetters(["questionnaire", "questionnaireSection"]),
    },
};
</script>
