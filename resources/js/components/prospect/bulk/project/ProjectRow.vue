<template>
    <item tag="label">
        <icon class="fa fa-file" />
        <div class="hc-item-main-content" v-text="project.name"></div>
        <icon class="fa fa-caret-right" />
    </item>
</template>

<script>
export default {
    props: {
        project: {
            type: Object,
        },
    },
};
</script>
