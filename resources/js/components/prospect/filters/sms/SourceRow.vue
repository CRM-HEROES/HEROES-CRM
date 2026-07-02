<template>
    <item tag="label">
        <icon class="fa fa-phone" />
        <div class="hc-item-main-content" v-text="source.name"></div>
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<script>
export default {
    props: {
        modelValue: {
            type: Array,
        },

        source: {
            type: Object,
        },
    },

    methods: {
        change(order) {
            let isChecked = order.target.checked;
            let newValue = [...this.modelValue];
            if (isChecked) {
                newValue.push(this.value);
            } else {
                newValue.splice(newValue.indexOf(this.value), 1);
            }
            this.$emit("update:modelValue", newValue);
        },
    },

    computed: {
        /**
         *
         */
        value() {
            return this.source.key;
        },

        /**
         *
         */
        isChecked() {
            return this.modelValue.includes(this.value);
        },
    },
};
</script>
