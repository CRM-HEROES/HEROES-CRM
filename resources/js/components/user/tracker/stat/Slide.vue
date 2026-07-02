<template>
    <slide
        :name="'user-logs-chart'"
        :title="$t('user.log_chart.title')"
        icon="fa fa-wifi"
        style="width: 420px"
    >
        <layout />
    </slide>
</template>

<script>
// Components
import Layout from "./Layout.vue";

export default {
    components: {
        Layout,
    },
};
</script>
