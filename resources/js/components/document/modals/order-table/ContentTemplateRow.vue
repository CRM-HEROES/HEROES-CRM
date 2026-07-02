<template>
    <item>
        <icon class="fa fa-align-left" />
        <div class="hc-item-main-content" v-text="template.name"></div>
        <icon class="fa fa-plus" />
    </item>
</template>

<script>
export default {
    props: {
        template: {
            type: Object,
        },
    },
};
</script>
