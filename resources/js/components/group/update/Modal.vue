<template>
    <modal name="group-update" :title="group ? group.name : ''">
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
        ...mapGetters(["group"]),
    },
};
</script>
