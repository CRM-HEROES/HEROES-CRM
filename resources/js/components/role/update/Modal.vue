<template>
    <modal name="role-update" :title="role ? role.name : ''">
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
        ...mapGetters(["role"]),
    },
};
</script>
