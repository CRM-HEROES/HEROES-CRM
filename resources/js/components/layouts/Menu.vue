<template>
    <component :is="tag" class="hc-header-menu">
        <i v-if="icon" :class="icon" :style="style"></i>
        <slot></slot>
        <span v-if="label" v-text="label"></span
    ></component>
</template>

<style>
.hc-header-menu {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    border: none;
    cursor: pointer;
    position: relative;
    background: none;
    border-radius: 10px;
    width: 40px;
    height: 40px;
    z-index: 5;
    text-decoration: none;
}

.hc-header-menu:disabled {
    opacity: 0.7;
}

.hc-header-menu:hover {
    background-color: #eeeeee;
}

.hc-header-menu:disabled:hover {
    background-color: transparent;
}

.hc-header-menu > i {
    color: #333333;
    font-size: 14px;
}

.hc-header-menu > svg,
.hc-header-menu > img {
    height: 22px;
}

.hc-header-menu > span {
    visibility: hidden;
    opacity: 0;
    position: absolute;
    font-size: 11px;
    color: white;
    line-height: 15px;
    left: 50%;
    transform: scale(0.9) translateX(-50%);
    top: 48px;
    background-color: #333333;
    padding: 5px 10px;
    border-radius: 4px;
    transition: all 100ms ease-out;
}

.hc-header-menu:hover > span {
    opacity: 1;
    visibility: visible;
    transform: scale(1) translateX(-50%);
}

.dark .hc-header-menu > i {
    color: #999999;
}

.dark .hc-header-menu:hover {
    background-color: #363a36;
}

@media (max-width: 767px) {
    .hc-header-menu {
        width: 30px;
        height: 30px;
    }

    .hc-header-menu > svg {
        height: 19px;
    }
}
</style>

<script>
export default {
    props: {
        tag: {
            type: String,
            default: "router-link",
        },

        icon: {
            type: String,
        },

        color: {
            type: String,
            default: null,
        },

        label: {
            type: String,
        },
    },

    computed: {
        /**
         *
         */
        style() {
            const style = {};

            if (this.color) {
                style.color = this.color;
            }

            return style;
        },
    },
};
</script>
