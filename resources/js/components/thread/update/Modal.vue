<template>
    <modal name="thread-update" :title="thread ? thread.name : ''">
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
        ...mapGetters(["thread"]),
    },
};
</script>
