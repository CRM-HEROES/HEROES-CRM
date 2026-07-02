<template>
    <item>
        <icon class="fa fa-list" />
        <div class="hc-item-main-content" v-text="summary.header.content"></div>
        <icon class="fa fa-caret-right" />
    </item>
</template>

<script>
export default {
    props: {
        summary: {
            type: Object,
        },
    },
};
</script>
