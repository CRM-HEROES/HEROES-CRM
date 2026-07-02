<template>
    <slide
        name="user-bulk-manage-threads"
        :title="$t('user.table.bulk.threads.title')"
        icon="fa fa-tags"
        style="width: 260px"
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
