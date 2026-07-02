<template>
    <modal
        name="questionnaire-section-add"
        :title="$t('questionnaire.section.add.title')"
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
