<template>
    <item tag="label">
        <icon class="fa fa-info icon-blue" />
        <div
            class="hc-item-main-content"
            v-text="tutorial.name"
            v-tooltip="tutorial.name"
        ></div>
        <icon class="fa fa-blue icon-eye" />
    </item>
</template>

<script>
export default {
    props: {
        tutorial: {
            type: Object,
        },
    },
};
</script>
