<template>
    <slide
        name="users-table-filter-category"
        :title="$t('user.table.filters.categories.title')"
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
