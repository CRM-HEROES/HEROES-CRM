<template>
    <slide
        name="users-table-filter-document"
        :title="$t('user.table.filters.documents.title')"
        icon="fa fa-file-pdf"
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
