<template>
    <modal name="user-vehicle-add" :title="$t('user.vehicles.add.title')">
        <layout />
    </modal>
</template>

<script>
// Components
import Layout from "./Layout.vue";

export default {
    components: {
        Layout,
    },
};
</script>
