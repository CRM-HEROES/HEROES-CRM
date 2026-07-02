<template>
    <slide
        name="manage-roles"
        :title="$t('role.list.title')"
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
