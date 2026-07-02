<template>
    <slide
        name="prospect-bulk-manage-groups"
        :title="$t('prospect.table.bulk.group.title')"
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
