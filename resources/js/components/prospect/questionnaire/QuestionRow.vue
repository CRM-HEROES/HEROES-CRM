<template></template>

<script>
import store from "@/store";

// Actions
import { OPEN_MODAL } from "@/actions/modal";
import { SET_QUESTIONNAIRE_QUESTION } from "@/actions/project/questionnaire/question";

export default {
    props: {
        question: {
            type: Object,
        },
    },

    methods: {
        edit(e) {
            store.commit(OPEN_MODAL, "question-update");
            store.commit(SET_QUESTIONNAIRE_QUESTION, this.question);
        },
    },

    computed: {},
};
</script>
