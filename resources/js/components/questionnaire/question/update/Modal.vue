<template>
    <modal
        name="questionnaire-question-update"
        :title="questionnaireQuestion ? questionnaireQuestion.name : ''"
    >
        <layout />
    </modal>
</template>

<script>
import { mapGetters } from "vuex";
import Layout from "./Layout.vue";

export default {
    components: {
        Layout,
    },

    computed: {
        ...mapGetters(["questionnaireQuestion"]),
    },
};
</script>
