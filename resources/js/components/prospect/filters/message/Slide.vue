<template>
    <slide
        name="prospects-table-filter-message"
        :title="$t('prospect.table.filters.message.title')"
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
