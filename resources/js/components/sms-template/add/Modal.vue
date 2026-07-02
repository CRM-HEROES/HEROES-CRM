<template>
    <modal name="sms-template-add" :title="$t('sms_template.add.title')">
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
