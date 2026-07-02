<template>
    <modal name="file-update" :title="file ? file.name : ''">
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
    mounted() {
        console.log(this.file);
    },
    computed: {
        ...mapGetters(["file"]),
    },
};
</script>
