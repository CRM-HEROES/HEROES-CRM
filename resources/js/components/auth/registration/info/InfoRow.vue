<template>
    <label class="hc-user-registration-field-row">
        <span v-text="label"></span>
        <div>
            <slot>
                <input
                    :value="modelValue"
                    @change="change"
                    :placeholder="label + ' ...'"
                />
            </slot>
        </div>
    </label>
</template>

<style>
.hc-user-registration-field-row {
    width: 100%;
    display: flex;
    flex-direction: column;
    font-size: 12px;
    border-radius: 3px;
    position: relative;
    color: #333333;
}

.hc-user-registration-field-row > span {
    width: 100%;
    line-height: 20px;
    display: inline-block;
    padding: 0;
    font-weight: 600;
}

.hc-user-registration-field-row > div {
    flex: 1;
    padding: 5px 0;
}

.hc-user-registration-field-row > div > input {
    border: 1px solid #dddddd;
    min-height: 34px;
    width: 100%;
    padding: 5px 10px;
    border-radius: 3px;
    overflow: hidden;
    float: left;
    background: none;
    color: #333333;
    padding: 5px 10px;
}

.hc-user-registration-field-row > div > *:focus {
    outline: 2px solid #12a0f3;
    border-color: transparent;
}
</style>

<script>
export default {
    props: {
        label: {
            type: String,
        },

        /**
         *
         */
        modelValue: {
            type: String,
            default: "",
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
