<template>
    <slide
        name="user-duplicate"
        :title="$t('user.duplicate.title')"
        icon="fa fa-copy"
        style="width: 360px"
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
