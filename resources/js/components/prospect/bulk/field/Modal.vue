<template>
    <slide
        name="prospect-bulk-update"
        :title="$t('prospect.table.bulk.field.title')"
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
