<template>
    <modal name="calendar-update" :title="calendar ? calendar.name : ''">
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
        ...mapGetters(["calendar"]),
    },
};
</script>
