<template>
    <component :is="tag" class="hc-icon" :style="style"
        ><slot></slot
    ></component>
</template>

<style>
.hc-icon {
    align-items: center;
    color: #999999;
    display: flex;
    flex-direction: column;
    justify-content: center;
    font-size: 15px;
    width: 36px;
    height: 36px;
    position: relative;
    overflow: hidden;
    text-shadow: 0px 1px 1px #0005;
}

a.hc-icon,
button.hc-icon,
label.hc-icon {
    cursor: pointer;
    text-decoration: none;
    background: none;
    border: none;
}

label.hc-icon > input[type="file"] {
    display: none;
}

a.hc-icon:hover,
button.hc-icon:hover,
label.hc-icon:hover {
    color: #1e88e5;
}

a.hc-icon:hover {
    text-decoration: none;
}

/*.hc-icon > div {
    width: 100%;
    height: 100%;
    align-items: center;
    display: flex;
    flex-direction: column;
    justify-content: center;
}*/

.hc-icon svg {
    height: 15px;
    display: inline-block;
}

.hc-icon img {
    max-width: 40px;
    max-height: 40px;
    display: inline-block;
}

.hc-icon input {
    display: none;
}

.dark .hc-icon {
    color: #999999;
}
</style>

<script>
export default {
    props: {
        /**
         * Html tag
         */
        tag: {
            type: String,
            default: "i",
        },

        /**
         * Size
         */
        iconSize: {
            type: Number,
            default: null,
        },

        /**
         * Size
         */
        size: {
            type: Number,
            default: null,
        },

        /**
         * Size
         */
        color: {
            type: String,
            default: null,
        },
    },

    computed: {
        /**
         *
         */
        style() {
            const style = {};

            if (this.iconSize) {
                style.fontSize = `${this.iconSize}px`;
            }

            if (this.size) {
                style.height = `${this.size}px`;
                style.width = `${this.size}px`;
            }

            if (this.color) {
                style.color = `${this.color}`;
            }

            return style;
        },
    },
};
</script>
