<template>
    <slide
        name="prospects-table-filter-field"
        :title="$t('prospect.table.filters.field.title')"
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
