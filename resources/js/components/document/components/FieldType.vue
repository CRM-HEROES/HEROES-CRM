<template>
    <label class="hc-document-field-type">
        <input type="checkbox" :checked="modelValue" @change="change" />
        <div class="hc-document-field-type-container">
            <div class="hc-document-field-type-content">
                <slot></slot>
            </div>
        </div>
        <span v-text="text"></span>
    </label>
</template>

<style>
.hc-document-field-type {
    width: 33.333%;
    float: left;
    padding: 4px;
    display: flex;
    flex-direction: column;
    cursor: pointer;
    border-radius: 10px;
}

.hc-document-field-type:hover {
    background-color: #eeeeee;
}

.hc-document-field-type > input {
    display: none;
}

.hc-document-field-type-container {
    width: 100%;
    border-radius: 10px;
    box-shadow: 0px 2px 5px -2px #0006;
    overflow: hidden;
    position: relative;
    color: #555555;
}

.hc-document-field-type > input:checked + .hc-document-field-type-container {
    background-color: #7939b8;
    color: white;
}

.hc-document-field-type-content {
    text-align: center;
    width: 100%;
    padding-top: 100%;
}

.hc-document-field-type-content > i,
.hc-document-field-type-content > svg {
    bottom: 0;
    height: auto;
    left: 0;
    margin: auto;
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 22px;
}

.hc-document-field-type > span {
    text-align: center;
    font-size: 12px;
    margin-top: 5px;
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

        text: {
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
