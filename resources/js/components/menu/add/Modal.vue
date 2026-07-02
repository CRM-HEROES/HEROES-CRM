<template>
    <modal name="menu-add" :title="$t('menu.add.title')">
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
