<template>
    <slide
        name="users-table-filter-order-action"
        :title="$t('user.table.filters.order-actions.title')"
        icon="fa fa-person-digging"
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
