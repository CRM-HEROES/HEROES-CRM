<template>
    <slide
        name="prospects-table-filter-import"
        :title="$t('prospect.table.filters.imports.title')"
        icon="fa fa-file-upload"
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
