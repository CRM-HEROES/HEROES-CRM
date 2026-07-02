<template>
    <modal
        name="questionnaire-add"
        :title="$t('questionnaire.questionnaire.add.title')"
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
