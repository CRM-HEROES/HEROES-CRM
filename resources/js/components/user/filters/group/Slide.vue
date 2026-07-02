<template>
    <slide
        name="users-table-filter-group"
        :title="$t('user.table.filters.groups.title')"
        icon="fa fa-users"
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
