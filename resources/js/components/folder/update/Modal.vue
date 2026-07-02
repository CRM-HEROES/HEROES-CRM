<template>
    <modal name="folder-update" :title="folder ? folder.name : ''">
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
        ...mapGetters(["folder"]),
    },
};
</script>
