<template>
    <slide
        name="prospects-table-filter-all"
        :title="$t('prospect.table.filters.all.title')"
        icon="fa fa-filter"
        style="width: 220px"
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
