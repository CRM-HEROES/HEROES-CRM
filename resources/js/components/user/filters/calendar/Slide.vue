<template>
    <slide
        name="users-table-filter-calendar"
        :title="$t('user.table.filters.calendars.title')"
        icon="fa fa-calendar"
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
