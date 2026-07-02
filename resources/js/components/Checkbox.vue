<template>
    <label class="hc-checkbox" :for="id ? id : null">
        <input
            :type="type"
            :name="name"
            :id="id ? id : null"
            :checked="isChecked"
            :value="value"
            :disabled="disabled"
            @change="change"
        />
        <span>
            <span></span>
        </span>
    </label>
</template>

<style>
.hc-checkbox {
    height: 14px;
    vertical-align: middle;
}

.hc-checkbox > input {
    display: none;
}

.hc-checkbox > input + * {
    background: #ddd;
    border-radius: 9px;
    cursor: pointer;
    display: inline-block;
    height: 100%;
    margin: 0;
    position: relative;
    transition: all 100ms ease-out;
    width: 26px;
}

.hc-checkbox > input:disabled + * {
    opacity: 0.3;
}

.hc-checkbox > input:checked + * {
    background: #1e88e5;
}

.hc-checkbox > input + * > span {
    content: "";
    display: inline-block;
    height: 0;
    left: 4px;
    margin-bottom: 0;
    position: absolute;
    top: 6px;
    transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
    width: 0;
}

.hc-checkbox > input + * > span:before {
    background: #fff;
    border-radius: 50%;
    box-shadow: 0 2px 2px #0004;
    content: "";
    display: inline-block;
    height: 12px;
    left: -2px;
    position: absolute;
    top: -5px;
    width: 12px;
    z-index: 0;
}

.hc-checkbox > input:checked + * > span {
    left: 15px;
}

.hc-checkbox > input + * > span:after {
    /*background: rgba(7, 100, 124, 0.3);
    border-radius: 50%;
    content: "";
    display: inline-block;
    height: 0;
    left: 0px;
    pointer-events: none;
    position: absolute;
    top: 0px;
    transition: all 0 ease-out 500ms;
    width: 0;
    z-index: 1;*/
}

.hc-checkbox > input:checked + * > span:after {
    background: transparent;
    height: 48px;
    left: -24px;
    top: -24px;
    transition: all 600ms cubic-bezier(0, 0.5, 0, 1);
    width: 48px;
    visibility: hidden;
}

.dark .hc-checkbox > input + * {
    background: #555555;
}

.dark .hc-checkbox > input:checked + * {
    background: #1e88e5;
}
</style>

<script>
export default {
    props: {
        modelValue: {
            type: Boolean,
            default: false,
        },

        name: {
            type: String,
            default: "",
        },

        type: {
            type: String,
            default: "checkbox",
        },

        id: {
            type: String,
            default: "",
        },

        disabled: {
            type: Boolean,
            default: false,
        },

        value: {},
    },

    methods: {
        change(event) {
            let isChecked = event.target.checked;
            if (this.modelValue instanceof Array) {
                let newValue = [...this.modelValue];
                if (isChecked) {
                    newValue.push(this.value);
                } else {
                    newValue.splice(newValue.indexOf(this.value), 1);
                }
                this.$emit("update:modelValue", newValue);
            } else {
                this.$emit("update:modelValue", isChecked);
            }
        },
    },

    computed: {
        isChecked() {
            if (this.modelValue instanceof Array) {
                return this.modelValue.includes(this.value);
            }

            return this.modelValue;
        },
    },
};
</script>
