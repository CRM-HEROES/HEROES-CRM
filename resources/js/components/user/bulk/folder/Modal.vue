<template>
    <slide
        name="user-bulk-manage-folders"
        :title="$t('user.table.bulk.folders.title')"
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
