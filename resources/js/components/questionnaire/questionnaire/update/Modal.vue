<template>
    <modal
        name="questionnaire-update"
        :title="questionnaire ? questionnaire.name : ''"
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
        ...mapGetters(["questionnaire"]),
    },
};
</script>
