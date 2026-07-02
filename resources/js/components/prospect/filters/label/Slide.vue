<template>
    <slide
        name="prospects-table-filter-label"
        :title="$t('prospect.table.filters.label.title')"
        icon="fa fa-tags"
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
