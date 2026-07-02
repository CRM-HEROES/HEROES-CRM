<template>
    <item tag="label" v-tooltip="intractionSource.name">
        <icon class="fa fa-intractionSource" :style="style" />
        <div class="hc-item-main-content" v-text="intractionSource.name"></div>
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<script>
export default {
    props: {
        modelValue: {
            type: Array,
        },

        intractionSource: {
            type: Object,
        },
    },

    data() {
        return {
            currentValue: this.value,
        };
    },

    methods: {
        change(event) {
            let isChecked = event.target.checked;
            let newValue = [...this.modelValue];
            if (isChecked) {
                newValue.push({
                    id: this.intractionSource.id,
                    name: this.intractionSource.name,
                    fillColor: this.intractionSource.bgcolor,
                });
            } else {
                newValue.splice(this.index, 1);
            }
            this.$emit("update:modelValue", newValue);
        },
    },

    computed: {
        /**
         *
         */
        value() {
            return this.intractionSource.id;
        },

        /**
         *
         */
        index() {
            return this.modelValue.findIndex((v) => v.id == this.value);
        },

        /**
         *
         */
        isChecked() {
            return this.index >= 0;
        },
    },
};
</script>
