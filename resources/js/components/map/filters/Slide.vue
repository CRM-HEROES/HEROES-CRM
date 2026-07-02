<template>
    <slide
        name="events-table-filter-event"
        :title="$t('prospect.table.filters.appointments.title')"
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
