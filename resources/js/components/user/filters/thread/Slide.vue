<template>
    <slide
        name="users-table-filter-thread"
        :title="$t('user.table.filters.threads.title')"
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
    },
};
</script>
