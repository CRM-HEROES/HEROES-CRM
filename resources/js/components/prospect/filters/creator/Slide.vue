<template>
    <slide
        name="prospects-table-filter-creator"
        :title="$t('prospect.table.filters.creators.title')"
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
