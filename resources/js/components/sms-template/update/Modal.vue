<template>
    <modal
        name="sms-template-update"
        :title="smsTemplate ? smsTemplate.name : ''"
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
        ...mapGetters(["smsTemplate"]),
    },
};
</script>
