<template>
    <item tag="label" v-tooltip="user.name">
        <icon class="fa fa-tag" />
        <div class="hc-item-main-content" v-text="user.name"></div>
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<script>
export default {
    props: {
        modelValue: {
            type: Array,
        },

        user: {
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
                    id: this.user.id,
                    name: this.user.name,
                    fillColor: this.user.bgcolor,
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
            return this.user.id;
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
