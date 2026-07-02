<template>
    <slide
        name="prospects-table-filter-pipedrive-account"
        :title="$t('prospect.table.filters.pipedrive_accounts.title')"
        icon="fa fa-file-upload"
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
