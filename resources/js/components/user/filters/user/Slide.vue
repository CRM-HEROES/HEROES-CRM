<template>
    <slide
        name="users-table-filter-user"
        :title="$t('user.table.filters.users.title')"
        icon="fa fa-user"
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
