<template>
    <slide
        name="user-bulk-manage-order-steps"
        :title="$t('user.table.bulk.order_steps.title')"
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
