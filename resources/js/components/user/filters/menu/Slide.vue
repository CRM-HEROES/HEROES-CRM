<template>
    <slide
        name="users-table-filter-menu"
        :title="$t('user.table.filters.menus.title')"
        icon="fa fa-list"
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
