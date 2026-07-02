<template>
    <item tag="a" v-tooltip="file.name" :href="file.url" target="_blank">
        <icon :size="30">
            <img :src="file.thumbnail" />
        </icon>
        <div class="hc-item-main-content" v-text="file.name"></div>
    </item>
</template>

<script>
export default {
    props: {
        file: {
            type: Object,
        },
    },
};
</script>
