<template>
    <modal
        name="questionnaire-option-update"
        :title="questionnaireOption ? questionnaireOption.name : ''"
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
        ...mapGetters(["questionnaireOption"]),
    },
};
</script>
