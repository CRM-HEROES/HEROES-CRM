<template>
    <modal
        name="questionnaire-question-add"
        :title="$t('questionnaire.question.add.title')"
    >
        <layout />
    </modal>
</template>

<script>
import Layout from "./Layout.vue";

export default {
    components: {
        Layout,
    },
};
</script>
