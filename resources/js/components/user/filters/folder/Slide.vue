<template>
    <slide
        name="users-table-filter-folder"
        :title="$t('user.table.filters.folders.title')"
        icon="fa fa-folder"
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
