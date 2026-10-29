<template>
    <slide
        name="prospects-table-filter-thread"
        :title="$t('prospect.table.filters.threads.title')"
        icon="fa fa-envelope"
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
    }
};
</script>
