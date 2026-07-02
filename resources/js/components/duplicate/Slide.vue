<template>
    <slide
        name="duplicate"
        :title="$t('duplicate.title')"
        icon="fa fa-file-upload"
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
