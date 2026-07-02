<template>
    <modal
        name="message-template-update"
        :title="messageTemplate ? messageTemplate.name : ''"
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
        ...mapGetters(["messageTemplate"]),
    },
};
</script>
