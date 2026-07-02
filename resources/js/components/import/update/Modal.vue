<template>
    <modal
        name="import-update"
        :title="prospectImport ? prospectImport.name : ''"
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
        ...mapGetters(["prospectImport"]),
    },
};
</script>
