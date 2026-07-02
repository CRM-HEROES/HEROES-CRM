<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="questionnaireKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label">
                <icon class="fa fa-check-square" />
                <div class="hc-item-main-content" v-text="$t('all')"></div>
                <checkbox v-model="all" />
            </item>
            <questionnaire-row
                v-for="questionnaire in filteredQuestionnaires"
                :key="questionnaire.id"
                :questionnaire="questionnaire"
                :value="isQuestionnaireChecked(questionnaire)"
            />
        </item-list>
        <buttons v-if="can('all.project.questionnaire.add')">
            <a @click.prevent="addQuestionnaire" v-text="$t('add')"></a>
        </buttons>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import {
    BULK_ADD_USER_QUESTIONNAIRE,
    BULK_REMOVE_USER_QUESTIONNAIRE,
    ADD_USER_QUESTIONNAIRE,
    REMOVE_USER_QUESTIONNAIRE,
} from "@/actions/project/user/questionnaire";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import QuestionnaireRow from "./QuestionnaireRow.vue";

export default {
    components: {
        QuestionnaireRow,
    },

    data() {
        return {
            name: "user-manage-questionnaires",
            questionnaireKeyword: "",
        };
    },

    methods: {
        /**
         *
         * @param {*} questionnaire
         */
        isQuestionnaireChecked(questionnaire) {
            return (
                // this.can("all") ||
                this.userQuestionnaires.findIndex(
                    (l) => l.id == questionnaire.id
                ) >= 0
            );
        },

        /**
         *
         */
        addQuestionnaire() {
            store.commit(OPEN_MODAL, "questionnaire-add");
        },
    },

    computed: {
        ...mapGetters(["questionnaires", "user", "userQuestionnaires", "can"]),

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
        all: {
            get: function () {
                return (
                    // this.can("all") ||
                    this.userQuestionnaires.length == this.questionnaires.length
                );
            },
            set: async function (value) {
                if (value) {
                    this.questionnaires.forEach((f) => {
                        store.commit(ADD_USER_QUESTIONNAIRE, f);
                    });
                    store.dispatch(BULK_ADD_USER_QUESTIONNAIRE, {
                        users: [this.user.id],
                        questionnaires: this.questionnaires.map((f) => f.id),
                    });
                } else {
                    this.questionnaires.forEach((f) => {
                        store.commit(REMOVE_USER_QUESTIONNAIRE, f);
                    });
                    store.dispatch(BULK_REMOVE_USER_QUESTIONNAIRE, {
                        users: [this.user.id],
                        questionnaires: this.questionnaires.map((f) => f.id),
                    });
                }
            },
        },
    },
};
</script>
