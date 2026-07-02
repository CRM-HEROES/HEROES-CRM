<template>
    <item tag="label">
        <icon class="fa fa-user-tie" />
        <div class="hc-item-main-content" v-text="role.name"></div>
        <checkbox :model-value="isChecked" @change="change" />
    </item>
</template>

<script>
export default {
    props: {
        modelValue: {
            type: Array,
        },

        role: {
            type: Object,
        },
    },

    methods: {
        change(event) {
            let isChecked = event.target.checked;
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
            return this.role.id;
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
