<template>
    <modal
        name="questionnaire-option-add"
        :title="$t('questionnaire.option.add.title')"
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
