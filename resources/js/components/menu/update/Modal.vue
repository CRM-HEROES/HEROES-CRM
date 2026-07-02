<template>
    <modal name="menu-update" :title="menu ? menu.name : ''">
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
        ...mapGetters(["menu"]),
    },
};
</script>
