<template>
    <item class="hc-prospect-field">
        <icon class="fa fa-info-circle" :style="style" />
        <div class="hc-item-main-content" v-text="field.name"></div>
        <icon class="fa fa-times icon-red" />
    </item>
</template>

<script>
export default {
    props: {
        field: {
            type: Object,
        },
    },
};
</script>
