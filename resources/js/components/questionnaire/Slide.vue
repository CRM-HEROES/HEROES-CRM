<template>
    <slide
        name="questionnaires"
        :title="$t('questionnaire.questionnaire.list.title')"
        style="width: 340px"
    >
        <layout />
    </slide>
</template>

<script>
import Layout from "./Layout.vue";

export default {
    components: {
        Layout,
    },
};
</script>
