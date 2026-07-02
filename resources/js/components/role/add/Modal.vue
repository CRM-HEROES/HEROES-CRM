<template>
    <modal name="role-add" :title="$t('role.add.title')">
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
