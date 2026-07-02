<template>
    <slide
        name="user-bulk-manage-users"
        :title="$t('user.table.bulk.users.title')"
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
