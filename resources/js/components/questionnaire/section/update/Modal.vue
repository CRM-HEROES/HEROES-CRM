<template>
    <modal
        name="questionnaire-section-update"
        :title="questionnaireSection ? questionnaireSection.name : ''"
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
        ...mapGetters(["questionnaireSection"]),
    },
};
</script>
