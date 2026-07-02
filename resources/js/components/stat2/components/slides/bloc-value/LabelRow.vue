<template>
    <item tag="label" v-tooltip="label.name">
        <icon class="fa fa-tag" :style="style" />
        <div class="hc-item-main-content" v-text="label.name"></div>
    </item>
</template>

<script>
export default {
    props: {
        label: {
            type: Object,
        },
    },

    computed: {
        /**
         *
         */
        style() {
            return {
                color: this.label.bgcolor,
            };
        },
    },
};
</script>
