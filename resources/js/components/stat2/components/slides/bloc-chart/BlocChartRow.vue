<template>
    <item v-tooltip="blocValue.name">
        <icon class="fa fa-chart-area" />
        <div class="hc-item-main-content" v-text="blocValue.name"></div>
    </item>
</template>

<script>
export default {
    props: {
        blocValue: {
            type: Object,
        },
    },
};
</script>
