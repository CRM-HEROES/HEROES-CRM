<template>
    <component
        v-once
        :is="tag"
        class="hc-tag"
        :style="style"
        v-text="text"
    ></component>
</template>

<script>
export default {
    props: {
        /**
         * HTML tag
         * Default <span>
         */
        tag: {
            type: String,
            default: "span",
        },

        /**
         * Text
         */
        text: {
            type: String,
        },

        /**
         * Text color
         */
        color: {
            type: String,
            default: "#333333",
        },

        /**
         * Tag bg color
         */
        bgcolor: {
            type: String,
            default: "#CCCCCC",
        },
    },

    computed: {
        /**
         * Tag css style
         */
        style() {
            return {
                color: this.color,
                "background-color": this.bgcolor,
            };
        },
    },
};
</script>
