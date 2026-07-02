<template>
    <div class="hc-color-palette" @click.prevent.stop>
        <div
            v-for="color in colors"
            :style="{
                backgroundColor: color,
            }"
            @click.stop="change(color)"
        ></div>
    </div>
</template>

<style scoped>
.hc-color-palette {
    width: 100%;
    border: none;
}

.hc-color-palette > div {
    margin: 2px;
    width: 20px;
    height: 20px;
    border-radius: 5px;
    border: 1px solid #555;
    cursor: pointer;
    float: left;
    opacity: 0.9;
}
.hc-color-palette > div:hover {
    opacity: 1;
}
</style>

<script>
export default {
    props: {
        colors: {
            type: Array,
            default: [
                "#FFFFFF",
                "#000000",
                "#FF0000",
                "#FF8800",
                "#FFCC00",
                "#FFFF00",
                "#00FF00",
                "#00CCFF",
                "#0088FF",
                "#0000FF",
                "#8800FF",
                "#FF0088",
                "#b5d31d",
                "#8b6144",
            ],
        },
    },

    methods: {
        change(value) {
            this.$emit("update:modelValue", value);
        },
    },
};
</script>
