<template>
    <slide
        name="settings"
        :title="$t('setting.title')"
        icon="fa fa-cog"
        style="width: 300px"
    >
        <layout />
    </slide>
</template>

<script>
import Layout from "./Layout.vue";

export default {
    components: {
        Layout,
    },
};
</script>
