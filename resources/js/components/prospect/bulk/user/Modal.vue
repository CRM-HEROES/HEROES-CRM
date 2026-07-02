<template>
    <slide
        name="prospect-bulk-manage-users"
        :title="$t('prospect.table.bulk.user.title')"
        icon="fa fa-tags"
        style="width: 260px"
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
