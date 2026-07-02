<template>
    <form
        class="hc-flex-column"
        style="height: 100%"
        v-if="questionToUpdate"
        @submit.prevent="update"
    >
        <item-list gap="5px" class="hc-flex-1" padding="10px 0">
            <v-field :label="$t('name')" required v-slot="{ label }"
                ><input
                    ref="questionName"
                    :placeholder="label + ' ...'"
                    v-model="questionToUpdate.name"
                    required
            /></v-field>
            <v-field :label="$t('type')" required>
                <select v-model="questionToUpdate.type">
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
            <item tag="label">
                <icon class="fa fa-check" />
                <div class="hc-item-main-content" v-text="$t('required')"></div>
                <checkbox v-model="questionToUpdate.is_required" />
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
        <loading :loading="updatingQuestion || removingQuestion" />
    </form>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    UPDATE_QUESTIONNAIRE_QUESTION,
    REMOVE_QUESTIONNAIRE_QUESTION,
} from "@/actions/project/questionnaire/question";
import { CLOSE_MODAL } from "@/actions/modal";

export default {
    data() {
        return {
            updatingQuestion: false,
            removingQuestion: false,
            fetchingQuestion: false,
            questionToUpdate: this.questionnaireQuestion,
        };
    },

    created() {
        this.questionToUpdate = this.questionnaireQuestion;
    },

    methods: {
        /**
         *
         */
        async update() {
            this.updatingQuestion = true;

            try {
                await store.dispatch(UPDATE_QUESTIONNAIRE_QUESTION, {
                    questionnaire: this.questionnaire.id,
                    section: this.questionnaireSection.id,
                    params: this.questionToUpdate,
                });
            } finally {
                this.updatingQuestion = false;
                store.commit(CLOSE_MODAL);
            }
        },

        /**
         *
         */
        remove() {
            hcConfirm(this.$t("delete_confirm"), async () => {
                this.removingQuestion = true;

                try {
                    await store.dispatch(REMOVE_QUESTIONNAIRE_QUESTION, {
                        questionnaire: this.questionnaire.id,
                        section: this.questionnaireSection.id,
                        slug: this.questionToUpdate.id,
                    });
                } finally {
                    this.removingQuestion = false;
                    store.commit(CLOSE_MODAL);
                }
            });
        },
    },

    watch: {
        async questionnaireQuestion(newValue) {
            if (newValue) {
                this.questionToUpdate = newValue;

                /*this.fetchingQuestion = true;
                this.questionToUpdate = await store.dispatch(
                    SHOW_QUESTIONNAIRE_QUESTION,
                    newValue.id
                );
                this.fetchingQuestion = false;*/
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
