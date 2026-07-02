<template>
    <modal
        name="prospect-link-update"
        :title="prospectLink ? prospectLink.name : ''"
    >
        <layout />
    </modal>
</template>

<script>
import { mapGetters } from "vuex";

// Components
import Layout from "./Layout.vue";

export default {
    components: {
        Layout,
    },

    computed: {
        ...mapGetters(["prospectLink"]),
    },
};
</script>
