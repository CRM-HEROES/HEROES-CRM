<template>
    <modal name="vehicle-add" :title="$t('vehicle.add.title')">
        <layout />
    </modal>
</template>

<script>
import Layout from "./Layout.vue";

export default {
    components: {
        Layout,
    },
};
</script>
