<template>
    <slide
        name="tutorials"
        :title="$t('setting.tutorials.title')"
        icon="fa fa-info"
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
