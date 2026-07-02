<template>
    <label class="hc-document-style-button">
        <input type="checkbox" :checked="modelValue" @change="change" />
        <i :class="icon"></i>
    </label>
</template>

<style>
.hc-document-style-button {
    font-size: 11px;
    height: 34px;
    width: 34px;
    cursor: pointer;
}

.hc-document-style-button > input {
    display: none;
}

.hc-document-style-button > i {
    display: inline-block;
    width: 100%;
    height: 100%;
    line-height: 34px;
    text-align: center;
    border-radius: 10px;
    background-color: white;
    color: #000000;
}

.hc-document-style-button:hover > i {
    background-color: #f6f2f9;
}

.hc-document-style-button > input:checked + i {
    background-color: #f6f2f9;
    color: #7939b8;
}
</style>

<script>
export default {
    props: {
        /**
         *
         */
        modelValue: {
            type: Boolean,
            default: false,
        },

        icon: {
            type: String,
        },
    },

    methods: {
        change(event) {
            this.$emit("change", event);
            this.$emit("update:modelValue", event.target.checked);
        },
    },
};
</script>
