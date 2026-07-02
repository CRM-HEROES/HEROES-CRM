<template>
    <button class="hc-card-menu">
        <i v-if="icon" :class="icon" :style="style"></i>
        <span class="hc-card-menu-label" v-text="label"></span>
        <span class="hc-card-menu-text" v-if="text" v-text="text"></span>
        <slot></slot>
    </button>
</template>

<style>
.hc-card-menu {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    border: none;
    cursor: pointer;
    /* position: relative; */
    background: none;
    border-radius: 10px;
}

.hc-card-menu:disabled {
    opacity: 0.7;
}

.hc-card-menu:hover {
    background-color: #eeeeee;
}

.hc-card-menu:disabled:hover {
    background-color: transparent;
}

.hc-card-menu > i {
    color: #484848;
    font-size: 17px;
}

.hc-card-menu-label {
    visibility: hidden;
    opacity: 0;
    position: absolute;
    font-size: 11px;
    color: white;
    line-height: 15px;
    left: 50%;
    transform: scale(0.9) translateX(-50%);
    bottom: 48px;
    background-color: #333333;
    padding: 5px 10px;
    border-radius: 4px;
    transition: all 100ms ease-out;
    min-width: 100px;
}

.hc-card-menu:hover > .hc-card-menu-label {
    opacity: 1;
    visibility: visible;
    transform: scale(1) translateX(-50%);
}

.hc-card-menu-text {
    font-size: 12px;
    margin-left: 3px;
    font-weight: 700;
}

.dark .hc-card-menu > i {
    color: #999999;
}

.dark .hc-card-menu:hover {
    background-color: #363a36;
}
</style>

<script>
export default {
    props: {
        icon: {
            type: String,
            default: null,
        },

        color: {
            type: String,
            default: null,
        },

        label: {
            type: String,
        },

        text: {
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

            if (this.color) {
                style.color = this.color;
            }

            return style;
        },
    },
};
</script>
