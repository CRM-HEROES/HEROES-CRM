<template>
    <modal name="pipeline-update" :title="pipeline ? pipeline.name : ''">
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
        ...mapGetters(["pipeline"]),
    },
};
</script>
