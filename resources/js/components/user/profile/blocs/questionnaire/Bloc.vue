<template>
    <bloc
        icon="fa fa-user-md icon-blue"
        :name="$t('user.profile.blocs.questionnaires')"
    >
        <template #options>
            <icon
                tag="a"
                class="fa fa-plus icon-blue"
                @click.prevent.stop="addQuestionnaire"
            />
        </template>
        <template #body>
            <div style="padding: 10px 10px; float: left; width: 100%">
                <questionnaire-row
                    v-for="(questionnaire, i) in userQuestionnaires"
                    :key="questionnaire.id"
                    :questionnaire="questionnaire"
                />
            </div>
        </template>
    </bloc>
</template>

<style></style>

<script>
import { mapGetters } from "vuex";
import store from "@/store";

// Actions
import { OPEN_SLIDE } from "@/actions/slide";

// Components
import Bloc from "@/components/user/profile/blocs/Bloc.vue";
import QuestionnaireRow from "./QuestionnaireRow.vue";

export default {
    components: {
        Bloc,
        QuestionnaireRow,
    },

    methods: {
        /**
         * Add questionnaire
         * See: @/components/questionnaire/add/Modal.vue
         */
        addQuestionnaire() {
            store.commit(OPEN_SLIDE, "user-manage-questionnaires");
        },
    },

    computed: {
        ...mapGetters(["project", "userQuestionnaires"]),
    },
};
</script>
