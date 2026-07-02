<template>
    <modal
        name="message-template-add"
        :title="$t('message_template.add.title')"
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
