<template>
    <item :tag="disabled ? 'div' : 'label'" class="hc-document-style">
        <icon :class="icon" color="#333333" icon-size="11" :size="30" />
        <div class="hc-item-main-content" v-text="name"></div>
        <slot>
            <input
                :value="modelValue"
                @change="change"
                type="number"
                placeholder="..."
            />
            <div class="hc-document-style-unit" v-text="'px'"></div>
        </slot>
        <div
            class="hc-document-style-disabled"
            v-if="disabled"
            @click.prevent.stop
        ></div>
    </item>
</template>

<style>
.hc-document-style {
    padding-right: 5px !important;
}

.hc-document-style > .hc-item-main-content {
    font-size: 12px;
}

.hc-document-style > input,
.hc-document-style > select {
    width: 36px;
    height: 22px;
    border: none;
    background: none;
    text-align: right;
    border-radius: 5px;
    overflow: hidden;
    -webkit-appearance: none;
    -moz-appearance: none;
}

.hc-document-style > input[type="number"] {
    -moz-appearance: textfield;
}

.hc-document-style > input::-webkit-outer-spin-button,
.hc-document-style > input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.hc-document-style > input[type="color"] {
    padding: 0;
}

.hc-document-style > select {
    width: 70px;
}

.hc-document-style > select::-ms-expand {
    display: none;
}

.hc-document-style-unit {
    font-size: 10px;
    color: #aaaaaa;
    height: 34px;
    width: 34px;
    line-height: 34px;
    text-align: center;
}

.hc-document-style-disabled {
    position: absolute;
    width: 100%;
    height: 100%;
    background-color: #fffb;
}
</style>

<script>
export default {
    props: {
        /**
         *
         */
        modelValue: {
            type: String,
            default: "",
        },

        icon: {
            type: String,
        },

        name: {
            type: String,
        },

        disabled: {
            type: Boolean,
            default: false,
        },
    },

    methods: {
        change(event) {
            this.$emit("change", event);
            this.$emit("update:modelValue", event.target.value);
        },
    },
};
</script>
