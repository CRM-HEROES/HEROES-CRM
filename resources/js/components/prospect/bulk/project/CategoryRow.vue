<template>
    <item tag="label">
        <icon class="fa fa-tags" />
        <div class="hc-item-main-content" v-text="category.name"></div>
        <div
            v-if="mappedCategory"
            class="hc-item-main-content"
            v-text="mappedCategory.name"
        ></div>
        <icon class="fa fa-caret-right" />
    </item>
</template>

<script>
export default {
    props: {
        category: {
            type: Object,
        },
        mappedCategory: {
            type: Object,
            default: null,
        },
    },
};
</script>
