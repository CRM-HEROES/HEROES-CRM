<template>
    <item tag="label">
        <icon class="fa fa-tags" />
        <div class="hc-item-main-content" v-text="category.name"></div>
    </item>
</template>

<script>
export default {
    props: {
        category: {
            type: Object,
        },
    },
};
</script>
