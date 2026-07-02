<template>
    <slide
        name="user-bulk-manage-calendars"
        :title="$t('user.table.bulk.calendars.title')"
        icon="fa fa-tags"
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
