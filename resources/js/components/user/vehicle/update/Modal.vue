<template>
    <modal
        name="user-vehicle-update"
        :title="userVehicle ? userVehicle.name : ''"
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
        ...mapGetters(["userVehicle"]),
    },
};
</script>
