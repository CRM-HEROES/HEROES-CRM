<template>
    <item tag="label" v-tooltip="smsSource.name">
        <icon class="fa fa-smsSource" :style="style" />
        <div class="hc-item-main-content" v-text="smsSource.name"></div>
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<script>
export default {
    props: {
        modelValue: {
            type: Array,
        },

        smsSource: {
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
                    id: this.smsSource.id,
                    name: this.smsSource.name,
                    fillColor: this.smsSource.bgcolor,
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
            return this.smsSource.id;
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
