<template>
    <component :is="tag" class="hc-item" :style="style">
        <slot></slot>
    </component>
</template>

<script>
export default {
    props: {
        /**
         *
         */
        tag: {
            type: String,
            default: "div",
        },

        /**
         *
         */
        padding: {
            type: Number,
            default: 0,
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
            return {
                padding: `${this.padding}px`,
                gap: `${this.gap}px`,
            };
        },
    },
};
</script>
