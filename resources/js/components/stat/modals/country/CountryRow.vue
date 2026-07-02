<template>
    <item>
        <icon class="fa fa-globe" :size="24" :icon-size="12" />
        <div class="hc-item-main-content" v-text="country"></div>
        <icon class="fa fa-link" :size="24" :icon-size="12" />
    </item>
</template>

<script>
export default {
    props: {
        country: {
            type: Object,
        },
    },
};
</script>
