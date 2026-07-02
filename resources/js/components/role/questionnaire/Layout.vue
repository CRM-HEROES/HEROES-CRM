<template>
    <div class="hc-flex-column" style="height: 100%">
        <search v-model="questionnaireKeyword" />
        <item-list class="hc-flex-1" padding="5px">
            <item tag="label">
                <icon class="fa fa-check-square" />
                <div class="hc-item-main-content" v-text="$t('all')"></div>
                <checkbox v-model="all" @change="change" :disabled="canAll" />
            </item>
            <questionnaire-row
                v-for="questionnaire in filteredQuestionnaires"
                :key="questionnaire.id"
                :questionnaire="questionnaire"
                :value="isQuestionnaireChecked(questionnaire)"
                :disabled="canAll"
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
    FETCH_ROLE_QUESTIONNAIRES,
    BULK_ADD_ROLE_QUESTIONNAIRE,
    BULK_REMOVE_ROLE_QUESTIONNAIRE,
    ADD_ROLE_QUESTIONNAIRE,
    REMOVE_ROLE_QUESTIONNAIRE,
} from "@/actions/project/role/questionnaire";
import { OPEN_MODAL } from "@/actions/modal";

// Components
import QuestionnaireRow from "./QuestionnaireRow.vue";

export default {
    components: {
        QuestionnaireRow,
    },

    data() {
        return {
            name: "role-manage-questionnaires",
            tab: 0,
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
                this.canAll ||
                this.roleQuestionnaires.findIndex(
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
        ...mapGetters([
            "questionnaires",
            "role",
            "roleQuestionnaires",
            "rolePermissions",
            "can",
        ]),

        /**
         *
         */
        canAll() {
            return this.rolePermissions.includes("all");
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

        /**
         *
         */
        all: {
            get: function () {
                return (
                    this.canAll ||
                    this.roleQuestionnaires.length == this.questionnaires.length
                );
            },
            set: async function (value) {
                if (value) {
                    this.questionnaires.forEach((f) => {
                        store.commit(ADD_ROLE_QUESTIONNAIRE, f);
                    });
                    store.dispatch(BULK_ADD_ROLE_QUESTIONNAIRE, {
                        roles: [this.role.id],
                        questionnaires: this.questionnaires.map((f) => f.id),
                    });
                } else {
                    this.questionnaires.forEach((f) => {
                        store.commit(REMOVE_ROLE_QUESTIONNAIRE, f);
                    });
                    store.dispatch(BULK_REMOVE_ROLE_QUESTIONNAIRE, {
                        roles: [this.role.id],
                        questionnaires: this.questionnaires.map((f) => f.id),
                    });
                }
            },
        },
    },
};
</script>
