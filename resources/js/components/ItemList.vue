<template>
    <div class="hc-item-list" :style="style">
        <slot></slot>
    </div>
</template>

<style>
.hc-item-list {
    height: 100%;
    overflow: auto;
    width: 100%;
    display: flex;
    flex-direction: column;
    overflow-x: hidden;
}

.hc-item-list > * {
    width: 100%;
}
</style>

<script>
export default {
    props: {
        /**
         *
         */
        padding: {
            type: String,
            default: null,
        },

        /**
         *
         */
        gap: {
            type: Number,
            default: 0,
        },
    },

    computed: {
        /**
         *
         */
        style() {
            const style = {};

            if (this.padding) {
                style.padding = this.padding;
            }

            if (this.gap) {
                style.gap = this.gap;
            }

            return style;
        },
    },
};
</script>
