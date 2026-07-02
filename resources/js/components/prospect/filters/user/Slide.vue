<template>
    <slide
        name="prospects-table-filter-user"
        :title="$t('prospect.table.filters.users.title')"
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
