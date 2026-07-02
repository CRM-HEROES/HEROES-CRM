<template>
    <modal name="import-add" :title="$t('import.add.title')">
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
