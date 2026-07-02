<template>
    <slide
        name="role-duplicate"
        :title="$t('role.duplicate.title')"
        icon="fa fa-copy"
        style="width: 360px"
    >
        <layout />
    </slide>
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
