<template>
    <slide
        name="stat-bloc-chart"
        :title="'Nouveau bloc de valeur'"
        icon="fa fa-chart-area"
        style="width: 260px"
    >
        <layout @chart-added="(data) => $emit('chart-added', data)" />
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
