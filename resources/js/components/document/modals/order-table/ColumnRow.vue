<template>
    <item>
        <icon class="fa fa-columns" />
        <div class="hc-item-main-content" v-text="column.header.content"></div>
        <icon class="fa fa-caret-right" />
    </item>
</template>

<script>
export default {
    props: {
        column: {
            type: Object,
        },
    },
};
</script>
