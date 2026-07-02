<template>
    <slide
        name="users-table-filter-role"
        :title="$t('user.table.filters.roles.title')"
        icon="fa fa-user-tie"
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
