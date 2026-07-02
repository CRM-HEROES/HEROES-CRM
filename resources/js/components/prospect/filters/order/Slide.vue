<template>
    <slide
        name="prospects-table-filter-order"
        :title="$t('prospect.table.filters.order.title')"
        icon="fa fa-shopping-cart"
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
