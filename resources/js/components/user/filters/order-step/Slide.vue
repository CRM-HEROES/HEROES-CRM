<template>
    <slide
        name="users-table-filter-order-step"
        :title="$t('user.table.filters.order-steps.title')"
        icon="fa fa-step-forward"
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
