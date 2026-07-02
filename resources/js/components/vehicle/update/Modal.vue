<template>
    <modal name="vehicle-update" :title="vehicle ? vehicle.name : ''">
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
        ...mapGetters(["vehicle"]),
    },
};
</script>
